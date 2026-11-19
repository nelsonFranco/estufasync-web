<?php
declare(strict_types=1);

require_once __DIR__ . '/conexao.php';

function consultar_thingspeak(string $recurso, array $parametros = [], int $limiteBytes = 2097152): string
{
    $config = configuracao_app();
    $canal = (string) ($config['thingspeak_canal'] ?? '');
    $chave = (string) ($config['thingspeak_read_key'] ?? '');
    if (!preg_match('/\A\d+\z/', $canal) || $chave === '') throw new RuntimeException('ThingSpeak não configurado.');
    $parametros['api_key'] = $chave;
    $url = 'https://api.thingspeak.com/channels/' . rawurlencode($canal) . '/' . ltrim($recurso, '/') . '?' . http_build_query($parametros, '', '&', PHP_QUERY_RFC3986);
    $contexto = stream_context_create([
        'http' => ['timeout' => 10, 'ignore_errors' => false, 'user_agent' => 'EstufaInteligente/1.0'],
        'ssl' => ['verify_peer' => true, 'verify_peer_name' => true],
    ]);
    $stream = @fopen($url, 'rb', false, $contexto);
    if (!$stream) throw new RuntimeException('ThingSpeak indisponível.');
    try {
        $dados = stream_get_contents($stream, $limiteBytes + 1);
    } finally {
        fclose($stream);
    }
    if ($dados === false || strlen($dados) > $limiteBytes) throw new RuntimeException('Resposta externa acima do limite.');
    json_decode($dados, true, 64, JSON_THROW_ON_ERROR);
    return $dados;
}
