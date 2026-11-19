<?php
require 'proteger.php';

$canalThingSpeak = (string) (configuracao_app()['thingspeak_canal'] ?? '');
if (!preg_match('/\A\d+\z/', $canalThingSpeak)) $canalThingSpeak = '0';
$urlGrafico = 'https://thingspeak.mathworks.com/channels/' . rawurlencode($canalThingSpeak) . '/charts/';

$paginaAtual = 'inicio';
require 'cabecalho.php';
?>

<main>
    <p class="page-greeting">Olá, <?= htmlspecialchars($_SESSION['perfil']['nome'] ?? $_SESSION['nome_exibicao'] ?? 'Usuário', ENT_QUOTES, 'UTF-8') ?>!</p>
    <section class="compact-intro">
        <div>
            <p class="eyebrow">MONITORAMENTO DA ESTUFA</p>
            <h1>Leituras dos sensores</h1>
            <p>Dados atuais recebidos pelo ThingSpeak.</p>
        </div>
        <div class="compact-status"><i></i> <span id="sensor-count">--</span> sensores · <span id="alert-count">--</span> alertas</div>
    </section>

    <section class="section-heading">
        <div>
            <p class="eyebrow">RESUMO DO DIA</p>
            <h2 id="environment-title">Verificando o ambiente</h2>
        </div>
        <p>Última leitura: <strong id="updated-at">aguardando...</strong></p>
    </section>

    <section class="overview-grid">
        <article class="overview-card">
            <span class="card-icon">♨</span>
            <p>Temperatura atual</p>
            <strong><span id="temperature">--</span> °C</strong>
            <b id="temperature-range">Faixa configurada: 20–28 °C</b>
            <small class="sensor-trend" id="temperature-trend">Calculando tendência...</small>
        </article>

        <article class="overview-card">
            <span class="card-icon">♒</span>
            <p>Umidade atual</p>
            <strong><span id="humidity">--</span>%</strong>
            <b id="humidity-range">Faixa configurada: 55–75%</b>
            <small class="sensor-trend" id="humidity-trend">Calculando tendência...</small>
        </article>

        <article class="overview-card">
            <span class="card-icon">◌</span>
            <p>Pressão atual</p>
            <strong><span id="pressure">--</span> hPa</strong>
            <b>Pressão atmosférica</b>
            <small class="sensor-trend" id="pressure-trend">Calculando tendência...</small>
        </article>

        <article class="overview-card">
            <span class="card-icon">☀</span>
            <p>Saída UV</p>
            <strong><span id="uv">--</span> mV</strong>
            <b>Sensor GUVA-S12SD</b>
            <small class="sensor-trend" id="uv-trend">Calculando tendência...</small>
        </article>

        <article class="overview-card">
            <span class="card-icon">≋</span>
            <p>Velocidade do vento</p>
            <strong><span id="wind">--</span> m/s</strong>
            <b>Anemômetro</b>
            <small class="sensor-trend" id="wind-trend">Calculando tendência...</small>
        </article>
    </section>

    <section class="next-step" id="system-message">
        <div>
            <span class="card-icon">✓</span>
            <div>
                <h3 id="system-title">Aguardando dados</h3>
                <p id="system-text">Consultando a última leitura do ThingSpeak.</p>
            </div>
        </div>
        <button class="secondary-button" id="refresh-data" type="button">Atualizar agora</button>
    </section>

    <section class="section-heading">
        <div>
            <p class="eyebrow">HISTÓRICO DO THINGSPEAK</p>
            <h2>Gráficos dos sensores</h2>
        </div>
            <p>Leitura de 20 minutos</p>
    </section>

    <section class="charts-grid">
        <article class="thing-chart">
            <h3>Temperatura (°C)</h3>
            <iframe src="<?= htmlspecialchars($urlGrafico, ENT_QUOTES, 'UTF-8') ?>1?bgcolor=%23ffffff&amp;color=%23d62020&amp;dynamic=true&amp;results=80&amp;type=line&amp;update=15" title="Gráfico de temperatura em graus Celsius" scrolling="auto"></iframe>
        </article>

        <article class="thing-chart">
            <h3>Umidade (%)</h3>
            <iframe src="<?= htmlspecialchars($urlGrafico, ENT_QUOTES, 'UTF-8') ?>2?bgcolor=%23ffffff&amp;color=%23d62020&amp;dynamic=true&amp;results=80&amp;type=line&amp;update=15" title="Gráfico de umidade em porcentagem" scrolling="auto"></iframe>
        </article>

        <article class="thing-chart">
            <h3>Pressão (hPa)</h3>
            <iframe src="<?= htmlspecialchars($urlGrafico, ENT_QUOTES, 'UTF-8') ?>3?bgcolor=%23ffffff&amp;color=%23d62020&amp;dynamic=true&amp;results=80&amp;type=line&amp;update=15" title="Gráfico de pressão em hectopascais" scrolling="auto"></iframe>
        </article>

        <article class="thing-chart">
            <h3>Radiação UVA/B (mV)</h3>
            <iframe src="<?= htmlspecialchars($urlGrafico, ENT_QUOTES, 'UTF-8') ?>4?bgcolor=%23ffffff&amp;color=%23d62020&amp;dynamic=true&amp;results=80&amp;type=line&amp;update=15" title="Gráfico de radiação UVA/B em milivolts" scrolling="auto"></iframe>
        </article>

        <article class="thing-chart">
            <h3>Velocidade do vento (m/s)</h3>
            <iframe src="<?= htmlspecialchars($urlGrafico, ENT_QUOTES, 'UTF-8') ?>5?bgcolor=%23ffffff&amp;color=%23d62020&amp;dynamic=true&amp;results=80&amp;type=line&amp;update=15" title="Gráfico de velocidade do vento em metros por segundo" scrolling="auto"></iframe>
        </article>
    </section>

    <p class="note">Os valores são atualizados automaticamente pelo canal ThingSpeak.</p>
</main>

<?php require 'rodape.php'; ?>
