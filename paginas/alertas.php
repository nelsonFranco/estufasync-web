<?php
require 'proteger.php';

$paginaAtual = 'alertas';
require 'cabecalho.php';
?>

<main>
    <section class="compact-intro">
        <div>
            <p class="eyebrow">MONITORAMENTO</p>
            <h1>Alertas</h1>
            <p>Acompanhe somente as leituras que precisam de atenção.</p>
        </div>
        <div class="compact-status"><i></i> Atualização automática</div>
    </section>

    <section class="alert-summary" aria-live="polite">
        <strong id="alert-page-count">--</strong>
        <span id="alert-page-message">Consultando as leituras atuais...</span>
    </section>
    <section class="alerts-list" id="alerts-list" aria-live="polite">
        <p class="empty-alerts">Aguardando dados do ThingSpeak.</p>
    </section>
    <div class="alert-actions"><button class="secondary-button" id="enable-notifications" type="button">Ativar notificações</button><a class="text-button" href="estufa.php">Configurar limites</a></div>
    <section class="section-heading"><div><p class="eyebrow">ACOMPANHAMENTO</p><h2>Histórico de alertas</h2></div><p>Salvo neste navegador</p></section>
    <section class="alert-history" id="alert-history"><p class="empty-alerts">Nenhum alerta registrado neste navegador.</p></section>
</main>

<?php require 'rodape.php'; ?>
