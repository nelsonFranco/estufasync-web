<?php
require 'proteger.php';
require_once __DIR__ . '/../controles-seguranca.php';

$estufaId = (int) $_SESSION['estufa_id'];
$usuarioId = (int) $_SESSION['usuario_id'];
$mensagem = '';
$erro = '';

function data_cultivo(mixed $valor): ?string
{
    $data = trim((string) $valor);
    if ($data === '') return null;
    $objeto = DateTime::createFromFormat('Y-m-d', $data);
    if (!$objeto || $objeto->format('Y-m-d') !== $data) throw new InvalidArgumentException('Informe uma data válida.');
    return $data;
}

function cultivo_atual(int $estufaId): array
{
    $consulta = banco()->prepare('SELECT * FROM cultivos WHERE estufa_id = :estufa_id AND ativo = 1 ORDER BY id DESC LIMIT 1');
    $consulta->execute(['estufa_id' => $estufaId]);
    $cultivo = $consulta->fetch();
    if ($cultivo) return $cultivo;
    banco()->prepare('INSERT INTO cultivos (estufa_id, ativo) VALUES (:estufa_id, 1)')->execute(['estufa_id' => $estufaId]);
    $consulta->execute(['estufa_id' => $estufaId]);
    return $consulta->fetch();
}

$cultivo = cultivo_atual($estufaId);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validar_csrf();
    try {
        $acao = (string) ($_POST['acao'] ?? 'salvar');
        if ($acao === 'evento') {
            $texto = texto_limitado($_POST['evento'] ?? '', 500);
            if ($texto === '') throw new InvalidArgumentException('Escreva o acontecimento antes de adicionar.');
            banco()->prepare('INSERT INTO diario_cultivo (cultivo_id, usuario_id, descricao) VALUES (:cultivo_id, :usuario_id, :descricao)')->execute(['cultivo_id' => $cultivo['id'], 'usuario_id' => $usuarioId, 'descricao' => $texto]);
            registrar_auditoria('diario_cultivo_adicionado', $usuarioId, $estufaId, ['cultivo_id' => (int) $cultivo['id']]);
            $mensagem = 'Registro adicionado ao diário compartilhado.';
        } elseif ($acao === 'salvar') {
            $plantasTexto = trim((string) ($_POST['plantas'] ?? ''));
            $plantas = $plantasTexto === '' ? null : filter_var($plantasTexto, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 1000000]]);
            if ($plantasTexto !== '' && $plantas === false) throw new InvalidArgumentException('A quantidade de plantas deve estar entre 0 e 1.000.000.');
            $dados = [
                'id' => (int) $cultivo['id'], 'estufa_id' => $estufaId,
                'cultura' => texto_limitado($_POST['cultura'] ?? '', 100) ?: null,
                'variedade' => texto_limitado($_POST['variedade'] ?? '', 100) ?: null,
                'plantio' => data_cultivo($_POST['plantio'] ?? ''),
                'fase' => texto_limitado($_POST['fase'] ?? '', 100) ?: null,
                'colheita' => data_cultivo($_POST['colheita'] ?? ''),
                'plantas' => $plantas,
                'setor' => texto_limitado($_POST['setor'] ?? '', 100) ?: null,
                'observacoes' => texto_limitado($_POST['observacoes'] ?? '', 2000) ?: null,
            ];
            if ($dados['plantio'] && $dados['colheita'] && $dados['colheita'] < $dados['plantio']) throw new InvalidArgumentException('A previsão de colheita não pode ser anterior ao plantio.');
            banco()->prepare('UPDATE cultivos SET cultura=:cultura, variedade=:variedade, data_plantio=:plantio, fase=:fase, previsao_colheita=:colheita, quantidade_plantas=:plantas, setor=:setor, observacoes=:observacoes WHERE id=:id AND estufa_id=:estufa_id AND ativo=1')->execute($dados);
            registrar_auditoria('cultivo_atualizado', $usuarioId, $estufaId, ['cultivo_id' => (int) $cultivo['id']]);
            $mensagem = 'Informações compartilhadas do cultivo foram atualizadas.';
        } else {
            throw new InvalidArgumentException('Ação inválida.');
        }
        $cultivo = cultivo_atual($estufaId);
    } catch (Throwable $falha) {
        $erro = $falha instanceof InvalidArgumentException ? $falha->getMessage() : 'Não foi possível salvar o cultivo.';
        error_log('Falha no cultivo: ' . $falha->getMessage());
    }
}

$diario = banco()->prepare('SELECT d.descricao, d.registrado_em, u.nome FROM diario_cultivo d JOIN usuarios u ON u.id=d.usuario_id WHERE d.cultivo_id=:cultivo_id ORDER BY d.id DESC LIMIT 30');
$diario->execute(['cultivo_id' => $cultivo['id']]);
$eventos = $diario->fetchAll();
$paginaAtual = 'cultivo';
require 'cabecalho.php';
?>
<main>
    <section class="heading"><div><p class="eyebrow">GESTÃO DO CULTIVO</p><h1>Ciclo de produção</h1><p>Estas informações são compartilhadas com todos os usuários da estufa.</p></div></section>
    <?php if ($mensagem): ?><p class="alert alert-success"><?= htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
    <?php if ($erro): ?><p class="alert alert-error"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
    <form class="settings-form" method="post">
        <?= campo_csrf() ?><input type="hidden" name="acao" value="salvar">
        <div class="form-grid">
            <div><label for="cultura">Cultura</label><input id="cultura" name="cultura" maxlength="100" value="<?= htmlspecialchars((string) ($cultivo['cultura'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="Ex.: Alface"></div>
            <div><label for="variedade">Variedade</label><input id="variedade" name="variedade" maxlength="100" value="<?= htmlspecialchars((string) ($cultivo['variedade'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
            <div><label for="plantio">Data do plantio</label><input id="plantio" name="plantio" type="date" value="<?= htmlspecialchars((string) ($cultivo['data_plantio'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
            <div><label for="colheita">Previsão de colheita</label><input id="colheita" name="colheita" type="date" value="<?= htmlspecialchars((string) ($cultivo['previsao_colheita'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
            <div><label for="fase">Fase atual</label><input id="fase" name="fase" maxlength="100" value="<?= htmlspecialchars((string) ($cultivo['fase'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="Ex.: Crescimento vegetativo"></div>
            <div><label for="plantas">Quantidade de plantas</label><input id="plantas" name="plantas" type="number" min="0" max="1000000" value="<?= htmlspecialchars((string) ($cultivo['quantidade_plantas'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
            <div><label for="setor">Setor</label><input id="setor" name="setor" maxlength="100" value="<?= htmlspecialchars((string) ($cultivo['setor'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
        </div>
        <label for="observacoes">Observações</label><textarea id="observacoes" name="observacoes" maxlength="2000" rows="4"><?= htmlspecialchars((string) ($cultivo['observacoes'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
        <button class="primary-button" type="submit">Salvar cultivo</button>
    </form>
    <section class="section-heading"><div><p class="eyebrow">HISTÓRICO OPERACIONAL</p><h2>Diário da estufa</h2></div></section>
    <form class="event-form" method="post"><?= campo_csrf() ?><input type="hidden" name="acao" value="evento"><label for="evento">Novo registro</label><div><input id="evento" name="evento" maxlength="500" required placeholder="Ex.: Irrigação realizada, sensor calibrado..."><button class="primary-button" type="submit">Adicionar</button></div></form>
    <section class="event-list">
        <?php if (!$eventos): ?><p class="empty-alerts">Nenhum acontecimento registrado.</p><?php endif; ?>
        <?php foreach ($eventos as $item): ?><article><time><?= htmlspecialchars(date('d/m/Y H:i', strtotime($item['registrado_em'])), ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars($item['nome'], ENT_QUOTES, 'UTF-8') ?></time><p><?= htmlspecialchars($item['descricao'], ENT_QUOTES, 'UTF-8') ?></p></article><?php endforeach; ?>
    </section>
</main>
<?php require 'rodape.php'; ?>
