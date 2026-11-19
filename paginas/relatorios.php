<?php
require 'proteger.php';
$paginaAtual = 'relatorios';
require 'cabecalho.php';
?>
<main id="report-page">
    <section class="heading"><div><p class="eyebrow">ANÁLISE DAS LEITURAS</p><h1>Relatório ambiental</h1><p>Resumo calculado com as leituras recentes do ThingSpeak.</p></div><div class="report-actions"><label for="report-period">Período</label><select id="report-period"><option value="20">20 minutos</option><option value="60">1 hora</option><option value="1440">24 horas</option></select><button class="secondary-button" id="export-csv" type="button">Exportar CSV</button><button class="primary-button" id="print-report" type="button">Imprimir relatório</button></div></section>
    <p class="alert" id="report-status">Carregando leituras...</p>
    <section class="report-grid">
        <article><span>Temperatura média</span><strong id="report-temperature">--</strong><small id="report-temperature-range">Mín. -- · Máx. --</small></article>
        <article><span>Umidade média</span><strong id="report-humidity">--</strong><small id="report-humidity-range">Mín. -- · Máx. --</small></article>
        <article><span>Tempo dentro do ideal</span><strong id="report-ideal">--</strong><small>Temperatura e umidade</small></article>
        <article><span>Disponibilidade</span><strong id="report-availability">--</strong><small>Leituras recebidas</small></article>
    </section>
    <section class="section-heading"><div><p class="eyebrow">DETALHAMENTO</p><h2>Resumo por sensor</h2></div></section>
    <div class="table-wrap"><table class="report-table"><thead><tr><th>Sensor</th><th>Mínimo</th><th>Média</th><th>Máximo</th><th>Unidade</th></tr></thead><tbody id="report-table-body"></tbody></table></div>
</main>
<?php require 'rodape.php'; ?>
