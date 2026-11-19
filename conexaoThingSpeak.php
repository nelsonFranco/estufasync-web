<?php
require_once __DIR__ . '/seguranca.php';
// A atualização automática não prolonga uma sessão ociosa.
exigir_login('index.php', false);
require_once __DIR__ . '/controles-seguranca.php';
require_once __DIR__ . '/thingspeak.php';

header('Content-Type: application/json; charset=UTF-8');
$usuarioId = (int) $_SESSION['usuario_id'];
$estufaId = (int) $_SESSION['estufa_id'];

try {
    if (limite_excedido('api_atual', (string) $usuarioId, 120, 300)) {
        http_response_code(429);
        header('Retry-After: 60');
        registrar_auditoria('limite_api_atual', $usuarioId, $estufaId);
        echo json_encode(['erro' => 'Muitas consultas. Aguarde um minuto.']);
        exit;
    }
    registrar_tentativa('api_atual', (string) $usuarioId);
    $dados = json_decode(consultar_thingspeak('feeds/last.json', [], 65536), true, 64, JSON_THROW_ON_ERROR);
    if (!is_array($dados)) throw new RuntimeException('Leitura inválida.');

    $consulta = banco()->prepare('SELECT temperatura_min, temperatura_max, umidade_min, umidade_max, wifi_min, pressao_min, pressao_max, uv_max, vento_max FROM configuracoes_alerta WHERE estufa_id = :estufa_id LIMIT 1');
    $consulta->execute(['estufa_id' => $estufaId]);
    $regras = $consulta->fetch() ?: [
        'temperatura_min' => 20, 'temperatura_max' => 28, 'umidade_min' => 55, 'umidade_max' => 75,
        'wifi_min' => -80, 'pressao_min' => 950, 'pressao_max' => 1050, 'uv_max' => 800, 'vento_max' => 10,
    ];
    $dados['alertas'] = $regras;
    echo json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
} catch (Throwable $erro) {
    http_response_code(502);
    error_log('Falha ao consultar leitura atual: ' . $erro->getMessage());
    echo json_encode(['erro' => 'Não foi possível consultar o ThingSpeak.']);
}
