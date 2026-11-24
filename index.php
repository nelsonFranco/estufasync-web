<?php
require_once __DIR__ . '/seguranca.php';

if (isset($_SESSION['usuario_id'])) {
    header('Location: paginas/boasvindas.php');
    exit;
}

$erro = isset($_GET['erro']) ? 'Não foi possível entrar. Verifique suas credenciais ou a configuração do sistema.' : '';
$saiu = isset($_GET['saiu']) ? 'Você saiu do sistema com segurança.' : '';
$mensagemConta = isset($_GET['senha_alterada']) ? 'Senha atualizada. Entre novamente com a nova senha.' : '';
$sessaoExpirada = isset($_GET['sessao']) ? 'Sua sessão terminou. Entre novamente para continuar.' : '';
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acesso | Sensoriamento Inteligente para Estufas</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="style/style.css">
    <link rel="stylesheet" href="style/tema.css">
    <link rel="icon" type="image/x-icon" href="icone.ico">
</head>
<body class="login-page">
    <main class="login-shell">
        <section class="login-intro" aria-label="Apresentação do sistema">
            <a class="brand brand-light" href="index.php"><img class="logo-img" src="icone.ico" alt="Logo Sensoriamento Inteligente para Estufas"><span>Sensoriamento Inteligente<br>para Estufas</span></a>
            <div>
                <p class="eyebrow eyebrow-light">ESTUFA INTELIGENTE</p>
                <h1>Cultive com mais controle.</h1>
                <p>Acompanhe o ambiente da estufa e o estado dos componentes em um só lugar.</p>
            </div>
            <div class="intro-status"><i></i> Sistema pronto para monitoramento</div>
        </section>
        <section class="login-card-wrap">
            <form class="login-card" action="login.php" method="post">
                <?= campo_csrf() ?>
                <p class="eyebrow">ACESSO RESTRITO</p>
                <h2>Entrar no painel</h2>
                <p class="form-help">Use suas credenciais para acessar a estufa.</p>
                <?php if ($erro): ?><p class="alert alert-error" role="alert"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
                <?php if ($saiu): ?><p class="alert alert-success" role="status"><?= htmlspecialchars($saiu, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
                <?php if ($mensagemConta): ?><p class="alert alert-success" role="status"><?= htmlspecialchars($mensagemConta, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
                <?php if ($sessaoExpirada): ?><p class="alert alert-error" role="alert"><?= htmlspecialchars($sessaoExpirada, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
                <label for="usuario">Usuário</label>
                <input id="usuario" name="usuario" maxlength="50" autocomplete="username" required autofocus>
                <label for="senha">Senha</label>
                <input id="senha" type="password" name="senha" maxlength="128" autocomplete="current-password" required>
                <button class="primary-button" type="submit">Entrar no sistema <span>→</span></button>
                <p class="account-switch">Ainda não possui uma conta? <a href="cadastro.php">Criar cadastro</a></p>
            </form>
        </section>
    </main>
</body>
</html>
