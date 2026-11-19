<?php
declare(strict_types=1);

/*
 * Funções de segurança compartilhadas pelo sistema.
 * Este arquivo deve ser carregado antes de qualquer HTML.
 */

function iniciar_sessao_segura(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['SERVER_PORT'] ?? '') === '443')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

    $configuracao = [];
    $arquivoConfiguracao = __DIR__ . '/config.php';
    if (is_file($arquivoConfiguracao)) {
        $configuracaoCarregada = require $arquivoConfiguracao;
        if (is_array($configuracaoCarregada)) $configuracao = $configuracaoCarregada;
    }
    $producaoHttps = str_starts_with((string) ($configuracao['app_url'] ?? ''), 'https://');

    if ($producaoHttps && !$https && !in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1'], true)) {
        $destino = rtrim((string) $configuracao['app_url'], '/') . ($_SERVER['REQUEST_URI'] ?? '/');
        header('Location: ' . $destino, true, 301);
        exit;
    }

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_name('ESTUFASESSID');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $https || $producaoHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

function enviar_cabecalhos_seguranca(): void
{
    if (headers_sent()) {
        return;
    }

    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    header('Cache-Control: no-store, private');
    if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
    header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self'; img-src 'self' data:; connect-src 'self'; frame-src https://thingspeak.com https://thingspeak.mathworks.com; object-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors 'self'");
}

function token_csrf(): string
{
    iniciar_sessao_segura();

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function campo_csrf(): string
{
    return '<input type="hidden" name="csrf_token" value="'
        . htmlspecialchars(token_csrf(), ENT_QUOTES, 'UTF-8')
        . '">';
}

function validar_csrf(): void
{
    $enviado = $_POST['csrf_token'] ?? '';
    $esperado = $_SESSION['csrf_token'] ?? '';

    if (!is_string($enviado) || !is_string($esperado) || $esperado === '' || !hash_equals($esperado, $enviado)) {
        http_response_code(403);
        exit('Não foi possível validar esta solicitação. Atualize a página e tente novamente.');
    }
}

function exigir_login(string $paginaLogin = '../index.php', bool $registrarAtividade = true): void
{
    iniciar_sessao_segura();

    if (empty($_SESSION['usuario_id']) || empty($_SESSION['inicio_sessao']) || empty($_SESSION['ultimo_acesso'])) {
        header('Location: ' . $paginaLogin);
        exit;
    }

    $agora = time();
    if (($agora - (int) $_SESSION['ultimo_acesso']) > 1800 || ($agora - (int) $_SESSION['inicio_sessao']) > 28800) {
        $_SESSION = [];
        session_destroy();
        header('Location: ' . $paginaLogin . '?sessao=expirada');
        exit;
    }

    if ($registrarAtividade && ($agora - (int) ($_SESSION['id_renovado_em'] ?? 0)) > 900) {
        session_regenerate_id(true);
        $_SESSION['id_renovado_em'] = $agora;
    }
    if ($registrarAtividade) $_SESSION['ultimo_acesso'] = $agora;

    try {
        require_once __DIR__ . '/conexao.php';
        $consulta = banco()->prepare('SELECT u.versao_sessao, u.ativo, u.estufa_id, u.nome, u.usuario, u.email, u.foto_perfil, e.nome AS estufa_nome FROM usuarios u JOIN estufas e ON e.id = u.estufa_id WHERE u.id = :id LIMIT 1');
        $consulta->execute(['id' => (int) $_SESSION['usuario_id']]);
        $conta = $consulta->fetch();
        if (!$conta || !(int) $conta['ativo'] || (int) $conta['versao_sessao'] !== (int) ($_SESSION['versao_sessao'] ?? 0)) {
            throw new RuntimeException('Sessão revogada.');
        }
        $_SESSION['estufa_id'] = (int) $conta['estufa_id'];
        $_SESSION['estufa_nome'] = $conta['estufa_nome'];
        $_SESSION['usuario'] = $conta['usuario'];
        $_SESSION['nome_exibicao'] = $conta['nome'];
        $_SESSION['perfil'] = ['nome' => $conta['nome'], 'usuario' => $conta['usuario'], 'email' => $conta['email'], 'foto' => $conta['foto_perfil'] ? '../' . $conta['foto_perfil'] : ''];
    } catch (Throwable $erro) {
        $_SESSION = [];
        session_destroy();
        header('Location: ' . $paginaLogin . '?sessao=invalida');
        exit;
    }
}

function finalizar_login(int $usuarioId, int $estufaId, int $versaoSessao, string $usuario, string $nome): void
{
    session_regenerate_id(true);
    $_SESSION['usuario_id'] = $usuarioId;
    $_SESSION['estufa_id'] = $estufaId;
    $_SESSION['versao_sessao'] = $versaoSessao;
    $_SESSION['usuario'] = $usuario;
    $_SESSION['nome_exibicao'] = $nome;
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    $_SESSION['inicio_sessao'] = time();
    $_SESSION['ultimo_acesso'] = time();
    $_SESSION['id_renovado_em'] = time();
}

iniciar_sessao_segura();
enviar_cabecalhos_seguranca();
