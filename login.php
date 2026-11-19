<?php
require_once __DIR__ . '/seguranca.php';
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/controles-seguranca.php';

// O formulário deve enviar os dados para esta página.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit();
}

validar_csrf();

// Recebe os dados digitados no formulário e limita o tamanho antes da consulta.
$usuario = trim((string) ($_POST['usuario'] ?? ''));
$senha = (string) ($_POST['senha'] ?? '');

if ($usuario === '' || strlen($usuario) > 50 || $senha === '' || strlen($senha) > 128) {
    header('Location: index.php?erro=1');
    exit;
}

try {
    if (limite_excedido('login_usuario', $usuario, 5, 900) || limite_excedido('login_ip', '*', 20, 900)) {
        registrar_auditoria('login_bloqueado', null, null, ['usuario_hash' => hash_privado(strtolower($usuario))]);
        header('Location: index.php?erro=1');
        exit;
    }
    $consulta = banco()->prepare(
        'SELECT id, estufa_id, usuario, nome, senha_hash, versao_sessao, ativo FROM usuarios WHERE usuario = :usuario LIMIT 1'
    );
    $consulta->execute(['usuario' => $usuario]);
    $usuarioBanco = $consulta->fetch();

    $hashComparacao = $usuarioBanco['senha_hash'] ?? password_hash('comparacao-sem-usuario', PASSWORD_DEFAULT);
    $senhaCorreta = password_verify($senha, $hashComparacao);
    if ($usuarioBanco && $senhaCorreta && (int) $usuarioBanco['ativo'] === 1) {
        if (password_needs_rehash($usuarioBanco['senha_hash'], PASSWORD_DEFAULT)) {
            banco()->prepare('UPDATE usuarios SET senha_hash = :hash WHERE id = :id')->execute(['hash' => password_hash($senha, PASSWORD_DEFAULT), 'id' => (int) $usuarioBanco['id']]);
        }
        limpar_falhas('login_usuario', $usuario);
        banco()->prepare('UPDATE usuarios SET ultimo_login_em=NOW() WHERE id=:id')->execute(['id' => (int) $usuarioBanco['id']]);
        finalizar_login((int) $usuarioBanco['id'], (int) $usuarioBanco['estufa_id'], (int) $usuarioBanco['versao_sessao'], $usuarioBanco['usuario'], $usuarioBanco['nome']);
        registrar_tentativa('login_usuario', $usuario, true);
        registrar_auditoria('login_sucesso', (int) $usuarioBanco['id'], (int) $usuarioBanco['estufa_id']);

        header('Location: paginas/boasvindas.php');
        exit;
    }
    registrar_tentativa('login_usuario', $usuario);
    registrar_tentativa('login_ip', '*');
    registrar_auditoria('login_falha', $usuarioBanco ? (int) $usuarioBanco['id'] : null, $usuarioBanco ? (int) $usuarioBanco['estufa_id'] : null, ['usuario_hash' => hash_privado(strtolower($usuario))]);
} catch (Throwable $erro) {
    // Não revelar detalhes de conexão ou SQL para quem está na tela de login.
    error_log('Falha no login do sistema de estufa: ' . $erro->getMessage());
}

// Se os dados estiverem errados, retorna ao login.
header('Location: index.php?erro=1');
exit();
