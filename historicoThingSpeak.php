<?php
require_once __DIR__ . '/seguranca.php';
// Consultas automáticas do painel não prolongam uma sessão ociosa.
exigir_login('index.php', false);
require_once __DIR__ . '/controles-seguranca.php';
require_once __DIR__ . '/thingspeak.php';

header('Content-Type: application/json; charset=UTF-8');
$usuarioId = (int) $_SESSION['usuario_id'];
$estufaId = (int) $_SESSION['estufa_id'];

try {
    if (limite_excedido('api_historico', (string) $usuarioId, 30, 300)) {
        http_response_code(429);
        header('Retry-After: 60');
        registrar_auditoria('limite_api_historico', $usuarioId, $estufaId);
        echo json_encode(['erro' => 'Muitas consultas de histórico. Aguarde um minuto.']);
        exit;
    }
    registrar_tentativa('api_historico', (string) $usuarioId);
    $quantidade = max(10, min((int) ($_GET['results'] ?? 80), 800));
    $minutos = max(0, min((int) ($_GET['minutes'] ?? 0), 1440));
    $parametros = $minutos > 0 ? ['minutes' => max(20, $minutos), 'results' => 800] : ['results' => $quantidade];
    echo consultar_thingspeak('feeds.json', $parametros, 2097152);
} catch (Throwable $erro) {
    http_response_code(502);
    error_log('Falha ao consultar histórico: ' . $erro->getMessage());
    echo json_encode(['erro' => 'Não foi possível consultar o histórico.']);
}
