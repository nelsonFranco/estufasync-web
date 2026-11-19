// Mostra a hora atual na página de boas-vindas.
let relogio = document.getElementById('live-clock');

if (relogio) {
    function atualizarRelogio() {
        let agora = new Date();
        relogio.innerHTML = agora.toLocaleTimeString('pt-BR', {
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    atualizarRelogio();
    setInterval(atualizarRelogio, 1000);
}

// Abre e fecha o menu lateral em telas pequenas.
let botaoMenu = document.getElementById('menu-button');
let barraLateral = document.getElementById('sidebar');
let fundoMenu = document.getElementById('sidebar-overlay');

function fecharMenu() {
    if (!barraLateral || !botaoMenu) return;
    barraLateral.classList.remove('open');
    document.body.classList.remove('menu-open');
    botaoMenu.setAttribute('aria-expanded', 'false');
}

if (botaoMenu && barraLateral) {
    botaoMenu.addEventListener('click', function() {
        let aberto = barraLateral.classList.toggle('open');
        document.body.classList.toggle('menu-open', aberto);
        botaoMenu.setAttribute('aria-expanded', aberto ? 'true' : 'false');
    });

    fundoMenu.addEventListener('click', fecharMenu);
}

let formularioEstufa = document.querySelector('form.settings-form');
let temperaturaMinima = document.getElementById('temperatura_min');
let temperaturaMaxima = document.getElementById('temperatura_max');
let umidadeMinima = document.getElementById('umidade_min');
let umidadeMaxima = document.getElementById('umidade_max');

if (formularioEstufa && temperaturaMinima && temperaturaMaxima) {
    formularioEstufa.addEventListener('submit', function(evento) {
        if (Number(temperaturaMinima.value) >= Number(temperaturaMaxima.value)) {
            evento.preventDefault();
            temperaturaMinima.setCustomValidity('O mínimo deve ser menor que o máximo.');
            temperaturaMinima.reportValidity();
            return;
        }
        temperaturaMinima.setCustomValidity('');
        if (Number(umidadeMinima.value) >= Number(umidadeMaxima.value)) {
            evento.preventDefault();
            umidadeMinima.setCustomValidity('O mínimo deve ser menor que o máximo.');
            umidadeMinima.reportValidity();
            return;
        }
        umidadeMinima.setCustomValidity('');
    });
}

let perfilAlerta = document.getElementById('perfil_alerta');
if (perfilAlerta) perfilAlerta.addEventListener('change', function() {
    let perfis = {
        alface: [15, 24, 60, 75],
        tomate: [18, 27, 55, 75],
        mudas: [20, 26, 65, 80]
    };
    let valores = perfis[perfilAlerta.value];
    if (!valores) return;
    temperaturaMinima.value = valores[0]; temperaturaMaxima.value = valores[1];
    umidadeMinima.value = valores[2]; umidadeMaxima.value = valores[3];
});

// Avisa quando existem mudanças ainda não salvas em um formulário de configuração.
let formularioComAlteracoes = false;
document.querySelectorAll('.settings-form').forEach(function(formulario) {
    formulario.addEventListener('input', function() { formularioComAlteracoes = true; });
    formulario.addEventListener('submit', function() { formularioComAlteracoes = false; });
});
window.addEventListener('beforeunload', function(evento) {
    if (!formularioComAlteracoes) return;
    evento.preventDefault();
    evento.returnValue = '';
});

if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() { navigator.serviceWorker.register('../service-worker.js').catch(function() {}); });
}
