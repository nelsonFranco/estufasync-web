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
    <link rel="stylesheet" href="../style/style.css">
    <link rel="icon" type="image/x-icon" href="../icone.ico">
    <link rel="manifest" href="../manifest.json">
    <meta name="theme-color" content="#0d4939">
</head>
<body data-user-id="<?= (int) ($_SESSION['usuario_id'] ?? 0) ?>" data-estufa-id="<?= (int) ($_SESSION['estufa_id'] ?? 0) ?>">
    <header class="app-header">
        <a class="brand" href="boasvindas.php"><img class="logo-img" src="../icone.ico" alt="Logo Sensoriamento Inteligente para Estufas"><span>Sensoriamento Inteligente<br>para Estufas</span></a>
        <div class="top-status"><i id="monitoring-dot"></i> <?= htmlspecialchars($nomeEstufa, ENT_QUOTES, 'UTF-8') ?> <span></span> <b id="monitoring-status">Verificando monitoramento</b> <a class="profile-avatar" href="perfil.php" title="Abrir perfil" aria-label="Abrir perfil de <?= htmlspecialchars($nomeUsuario, ENT_QUOTES, 'UTF-8') ?>"><?php if ($fotoUsuario): ?><img src="<?= htmlspecialchars($fotoUsuario, ENT_QUOTES, 'UTF-8') ?>" alt=""><?php else: ?><?= htmlspecialchars($inicialUsuario, ENT_QUOTES, 'UTF-8') ?><?php endif; ?></a></div>
        <button class="menu-button" id="menu-button" type="button" aria-label="Abrir menu" aria-expanded="false">☰</button>
    </header>

    <div class="app-layout">
        <aside class="sidebar" id="sidebar">
            <p class="sidebar-label">NAVEGAÇÃO</p>
            <nav class="sidebar-nav" aria-label="Navegação principal">
                <a class="<?= $paginaAtual == 'inicio' ? 'active' : '' ?>" href="boasvindas.php"><span>⌂</span> Início</a>
                <a class="<?= $paginaAtual == 'estufa' ? 'active' : '' ?>" href="estufa.php"><span>⌂</span> Estufa</a>
                <a class="<?= $paginaAtual == 'cultivo' ? 'active' : '' ?>" href="cultivo.php"><span>♧</span> Cultivo</a>
                <a class="<?= $paginaAtual == 'manutencao' ? 'active' : '' ?>" href="manutencao.php"><span>◌</span> Componentes</a>
                <a class="<?= $paginaAtual == 'alertas' ? 'active' : '' ?>" href="alertas.php"><span>!</span> Alertas</a>
                <a class="<?= $paginaAtual == 'relatorios' ? 'active' : '' ?>" href="relatorios.php"><span>▥</span> Relatórios</a>
                <a class="<?= $paginaAtual == 'suporte' ? 'active' : '' ?>" href="suporte.php"><span>?</span> Suporte</a>
                <a class="<?= $paginaAtual == 'perfil' ? 'active' : '' ?>" href="perfil.php"><span>◉</span> Perfil</a>
            </nav>

            <form action="../logout.php" method="post">
                <?= campo_csrf() ?>
                <button class="sidebar-logout" type="submit"><span>↪</span> Sair</button>
            </form>
        </aside>
        <div class="sidebar-overlay" id="sidebar-overlay"></div>
        <div class="page-content">
