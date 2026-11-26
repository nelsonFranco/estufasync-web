<?php
/*
 * Relatórios ficam no site Estufa (estufa.freedev.app).
 * Esta página gera um passe assinado (HMAC-SHA256), válido por 60 segundos e de uso único,
 * e manda a pessoa direto para o painel do Estufa, sem pedir usuário e senha de novo.
 * O mesmo segredo fica no config.php daqui e no config.producao.php do Estufa.
 */
require 'proteger.php';
require_once __DIR__ . '/../conexao.php';

$config = configuracao_app();
$urlEstufa = rtrim((string) ($config['estufa_url'] ?? ''), '/');
$segredo = (string) ($config['estufa_sso_segredo'] ?? '');

if ((!str_starts_with($urlEstufa, 'https://') && !str_starts_with($urlEstufa, 'http://')) || strlen($segredo) < 32) {
    error_log('Relatórios: estufa_url ou estufa_sso_segredo não configurados no config.php.');
    http_response_code(503);
    exit('Os relatórios estão indisponíveis no momento.');
}

$base64url = static fn(string $dados): string => rtrim(strtr(base64_encode($dados), '+/', '-_'), '=');

$agora = time();
$carga = $base64url(json_encode([
    'usuario' => (string) ($_SESSION['usuario'] ?? ''),
    'nome' => (string) ($_SESSION['nome_exibicao'] ?? ''),
    'iat' => $agora,
    'exp' => $agora + 60,
    'jti' => bin2hex(random_bytes(16)),
], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
$assinatura = $base64url(hash_hmac('sha256', $carga, $segredo, true));

header('Cache-Control: no-store');
header('Referrer-Policy: no-referrer');
header('Location: ' . $urlEstufa . '/entrar-estufasync.php?passe=' . $carga . '.' . $assinatura, true, 303);
exit;
