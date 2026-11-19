<?php
declare(strict_types=1);

require_once __DIR__ . '/conexao.php';

function segredo_app(): string
{
    $segredo = (string) (configuracao_app()['app_secret'] ?? '');
    if (strlen($segredo) < 32) {
        throw new RuntimeException('app_secret precisa ter pelo menos 32 caracteres.');
    }
    return $segredo;
}

function hash_privado(string $valor): string
{
    return hash_hmac('sha256', $valor, segredo_app());
}

function chave_cliente(string $identificador = ''): string
{
    $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? 'desconhecido');
    return hash_privado($ip . '|' . strtolower(trim($identificador)));
}

function limite_excedido(string $acao, string $identificador, int $maximo, int $janelaSegundos): bool
{
    $inicio = date('Y-m-d H:i:s', time() - $janelaSegundos);
    $consulta = banco()->prepare('SELECT COUNT(*) FROM tentativas_seguranca WHERE acao = :acao AND chave_hash = :chave AND sucesso = 0 AND criado_em >= :inicio');
    $consulta->execute(['acao' => $acao, 'chave' => chave_cliente($identificador), 'inicio' => $inicio]);
    return (int) $consulta->fetchColumn() >= $maximo;
}

function registrar_tentativa(string $acao, string $identificador, bool $sucesso = false): void
{
    $inserir = banco()->prepare('INSERT INTO tentativas_seguranca (acao, chave_hash, sucesso) VALUES (:acao, :chave, :sucesso)');
    $inserir->execute(['acao' => $acao, 'chave' => chave_cliente($identificador), 'sucesso' => $sucesso ? 1 : 0]);
    if (random_int(1, 100) === 1) {
        banco()->exec("DELETE FROM tentativas_seguranca WHERE criado_em < DATE_SUB(NOW(), INTERVAL 30 DAY)");
    }
}

function limpar_falhas(string $acao, string $identificador): void
{
    $apagar = banco()->prepare('DELETE FROM tentativas_seguranca WHERE acao = :acao AND chave_hash = :chave AND sucesso = 0');
    $apagar->execute(['acao' => $acao, 'chave' => chave_cliente($identificador)]);
}

function senha_aceitavel(string $senha): bool
{
    return strlen($senha) >= 12 && strlen($senha) <= 128
        && preg_match('/[a-z]/', $senha) === 1
        && preg_match('/[A-Z]/', $senha) === 1
        && preg_match('/\d/', $senha) === 1;
}

function registrar_auditoria(string $evento, ?int $usuarioId = null, ?int $estufaId = null, array $detalhes = []): void
{
    try {
        $ipHash = hash_privado((string) ($_SERVER['REMOTE_ADDR'] ?? 'desconhecido'));
        $userAgentHash = hash_privado((string) ($_SERVER['HTTP_USER_AGENT'] ?? 'desconhecido'));
        $inserir = banco()->prepare('INSERT INTO auditoria (usuario_id, estufa_id, evento, ip_hash, user_agent_hash, detalhes) VALUES (:usuario_id, :estufa_id, :evento, :ip_hash, :ua_hash, :detalhes)');
        $inserir->execute([
            'usuario_id' => $usuarioId,
            'estufa_id' => $estufaId,
            'evento' => substr($evento, 0, 80),
            'ip_hash' => $ipHash,
            'ua_hash' => $userAgentHash,
            'detalhes' => $detalhes ? json_encode($detalhes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
        ]);
    } catch (Throwable $erro) {
        error_log('Falha ao registrar auditoria: ' . $erro->getMessage());
    }
}

function texto_limitado(mixed $valor, int $maximo): string
{
    $texto = trim((string) $valor);
    if (strlen($texto) > $maximo) {
        throw new InvalidArgumentException('Campo acima do limite permitido.');
    }
    return $texto;
}
