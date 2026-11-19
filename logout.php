<?php
require_once __DIR__ . '/seguranca.php';
require_once __DIR__ . '/controles-seguranca.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

validar_csrf();
registrar_auditoria('logout', isset($_SESSION['usuario_id']) ? (int) $_SESSION['usuario_id'] : null, isset($_SESSION['estufa_id']) ? (int) $_SESSION['estufa_id'] : null);

// Apaga os dados da sessão e volta para o login.
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $cookie = session_get_cookie_params();
    setcookie(session_name(), '', ['expires' => time() - 42000, 'path' => $cookie['path'], 'domain' => $cookie['domain'], 'secure' => (bool) $cookie['secure'], 'httponly' => true, 'samesite' => 'Lax']);
}
session_destroy();
header('Clear-Site-Data: "cache", "storage"');

header('Location: index.php?saiu=1');
exit();
