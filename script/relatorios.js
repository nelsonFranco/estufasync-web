let dadosRelatorio = [];
let regrasRelatorio = {};

function numeroRelatorio(valor) {
    let numero = Number(valor);
    return isNaN(numero) ? null : numero;
}

function formatarRelatorio(valor, unidade) {
    if (valor === null || isNaN(valor)) return '--';
    return valor.toFixed(2).replace('.', ',') + (unidade ? ' ' + unidade : '');
}

function estatisticas(campo) {
    let valores = dadosRelatorio.map(function(item) { return numeroRelatorio(item[campo]); }).filter(function(valor) { return valor !== null; });
    if (!valores.length) return { minimo: null, media: null, maximo: null };
    return {
        minimo: Math.min.apply(null, valores),
        media: valores.reduce(function(total, valor) { return total + valor; }, 0) / valores.length,
        maximo: Math.max.apply(null, valores)
    };
}

function preencherRelatorio() {
    let temperatura = estatisticas('field1');
    let umidade = estatisticas('field2');
    document.getElementById('report-temperature').textContent = formatarRelatorio(temperatura.media, '°C');
    document.getElementById('report-temperature-range').textContent = 'Mín. ' + formatarRelatorio(temperatura.minimo, '°C') + ' · Máx. ' + formatarRelatorio(temperatura.maximo, '°C');
    document.getElementById('report-humidity').textContent = formatarRelatorio(umidade.media, '%');
    document.getElementById('report-humidity-range').textContent = 'Mín. ' + formatarRelatorio(umidade.minimo, '%') + ' · Máx. ' + formatarRelatorio(umidade.maximo, '%');

    let validas = dadosRelatorio.filter(function(item) { return numeroRelatorio(item.field1) !== null && numeroRelatorio(item.field2) !== null; });
    let tMin = Number(regrasRelatorio.temperatura_min ?? 20), tMax = Number(regrasRelatorio.temperatura_max ?? 28);
    let uMin = Number(regrasRelatorio.umidade_min ?? 55), uMax = Number(regrasRelatorio.umidade_max ?? 75);
    let ideais = validas.filter(function(item) { let t = Number(item.field1); let u = Number(item.field2); return t >= tMin && t <= tMax && u >= uMin && u <= uMax; });
    let percentualIdeal = validas.length ? (ideais.length / validas.length) * 100 : 0;
    document.getElementById('report-ideal').textContent = formatarRelatorio(percentualIdeal, '%');
    document.getElementById('report-availability').textContent = formatarRelatorio((validas.length / Math.max(dadosRelatorio.length, 1)) * 100, '%');

    let sensores = [['Temperatura', 'field1', '°C'], ['Umidade', 'field2', '%'], ['Pressão', 'field3', 'hPa'], ['Radiação UVA/B', 'field4', 'mV'], ['Vento', 'field5', 'm/s']];
    document.getElementById('report-table-body').innerHTML = sensores.map(function(sensor) {
        let dados = estatisticas(sensor[1]);
        return '<tr><td>' + sensor[0] + '</td><td>' + formatarRelatorio(dados.minimo, '') + '</td><td>' + formatarRelatorio(dados.media, '') + '</td><td>' + formatarRelatorio(dados.maximo, '') + '</td><td>' + sensor[2] + '</td></tr>';
    }).join('');
}

function exportarCsv() {
    if (!dadosRelatorio.length) return;

    function textoCsv(valor) {
        return '"' + String(valor ?? '').replace(/"/g, '""') + '"';
    }

    function numeroCsv(valor) {
        let numero = Number(valor);
        if (valor === null || valor === '' || isNaN(numero)) return '';
        return numero.toFixed(2).replace('.', ',');
    }

    let cabecalho = ['Data', 'Hora', 'Temperatura (°C)', 'Umidade (%)', 'Pressão (hPa)', 'Radiação UVA/B (mV)', 'Velocidade do vento (m/s)', 'Sinal Wi-Fi (dBm)'];
    let linhas = ['sep=;', cabecalho.map(textoCsv).join(';')];

    dadosRelatorio.forEach(function(item) {
        let data = new Date(item.created_at);
        let dataFormatada = isNaN(data.getTime()) ? '' : data.toLocaleDateString('pt-BR');
        let horaFormatada = isNaN(data.getTime()) ? '' : data.toLocaleTimeString('pt-BR');
        let colunas = [
            textoCsv(dataFormatada),
            textoCsv(horaFormatada),
            numeroCsv(item.field1),
            numeroCsv(item.field2),
            numeroCsv(item.field3),
            numeroCsv(item.field4),
            numeroCsv(item.field5),
            numeroCsv(item.field8)
        ];
        linhas.push(colunas.join(';'));
    });

    let conteudo = '\uFEFF' + linhas.join('\r\n');
    let arquivo = new Blob([conteudo], { type: 'text/csv;charset=utf-8' });
    let link = document.createElement('a');
    link.href = URL.createObjectURL(arquivo);
    let hoje = new Date().toISOString().slice(0, 10);
    link.download = 'relatorio-estufa-' + hoje + '.csv';
    link.click();
    URL.revokeObjectURL(link.href);
}

function carregarRelatorio() {
    let periodo = document.getElementById('report-period').value;
    let status = document.getElementById('report-status');
    status.className = 'alert'; status.textContent = 'Carregando leituras...';
    Promise.all([
        fetch('../historicoThingSpeak.php?minutes=' + periodo).then(function(resposta) { if (!resposta.ok) throw new Error(); return resposta.json(); }),
        fetch('../conexaoThingSpeak.php').then(function(resposta) { if (!resposta.ok) throw new Error(); return resposta.json(); })
    ]).then(function(resultados) {
        dadosRelatorio = resultados[0].feeds || [];
        regrasRelatorio = resultados[1].alertas || {};
        preencherRelatorio();
        status.className = 'alert alert-success';
        status.textContent = dadosRelatorio.length + ' leituras analisadas.';
    }).catch(function() {
        status.className = 'alert alert-error';
        status.textContent = 'Não foi possível carregar as leituras para o relatório.';
    });
}

if (document.getElementById('report-page')) {
    carregarRelatorio();
    document.getElementById('report-period').addEventListener('change', carregarRelatorio);
    document.getElementById('export-csv').addEventListener('click', exportarCsv);
    document.getElementById('print-report').addEventListener('click', function() { window.print(); });
}
