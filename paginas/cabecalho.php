<?php
// Cada página informa qual menu deve ficar marcado.
$paginaAtual = $paginaAtual ?? '';
$nomeEstufa = $_SESSION['estufa_nome'] ?? 'Estufa 01';
$nomeUsuario = $_SESSION['perfil']['nome'] ?? $_SESSION['nome_exibicao'] ?? 'Usuário';
$fotoUsuario = $_SESSION['perfil']['foto'] ?? '';
$inicialUsuario = function_exists('mb_substr') ? mb_strtoupper(mb_substr(trim($nomeUsuario), 0, 1, 'UTF-8'), 'UTF-8') : strtoupper(substr(trim($nomeUsuario), 0, 1));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sensoriamento Inteligente para Estufas</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="../style/style.css">
    <link rel="stylesheet" href="../style/tema.css">
    <link rel="icon" type="image/x-icon" href="../icone.ico">
    <link rel="manifest" href="../manifest.json">
    <meta name="theme-color" content="#11120D">
</head>
<body data-user-id="<?= (int) ($_SESSION['usuario_id'] ?? 0) ?>" data-estufa-id="<?= (int) ($_SESSION['estufa_id'] ?? 0) ?>">
    <header class="app-header" id="app-header">
        <a class="brand" href="boasvindas.php"><img class="logo-img" src="../icone.ico" alt="Logo Sensoriamento Inteligente para Estufas"><span>Sensoriamento Inteligente<br>para Estufas</span></a>
        <nav class="topnav" id="sidebar" aria-label="Navegação principal">
            <div class="topnav-links">
                <a class="<?= $paginaAtual == 'inicio' ? 'active' : '' ?>" href="boasvindas.php"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 11l9-8 9 8"/><path d="M5 10v10h14V10"/></svg><span>Painel</span></a>
                <a class="<?= $paginaAtual == 'estufa' ? 'active' : '' ?>" href="estufa.php"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 20V10a9 9 0 0 1 18 0v10"/><path d="M3 20h18"/><path d="M12 1v19"/></svg><span>Estufa</span></a>
                <a class="<?= $paginaAtual == 'cultivo' ? 'active' : '' ?>" href="cultivo.php"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21v-9"/><path d="M12 12c0-4 3-7 8-7 0 5-3 8-8 8z"/><path d="M12 15c0-3-2-5-6-5 0 4 2 6 6 6z"/></svg><span>Cultivo</span></a>
                <a class="<?= $paginaAtual == 'manutencao' ? 'active' : '' ?>" href="manutencao.php"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="6" y="6" width="12" height="12" rx="2"/><path d="M9 2v4M15 2v4M9 18v4M15 18v4M2 9h4M2 15h4M18 9h4M18 15h4"/></svg><span>Componentes</span></a>
                <a class="<?= $paginaAtual == 'alertas' ? 'active' : '' ?>" href="alertas.php"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 8 3 8H3s3-1 3-8"/><path d="M10 21a2 2 0 0 0 4 0"/></svg><span>Alertas</span></a>
                <a class="<?= $paginaAtual == 'relatorios' ? 'active' : '' ?>" href="relatorios.php"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/></svg><span>Relatórios</span></a>
                <a class="<?= $paginaAtual == 'suporte' ? 'active' : '' ?>" href="suporte.php"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M9.5 9.5a2.5 2.5 0 1 1 3.5 2.3c-.7.4-1 .9-1 1.7"/><path d="M12 17h.01"/></svg><span>Suporte</span></a>
                <a class="<?= $paginaAtual == 'perfil' ? 'active' : '' ?> nav-perfil" href="perfil.php"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21c1-4 4-6 8-6s7 2 8 6"/></svg><span>Perfil</span></a>
            </div>
            <form class="logout-form" action="../logout.php" method="post">
                <?= campo_csrf() ?>
                <button class="logout-btn" type="submit" aria-label="Sair" title="Sair"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5M21 12H9"/></svg><span>Sair</span></button>
            </form>
        </nav>
        <div class="top-status"><i id="monitoring-dot"></i> <?= htmlspecialchars($nomeEstufa, ENT_QUOTES, 'UTF-8') ?> <span></span> <b id="monitoring-status">Verificando monitoramento</b> <a class="profile-avatar" href="perfil.php" title="Abrir perfil" aria-label="Abrir perfil de <?= htmlspecialchars($nomeUsuario, ENT_QUOTES, 'UTF-8') ?>"><?php if ($fotoUsuario): ?><img src="<?= htmlspecialchars($fotoUsuario, ENT_QUOTES, 'UTF-8') ?>" alt=""><?php else: ?><?= htmlspecialchars($inicialUsuario, ENT_QUOTES, 'UTF-8') ?><?php endif; ?></a></div>
        <button class="menu-button" id="menu-button" type="button" aria-label="Abrir menu" aria-expanded="false" aria-controls="sidebar">☰</button>
    </header>

    <div class="app-layout">
        <div class="sidebar-overlay" id="sidebar-overlay"></div>
        <div class="page-content">
        <script>
        (function () {
            var header = document.getElementById('app-header');
            var nav = document.getElementById('sidebar');
            var btn = document.getElementById('menu-button');
            if (!header || !nav || !btn) return;
            function setMenu(aberto) {
                nav.classList.toggle('open', aberto);
                document.body.classList.toggle('menu-open', aberto);
                btn.setAttribute('aria-expanded', aberto ? 'true' : 'false');
                btn.textContent = aberto ? '✕' : '☰';
            }
            document.addEventListener('click', function (e) {
                if (e.target.closest('#menu-button')) { e.stopImmediatePropagation(); setMenu(!nav.classList.contains('open')); return; }
                if (e.target.closest('#sidebar-overlay') || e.target.closest('#sidebar a')) setMenu(false);
            }, true);
            document.addEventListener('keydown', function (e) { if (e.key === 'Escape') setMenu(false); });
            window.addEventListener('resize', function () { if (window.innerWidth > 1080) setMenu(false); });
            var ultimoY = Math.max(window.scrollY, 0), agendado = false, folga = 8;
            function aoRolar() {
                var y = Math.max(window.scrollY, 0);
                header.classList.toggle('scrolled', y > 4);
                if (Math.abs(y - ultimoY) > folga) ultimoY = y;
                agendado = false;
            }
            window.addEventListener('scroll', function () { if (!agendado) { agendado = true; requestAnimationFrame(aoRolar); } }, { passive: true });
            aoRolar();
        })();
        </script>
