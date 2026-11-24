<?php
require 'proteger.php';
require_once __DIR__ . '/../controles-seguranca.php';

$estufaId = (int) $_SESSION['estufa_id'];
$mensagem = '';
$erro = '';

function numero_estufa(string $campo, float $minimo, float $maximo): float
{
    $valor = filter_input(INPUT_POST, $campo, FILTER_VALIDATE_FLOAT);
    if ($valor === false || $valor === null || $valor < $minimo || $valor > $maximo) throw new InvalidArgumentException('Há um limite numérico inválido.');
    return (float) $valor;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validar_csrf();
    try {
        $dados = [
            'nome' => texto_limitado($_POST['nome'] ?? '', 100),
            'localizacao' => texto_limitado($_POST['local'] ?? '', 150),
            'cultivo' => texto_limitado($_POST['cultivo'] ?? '', 100),
            'area' => texto_limitado($_POST['area'] ?? '', 50),
            'observacoes' => texto_limitado($_POST['observacoes'] ?? '', 2000),
            'setores' => texto_limitado($_POST['setores'] ?? '', 1000),
            'perfil' => texto_limitado($_POST['perfil_alerta'] ?? '', 50),
        ];
        if ($dados['nome'] === '' || !in_array($dados['perfil'], ['personalizado', 'alface', 'tomate', 'mudas'], true)) throw new InvalidArgumentException('Dados da estufa inválidos.');
        $limites = [
            'temperatura_min' => numero_estufa('temperatura_min', -50, 100), 'temperatura_max' => numero_estufa('temperatura_max', -50, 100),
            'umidade_min' => numero_estufa('umidade_min', 0, 100), 'umidade_max' => numero_estufa('umidade_max', 0, 100),
            'wifi_min' => numero_estufa('wifi_min', -150, 0), 'pressao_min' => numero_estufa('pressao_min', 100, 1500),
            'pressao_max' => numero_estufa('pressao_max', 100, 1500), 'uv_max' => numero_estufa('uv_max', 0, 100000),
            'vento_max' => numero_estufa('vento_max', 0, 200),
        ];
        if ($limites['temperatura_min'] > $limites['temperatura_max'] || $limites['umidade_min'] > $limites['umidade_max'] || $limites['pressao_min'] > $limites['pressao_max']) throw new InvalidArgumentException('O limite mínimo não pode ser maior que o máximo.');

        $pdo = banco();
        $pdo->beginTransaction();
        $pdo->prepare('UPDATE estufas SET nome=:nome, localizacao=:localizacao, cultivo_principal=:cultivo, area=:area, observacoes=:observacoes, setores=:setores, perfil_alerta=:perfil WHERE id=:id')->execute($dados + ['id' => $estufaId]);
        $pdo->prepare('INSERT INTO configuracoes_alerta (estufa_id, temperatura_min, temperatura_max, umidade_min, umidade_max, wifi_min, pressao_min, pressao_max, uv_max, vento_max) VALUES (:estufa_id,:temperatura_min,:temperatura_max,:umidade_min,:umidade_max,:wifi_min,:pressao_min,:pressao_max,:uv_max,:vento_max) ON DUPLICATE KEY UPDATE temperatura_min=VALUES(temperatura_min),temperatura_max=VALUES(temperatura_max),umidade_min=VALUES(umidade_min),umidade_max=VALUES(umidade_max),wifi_min=VALUES(wifi_min),pressao_min=VALUES(pressao_min),pressao_max=VALUES(pressao_max),uv_max=VALUES(uv_max),vento_max=VALUES(vento_max)')->execute($limites + ['estufa_id' => $estufaId]);
        $pdo->commit();
        $_SESSION['estufa_nome'] = $dados['nome'];
        registrar_auditoria('estufa_atualizada', (int) $_SESSION['usuario_id'], $estufaId);
        $mensagem = 'Informações compartilhadas da estufa foram atualizadas.';
    } catch (Throwable $falha) {
        if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
        $erro = $falha instanceof InvalidArgumentException ? $falha->getMessage() : 'Não foi possível salvar a estufa.';
        error_log('Falha ao atualizar estufa: ' . $falha->getMessage());
    }
}

$consulta = banco()->prepare('SELECT e.*, c.temperatura_min,c.temperatura_max,c.umidade_min,c.umidade_max,c.wifi_min,c.pressao_min,c.pressao_max,c.uv_max,c.vento_max FROM estufas e LEFT JOIN configuracoes_alerta c ON c.estufa_id=e.id WHERE e.id=:id LIMIT 1');
$consulta->execute(['id' => $estufaId]);
$estufa = $consulta->fetch();
if (!$estufa) { http_response_code(404); exit('Estufa não encontrada.'); }
$padroes = ['temperatura_min'=>20,'temperatura_max'=>28,'umidade_min'=>55,'umidade_max'=>75,'wifi_min'=>-80,'pressao_min'=>950,'pressao_max'=>1050,'uv_max'=>800,'vento_max'=>10];
foreach ($padroes as $campo=>$valor) if ($estufa[$campo] === null) $estufa[$campo]=$valor;
$paginaAtual = 'estufa'; require 'cabecalho.php';
?>
<main><section class="quarteirao"><section class="heading"><div><p class="eyebrow">CONFIGURAÇÃO COMPARTILHADA</p><h1>Informações da estufa</h1><p>As alterações ficam disponíveis para todos os usuários desta estufa.</p></div></section>
<?php if($mensagem):?><p class="alert alert-success"><?=htmlspecialchars($mensagem,ENT_QUOTES,'UTF-8')?></p><?php endif;?><?php if($erro):?><p class="alert alert-error"><?=htmlspecialchars($erro,ENT_QUOTES,'UTF-8')?></p><?php endif;?>
<form class="settings-form" method="post"><?=campo_csrf()?>
<div class="form-grid"><div><label for="nome">Nome da estufa</label><input id="nome" name="nome" maxlength="100" value="<?=htmlspecialchars($estufa['nome'],ENT_QUOTES,'UTF-8')?>" required></div><div><label for="local">Localização</label><input id="local" name="local" maxlength="150" value="<?=htmlspecialchars($estufa['localizacao']??'',ENT_QUOTES,'UTF-8')?>"></div><div><label for="cultivo">Cultivo principal</label><input id="cultivo" name="cultivo" maxlength="100" value="<?=htmlspecialchars($estufa['cultivo_principal']??'',ENT_QUOTES,'UTF-8')?>"></div><div><label for="area">Área</label><input id="area" name="area" maxlength="50" value="<?=htmlspecialchars($estufa['area']??'',ENT_QUOTES,'UTF-8')?>"></div></div>
<label for="observacoes">Observações</label><textarea id="observacoes" name="observacoes" maxlength="2000" rows="5"><?=htmlspecialchars($estufa['observacoes']??'',ENT_QUOTES,'UTF-8')?></textarea>
<section class="settings-subsection"><p class="eyebrow">PADRÕES DE ALERTA</p><h2>Defina quando avisar</h2><label for="perfil_alerta">Perfil inicial</label><select id="perfil_alerta" name="perfil_alerta"><?php foreach(['personalizado'=>'Personalizado','alface'=>'Alface','tomate'=>'Tomate','mudas'=>'Mudas'] as $valor=>$rotulo):?><option value="<?=$valor?>" <?=$estufa['perfil_alerta']===$valor?'selected':''?>><?=$rotulo?></option><?php endforeach;?></select><div class="form-grid">
<?php $campos=['temperatura_min'=>'Temperatura mínima (°C)','temperatura_max'=>'Temperatura máxima (°C)','umidade_min'=>'Umidade mínima (%)','umidade_max'=>'Umidade máxima (%)','wifi_min'=>'Wi-Fi mínimo (dBm)','pressao_min'=>'Pressão mínima (hPa)','pressao_max'=>'Pressão máxima (hPa)','uv_max'=>'UVA/B máxima (mV)','vento_max'=>'Vento máximo (m/s)']; foreach($campos as $campo=>$rotulo):?><div><label for="<?=$campo?>"><?=$rotulo?></label><input id="<?=$campo?>" name="<?=$campo?>" type="number" step="0.01" value="<?=htmlspecialchars((string)$estufa[$campo],ENT_QUOTES,'UTF-8')?>" required></div><?php endforeach;?>
</div></section><section class="settings-subsection"><h2>Setores</h2><textarea name="setores" maxlength="1000" rows="3"><?=htmlspecialchars($estufa['setores']??'',ENT_QUOTES,'UTF-8')?></textarea></section><button class="primary-button" type="submit">Salvar informações</button></form></section></main>
<?php require 'rodape.php';?>
