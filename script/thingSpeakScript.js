// Busca a última leitura do canal ThingSpeak.
function atualizarDados() {
    let mensagem = document.getElementById('system-message');

    if (mensagem) {
        mensagem.classList.add('loading');
    }

    fetch('../conexaoThingSpeak.php')
        .then(function(resposta) {
            if (!resposta.ok) {
                throw new Error('O ThingSpeak respondeu com erro.');
            }

            return resposta.json();
        })
        .then(function(dados) {
            function formatarNumero(valor) {
                let numero = Number(valor);

                if (isNaN(numero)) {
                    return '--';
                }

                return numero.toFixed(2).replace(/\.?0+$/, '').replace('.', ',');
            }

            function colocarValor(id, valor) {
                let elemento = document.getElementById(id);
                if (elemento) elemento.textContent = formatarNumero(valor);
            }

            colocarValor('temperature', dados.field1);
            colocarValor('humidity', dados.field2);
            colocarValor('pressure', dados.field3);
            colocarValor('uv', dados.field4);
            colocarValor('wind', dados.field5);
            colocarValor('wifi-signal', dados.field8);

            let regras = dados.alertas || {};
            let faixaTemperatura = document.getElementById('temperature-range');
            let faixaUmidade = document.getElementById('humidity-range');
            if (faixaTemperatura) faixaTemperatura.textContent = 'Faixa configurada: ' + formatarNumero(regras.temperatura_min ?? 20) + '–' + formatarNumero(regras.temperatura_max ?? 28) + ' °C';
            if (faixaUmidade) faixaUmidade.textContent = 'Faixa configurada: ' + formatarNumero(regras.umidade_min ?? 55) + '–' + formatarNumero(regras.umidade_max ?? 75) + '%';

            if (dados.created_at) {
                let data = new Date(dados.created_at);
                let atualizado = document.getElementById('updated-at');

                if (atualizado) {
                    atualizado.textContent = data.toLocaleString('pt-BR');
                }

                atualizarSaude(dados, data);
                atualizarEstadoMonitoramento(data);
            }

            atualizarAlertas(dados);
            atualizarPaginaAlertas(dados);
            atualizarResumo(dados);

            let status = document.querySelector('.connection');
            if (status) status.innerHTML = '<i></i> ThingSpeak conectado';
            if (document.getElementById('temperature')) atualizarTendencias();
            if (mensagem) mensagem.classList.remove('loading');
        })
        .catch(function(erro) {
            let status = document.querySelector('.connection');
            if (status) status.innerHTML = '<i></i> Falha na conexão';

            let titulo = document.getElementById('system-title');
            let texto = document.getElementById('system-text');
            if (titulo) titulo.textContent = 'Dados indisponíveis';
            if (texto) texto.textContent = 'Não foi possível consultar o ThingSpeak. Tente novamente.';

            let monitoramento = document.getElementById('monitoring-status');
            let ponto = document.getElementById('monitoring-dot');
            if (monitoramento) monitoramento.textContent = 'Monitoramento indisponível';
            if (ponto) ponto.classList.add('offline');

            console.log(erro.message);
        });
}

function atualizarResumo(dados) {
    let existe = function(valor) { return valor !== null && valor !== undefined && valor !== '' && !isNaN(Number(valor)); };
    let sensoresAtivos = 0;
    if (existe(dados.field1) && existe(dados.field2) && existe(dados.field3)) sensoresAtivos++;
    if (existe(dados.field4)) sensoresAtivos++;
    if (existe(dados.field5)) sensoresAtivos++;
    let alertas = obterAlertasAtuais(dados).length;

    let sensores = document.getElementById('sensor-count');
    let componentes = document.getElementById('component-count');
    let contadorAlertas = document.getElementById('alert-count');

    if (sensores) sensores.textContent = sensoresAtivos;
    if (componentes) componentes.textContent = sensoresAtivos;
    if (contadorAlertas) contadorAlertas.textContent = alertas;
}

function atualizarSaude(dados, data) {
    let minutos = (new Date() - data) / 60000;
    let leituraRecente = minutos < 5;
    let componentes = document.querySelectorAll('[data-sensor]');

    componentes.forEach(function(componente) {
        let sensor = componente.getAttribute('data-sensor');

        // DHT22 usa os fields 6 e 7, que ainda não serão exibidos nesta versão.
        if (sensor == 'dht') return;

        let campoExiste = true;

        if (sensor == 'bme') campoExiste = dados.field1 !== null && dados.field2 !== null && dados.field3 !== null;
        if (sensor == 'uv') campoExiste = dados.field4 !== null;
        if (sensor == 'sv10') campoExiste = dados.field5 !== null;

        let status = componente.querySelector('.health-status');
        let ponto = componente.querySelector('.health-dot');

        if (leituraRecente && campoExiste) {
            status.textContent = 'Operacional';
            ponto.classList.add('online');
        } else if (!leituraRecente) {
            status.textContent = 'Leitura antiga';
            ponto.classList.remove('online');
        } else {
            status.textContent = 'Sem leitura';
            ponto.classList.remove('online');
        }
    });
}

function obterAlertasAtuais(dados) {
    let temValor = function(valor) { return valor !== null && valor !== undefined && valor !== '' && !isNaN(Number(valor)); };
    let regras = dados.alertas || {};
    let valores = {
        temperatura: Number(dados.field1), umidade: Number(dados.field2), pressao: Number(dados.field3),
        uv: Number(dados.field4), vento: Number(dados.field5), wifi: Number(dados.field8)
    };
    let limites = {
        temperaturaMin: Number(regras.temperatura_min ?? 20), temperaturaMax: Number(regras.temperatura_max ?? 28),
        umidadeMin: Number(regras.umidade_min ?? 55), umidadeMax: Number(regras.umidade_max ?? 75),
        pressaoMin: Number(regras.pressao_min ?? 950), pressaoMax: Number(regras.pressao_max ?? 1050),
        uvMax: Number(regras.uv_max ?? 800), ventoMax: Number(regras.vento_max ?? 10), wifiMin: Number(regras.wifi_min ?? -80)
    };
    let alertas = [];
    if (temValor(dados.field1) && (valores.temperatura < limites.temperaturaMin || valores.temperatura > limites.temperaturaMax)) alertas.push({ codigo:'temperatura', titulo:'Temperatura fora da faixa', texto:'A leitura está em ' + formatarAlerta(valores.temperatura) + ' °C. Verifique ventilação, sombreamento ou aquecimento.', gravidade:'critico' });
    if (temValor(dados.field2) && (valores.umidade < limites.umidadeMin || valores.umidade > limites.umidadeMax)) alertas.push({ codigo:'umidade', titulo:'Umidade fora da faixa', texto:'A leitura está em ' + formatarAlerta(valores.umidade) + '%. Verifique irrigação e ventilação.', gravidade:'atencao' });
    if (temValor(dados.field3) && (valores.pressao < limites.pressaoMin || valores.pressao > limites.pressaoMax)) alertas.push({ codigo:'pressao', titulo:'Pressão fora da faixa', texto:'A pressão atual é ' + formatarAlerta(valores.pressao) + ' hPa.', gravidade:'atencao' });
    if (temValor(dados.field4) && valores.uv > limites.uvMax) alertas.push({ codigo:'uv', titulo:'Radiação UVA/B elevada', texto:'A saída do sensor está em ' + formatarAlerta(valores.uv) + ' mV. Avalie o sombreamento.', gravidade:'critico' });
    if (temValor(dados.field5) && valores.vento > limites.ventoMax) alertas.push({ codigo:'vento', titulo:'Vento acima do limite', texto:'A velocidade está em ' + formatarAlerta(valores.vento) + ' m/s. Verifique a estrutura e as aberturas.', gravidade:'critico' });
    if (temValor(dados.field8) && valores.wifi < limites.wifiMin) alertas.push({ codigo:'wifi', titulo:'Sinal Wi-Fi fraco', texto:'A intensidade está em ' + formatarAlerta(valores.wifi) + ' dBm. A transmissão pode ser interrompida.', gravidade:'atencao' });
    return alertas;
}

function atualizarAlertas(dados) {
    let titulo = document.getElementById('system-title');
    let texto = document.getElementById('system-text');
    if (!titulo || !texto) return;

    let alertas = obterAlertasAtuais(dados);
    let ambiente = document.getElementById('environment-title');
    if (alertas.length) {
        titulo.textContent = alertas[0].titulo;
        texto.textContent = alertas[0].texto;
        if (ambiente) ambiente.textContent = alertas.some(function(item) { return item.gravidade == 'critico'; }) ? 'Ambiente em condição crítica' : 'Ambiente requer atenção';
    } else {
        titulo.textContent = 'Leitura estável';
        texto.textContent = 'O ambiente está dentro das faixas recomendadas.';
        if (ambiente) ambiente.textContent = 'Ambiente em equilíbrio';
    }
}

function atualizarPaginaAlertas(dados) {
    let lista = document.getElementById('alerts-list');
    let contador = document.getElementById('alert-page-count');
    let mensagem = document.getElementById('alert-page-message');
    if (!lista || !contador || !mensagem) return;

    let alertas = obterAlertasAtuais(dados);

    contador.textContent = alertas.length;
    mensagem.textContent = alertas.length ? 'item(ns) precisam de atenção' : 'Nenhum alerta no momento';
    lista.replaceChildren();
    if (!alertas.length) {
        let vazio = document.createElement('div');
        vazio.className = 'empty-alerts';
        let icone = document.createElement('span'); icone.textContent = '✓';
        let titulo = document.createElement('strong'); titulo.textContent = 'Tudo certo por enquanto';
        let texto = document.createElement('p'); texto.textContent = 'As leituras monitoradas estão dentro das faixas configuradas.';
        vazio.append(icone, titulo, texto); lista.appendChild(vazio);
        salvarHistoricoAlertas(alertas);
        renderizarHistoricoAlertas();
        return;
    }
    alertas.forEach(function(alerta) {
        let artigo = document.createElement('article');
        artigo.className = 'alert-item ' + (alerta.gravidade === 'critico' ? 'critico' : 'atencao');
        let icone = document.createElement('span'); icone.className = 'alert-icon'; icone.textContent = '!';
        let conteudo = document.createElement('div');
        let titulo = document.createElement('h2'); titulo.textContent = String(alerta.titulo || 'Alerta');
        let texto = document.createElement('p'); texto.textContent = String(alerta.texto || '');
        conteudo.append(titulo, texto); artigo.append(icone, conteudo); lista.appendChild(artigo);
    });
    salvarHistoricoAlertas(alertas);
    renderizarHistoricoAlertas();
}

function formatarAlerta(valor) {
    return valor.toFixed(2).replace(/\.?0+$/, '').replace('.', ',');
}

function atualizarEstadoMonitoramento(data) {
    let minutos = (new Date() - data) / 60000;
    let texto = document.getElementById('monitoring-status');
    let ponto = document.getElementById('monitoring-dot');
    if (!texto || !ponto) return;
    if (minutos <= 5) {
        texto.textContent = 'Monitoramento ativo';
        ponto.classList.remove('offline');
    } else {
        texto.textContent = 'Leitura atrasada há ' + Math.floor(minutos) + ' min';
        ponto.classList.add('offline');
    }
}

function atualizarTendencias() {
    fetch('../historicoThingSpeak.php?results=10').then(function(resposta) { return resposta.json(); }).then(function(dados) {
        let feeds = dados.feeds || [];
        let campos = [['field1', 'temperature-trend', '°C'], ['field2', 'humidity-trend', '%'], ['field3', 'pressure-trend', 'hPa'], ['field4', 'uv-trend', 'mV'], ['field5', 'wind-trend', 'm/s']];
        campos.forEach(function(campo) {
            let valores = feeds.map(function(item) { return Number(item[campo[0]]); }).filter(function(valor) { return !isNaN(valor); });
            let elemento = document.getElementById(campo[1]);
            if (!elemento || valores.length < 2) return;
            let diferenca = valores[valores.length - 1] - valores[0];
            let seta = Math.abs(diferenca) < 0.01 ? '→ Estável' : diferenca > 0 ? '↑ Subindo' : '↓ Caindo';
            elemento.textContent = seta + ' · ' + (diferenca > 0 ? '+' : '') + formatarAlerta(diferenca) + ' ' + campo[2];
        });
    }).catch(function() {});
}

function chaveHistoricoAlertas() {
    let estufa = document.body ? document.body.dataset.estufaId : '';
    let usuario = document.body ? document.body.dataset.userId : '';
    return 'historicoAlertasEstufa:' + (estufa || 'sem-estufa') + ':usuario:' + (usuario || 'sem-usuario');
}

function lerHistoricoAlertas() {
    try {
        let valor = JSON.parse(localStorage.getItem(chaveHistoricoAlertas()) || '[]');
        return Array.isArray(valor) ? valor.slice(0, 50) : [];
    } catch (erro) { return []; }
}

function salvarHistoricoAlertas(alertasAtuais) {
    let historico = lerHistoricoAlertas();
    let agora = new Date().toISOString();
    historico.forEach(function(item) {
        if (item.status == 'ativo' && !alertasAtuais.some(function(alerta) { return alerta.codigo == item.codigo; })) {
            item.status = 'resolvido'; item.resolvidoEm = agora;
        }
    });
    alertasAtuais.forEach(function(alerta) {
        let existente = historico.find(function(item) { return item.codigo == alerta.codigo && item.status == 'ativo'; });
        if (existente) { existente.ultimaLeitura = agora; existente.texto = alerta.texto; }
        else {
            historico.unshift({ id: Date.now() + alerta.codigo, codigo: alerta.codigo, titulo: alerta.titulo, texto: alerta.texto, gravidade: alerta.gravidade, inicio: agora, ultimaLeitura: agora, status: 'ativo', reconhecido: false });
            if ('Notification' in window && Notification.permission == 'granted') new Notification(alerta.titulo, { body: alerta.texto });
        }
    });
    localStorage.setItem(chaveHistoricoAlertas(), JSON.stringify(historico.slice(0, 50)));
}

function renderizarHistoricoAlertas() {
    let lista = document.getElementById('alert-history');
    if (!lista) return;
    let historico = lerHistoricoAlertas();
    lista.replaceChildren();
    if (!historico.length) {
        let vazio = document.createElement('p'); vazio.className = 'empty-alerts'; vazio.textContent = 'Nenhum alerta registrado neste navegador.';
        lista.appendChild(vazio); return;
    }
    historico.forEach(function(item) {
        let status = item.status === 'resolvido' ? 'resolvido' : 'ativo';
        let data = new Date(item.inicio).toLocaleString('pt-BR');
        let fim = status === 'resolvido' && item.resolvidoEm ? new Date(item.resolvidoEm) : new Date();
        let duracao = Math.max(0, Math.floor((fim - new Date(item.inicio)) / 60000));
        let artigo = document.createElement('article');
        let conteudo = document.createElement('div');
        let etiqueta = document.createElement('span'); etiqueta.className = 'history-status ' + status; etiqueta.textContent = status;
        let titulo = document.createElement('h3'); titulo.textContent = String(item.titulo || 'Alerta');
        let texto = document.createElement('p'); texto.textContent = String(item.texto || '');
        let detalhes = document.createElement('small'); detalhes.textContent = 'Início: ' + data + ' · Duração: ' + duracao + ' min';
        conteudo.append(etiqueta, titulo, texto, detalhes); artigo.appendChild(conteudo);
        if (status === 'ativo' && !item.reconhecido) {
            let botao = document.createElement('button'); botao.type = 'button'; botao.dataset.ack = String(item.id); botao.textContent = 'Reconhecer'; artigo.appendChild(botao);
        }
        lista.appendChild(artigo);
    });
}

let historicoAlertas = document.getElementById('alert-history');
if (historicoAlertas) {
    historicoAlertas.addEventListener('click', function(evento) {
        let id = evento.target.getAttribute('data-ack');
        if (!id) return;
        let historico = lerHistoricoAlertas();
        let item = historico.find(function(alerta) { return alerta.id == id; });
        if (item) item.reconhecido = true;
        localStorage.setItem(chaveHistoricoAlertas(), JSON.stringify(historico));
        renderizarHistoricoAlertas();
    });
    renderizarHistoricoAlertas();
}

let botaoNotificacoes = document.getElementById('enable-notifications');
if (botaoNotificacoes) botaoNotificacoes.addEventListener('click', function() {
    if (!('Notification' in window)) { botaoNotificacoes.textContent = 'Navegador sem suporte'; return; }
    Notification.requestPermission().then(function(permissao) { botaoNotificacoes.textContent = permissao == 'granted' ? 'Notificações ativadas' : 'Notificações não autorizadas'; });
});

let botaoAtualizar = document.getElementById('refresh-data');
if (botaoAtualizar) botaoAtualizar.addEventListener('click', atualizarDados);

if (document.getElementById('monitoring-status')) {
    atualizarDados();
    setInterval(atualizarDados, 15000);
}
