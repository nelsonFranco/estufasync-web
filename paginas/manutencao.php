<?php
require 'proteger.php';

$paginaAtual = 'manutencao';
require 'cabecalho.php';
?>

<main>
    <section class="quarteirao">
    <section class="heading">
        <div>
            <p class="eyebrow">HARDWARE E CONECTIVIDADE</p>
            <h1>Painel de componentes</h1>
            <p>Confira os sensores, o controlador e a conexão da estufa.</p>
        </div>
        <div class="connection"><i></i> Aguardando dados</div>
    </section>

    <section class="maintenance-summary">
        <article>
            <span>Componentes monitorados</span>
            <strong><span id="component-count">--</span> sensores</strong>
            <small>Com leitura disponível</small>
        </article>

        <article>
            <span>Frequência do SV10</span>
            <strong><span id="wind">--</span> Hz</strong>
            <small>ThingSpeak · Field 5</small>
        </article>

        <article>
            <span>Sinal Wi-Fi</span>
            <strong><span id="wifi-signal">--</span> dBm</strong>
            <small>ThingSpeak · Field 8</small>
        </article>
    </section>

    <section class="section-heading">
        <div>
            <p class="eyebrow">SAÚDE DOS COMPONENTES</p>
            <h2>Estado dos sensores</h2>
        </div>
    </section>

    <section class="component-health">
        <article data-sensor="dht"><span class="health-dot"></span><strong>DHT22</strong><b class="health-status">Não monitorado</b></article>
        <article data-sensor="bme"><span class="health-dot"></span><strong>BME280</strong><b class="health-status">Aguardando</b></article>
        <article data-sensor="uv"><span class="health-dot"></span><strong>GUVA-S12SD</strong><b class="health-status">Aguardando</b></article>
        <article data-sensor="sv10"><span class="health-dot"></span><strong>SV10</strong><b class="health-status">Aguardando</b></article>
    </section>

    <p class="note">Os dados de frequência e Wi-Fi são recebidos do ThingSpeak. O estado dos componentes é baseado na última leitura disponível.</p>
    </section>
</main>

<?php require 'rodape.php'; ?>
