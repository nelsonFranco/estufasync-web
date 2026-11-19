<?php
declare(strict_types=1);

/* Carrega segredos apenas no servidor. Nunca use este arquivo no JavaScript. */
function configuracao_app(): array
{
    static $configuracao = null;

    if (is_array($configuracao)) {
        return $configuracao;
    }

    $arquivoConfiguracao = __DIR__ . '/config.php';
    if (!is_file($arquivoConfiguracao)) {
        throw new RuntimeException('Configuração privada ainda não foi criada.');
    }

    $configuracao = require $arquivoConfiguracao;
    if (!is_array($configuracao)) {
        throw new RuntimeException('A configuração privada é inválida.');
    }

    return $configuracao;
}

/* Conexão única com PDO. Toda consulta deve usar prepare() e execute(). */
function banco(): PDO
{
    static $conexao = null;

    if ($conexao instanceof PDO) {
        return $conexao;
    }

    $configuracao = configuracao_app();
    $dsn = 'mysql:host=' . $configuracao['db_host']
        . ';dbname=' . $configuracao['db_name']
        . ';charset=utf8mb4';

    $conexao = new PDO($dsn, $configuracao['db_user'], $configuracao['db_password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $conexao;
}
