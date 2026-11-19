<?php
require 'proteger.php';

$paginaAtual = 'suporte';
require 'cabecalho.php';
?>

<main>
    <section class="heading">
        <div>
            <p class="eyebrow">AJUDA</p>
            <h1>Suporte</h1>
            <p>Encontre orientações rápidas para utilizar o sistema.</p>
        </div>
    </section>

    <section class="support-grid">
        <article class="support-card">
            <span class="card-icon">⌂</span>
            <h2>Leituras dos sensores</h2>
            <p>A página inicial mostra as leituras mais recentes enviadas pelo ThingSpeak. Os dados são atualizados automaticamente.</p>
        </article>
        <article class="support-card">
            <span class="card-icon">!</span>
            <h2>Alertas</h2>
            <p>Consulte a página Alertas para verificar leituras fora dos limites configurados.</p>
        </article>
        <article class="support-card">
            <span class="card-icon">⚙</span>
            <h2>Configurar a estufa</h2>
            <p>Na página Estufa, altere os limites de temperatura, umidade e sinal Wi-Fi.</p>
        </article>
        <article class="support-card">
            <span class="card-icon">◌</span>
            <h2>Componentes</h2>
            <p>Use Componentes para acompanhar a situação dos sensores e do sinal do dispositivo.</p>
        </article>
    </section>

    <section class="support-contact">
        <h2>Precisa de mais ajuda?</h2>
        <p>Verifique primeiro se o dispositivo está conectado à internet e se o canal ThingSpeak recebeu uma leitura recente.</p>
    </section>
</main>

<?php require 'rodape.php'; ?>
