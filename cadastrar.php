<?php
declare(strict_types=1);

require_once __DIR__ . '/seguranca.php';
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/controles-seguranca.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: cadastro.php');
    exit;
}

validar_csrf();

$nome = trim((string) ($_POST['nome'] ?? ''));
$usuario = trim((string) ($_POST['usuario'] ?? ''));
$email = strtolower(trim((string) ($_POST['email'] ?? '')));
$senha = (string) ($_POST['senha'] ?? '');
$confirmarSenha = (string) ($_POST['confirmar_senha'] ?? '');
$codigoConvite = trim((string) ($_POST['codigo_convite'] ?? ''));
$aceitouTermos = ($_POST['aceitar_termos'] ?? '') === '1';

$usuarioValido = preg_match('/\A[A-Za-z0-9_.-]{3,50}\z/', $usuario) === 1;
$senhaForte = senha_aceitavel($senha);

// Cada mensagem descreve apenas os campos enviados pela própria pessoa.
$problemas = [];
if ($nome === '' || strlen($nome) > 100) $problemas[] = 'Informe um nome com até 100 caracteres.';
if (!$usuarioValido) $problemas[] = 'O usuário deve ter de 3 a 50 caracteres e usar apenas letras, números, ponto, hífen ou sublinhado.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) $problemas[] = 'Informe um e-mail válido.';
if (!$senhaForte) $problemas[] = 'A senha precisa ter 12 ou mais caracteres, com letra maiúscula, minúscula e número.';
if (!hash_equals($senha, $confirmarSenha)) $problemas[] = 'A confirmação de senha está diferente.';
if (!$aceitouTermos) $problemas[] = 'Aceite os Termos de Uso e a Política de Privacidade.';

if ($problemas) {
    $_SESSION['erros_cadastro'] = $problemas;
    header('Location: cadastro.php?erro=dados');
    exit;
}

try {
    $pdo = banco();
    if (limite_excedido('cadastro_ip', '*', 5, 3600) || limite_excedido('cadastro_email', $email, 3, 3600)) {
        registrar_auditoria('cadastro_bloqueado', null, null, ['email_hash' => hash_privado($email)]);
        header('Location: cadastro.php?enviado=1');
        exit;
    }
    registrar_tentativa('cadastro_ip', '*');
    registrar_tentativa('cadastro_email', $email);

    $config = configuracao_app();
    $estufaId = (int) ($config['estufa_id'] ?? 0);
    $conviteEsperado = (string) ($config['codigo_convite_hash'] ?? '');
    if ($estufaId < 1 || $conviteEsperado === '' || !hash_equals($conviteEsperado, hash('sha256', $codigoConvite))) {
        registrar_auditoria('cadastro_convite_invalido', null, $estufaId ?: null);
        header('Location: cadastro.php?erro=convite');
        exit;
    }
    $estufaExiste = $pdo->prepare('SELECT id FROM estufas WHERE id = :id LIMIT 1');
    $estufaExiste->execute(['id' => $estufaId]);
    if (!$estufaExiste->fetch()) throw new RuntimeException('Estufa configurada não existe.');

    $verificar = $pdo->prepare('SELECT id FROM usuarios WHERE usuario = :usuario OR email = :email LIMIT 1');
    $verificar->execute(['usuario' => $usuario, 'email' => $email]);

    if ($verificar->fetch()) {
        header('Location: cadastro.php?enviado=1');
        exit;
    }

    $pdo->beginTransaction();
    $inserir = $pdo->prepare(
        'INSERT INTO usuarios (estufa_id, nome, usuario, email, senha_hash, termos_aceitos_em) VALUES (:estufa_id, :nome, :usuario, :email, :senha_hash, NOW())'
    );
    $inserir->execute([
        'estufa_id' => $estufaId,
        'nome' => $nome,
        'usuario' => $usuario,
        'email' => $email,
        'senha_hash' => password_hash($senha, PASSWORD_DEFAULT),
    ]);
    $usuarioId = (int) $pdo->lastInsertId();
    $pdo->commit();
    registrar_auditoria('cadastro_criado', $usuarioId, $estufaId);
    header('Location: cadastro.php?enviado=1');
    exit;
} catch (Throwable $erro) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    error_log('Falha ao criar usuário no sistema de estufa: ' . $erro->getMessage());
    header('Location: cadastro.php?erro=sistema');
    exit;
}
