<?php
require_once __DIR__ . '/seguranca.php';

if (isset($_SESSION['usuario_id'])) {
    header('Location: paginas/boasvindas.php');
    exit;
}

$errosDados = $_SESSION['erros_cadastro'] ?? [];
unset($_SESSION['erros_cadastro']);
$erro = match ($_GET['erro'] ?? '') {
    'dados' => $errosDados ?: ['Confira nome, usuário, e-mail, senha, confirmação e o aceite dos termos.'],
    'convite' => 'O código de convite informado não é válido para esta estufa.',
    'sistema' => 'Não foi possível criar a conta neste momento. Tente novamente.',
    default => [],
};
$status = isset($_GET['enviado']) ? 'Solicitação concluída. Se os dados estavam disponíveis, a conta já pode acessar o sistema.' : '';
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Criar conta | Sensoriamento Inteligente para Estufas</title>
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
                <p class="eyebrow eyebrow-light">PRIMEIRO ACESSO</p>
                <h1>Comece a acompanhar a estufa.</h1>
                <p>Crie sua conta para acessar, com autorização, a mesma estufa monitorada pela equipe.</p>
            </div>
            <div class="intro-status"><i></i> Cadastro protegido</div>
        </section>
        <section class="login-card-wrap">
            <form class="login-card" action="cadastrar.php" method="post">
                <?= campo_csrf() ?>
                <p class="eyebrow">NOVA CONTA</p>
                <h2>Criar cadastro</h2>
                <p class="form-help">O código de convite vinculará sua conta à estufa cadastrada.</p>
                <?php if ($erro): ?><div class="alert alert-error" role="alert"><?php if (is_array($erro)): ?><ul><?php foreach ($erro as $item): ?><li><?= htmlspecialchars($item, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?></ul><?php else: ?><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?><?php endif; ?></div><?php endif; ?>
                <?php if ($status): ?><p class="alert alert-success" role="status"><?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
                <label for="nome">Nome completo</label>
                <input id="nome" name="nome" maxlength="100" autocomplete="name" required autofocus>
                <label for="usuario">Nome de usuário</label>
                <input id="usuario" name="usuario" maxlength="50" pattern="[A-Za-z0-9_.-]{3,50}" autocomplete="username" required>
                <p class="field-hint">Use de 3 a 50 caracteres: letras, números, ponto, hífen ou sublinhado.</p>
                <label for="email">E-mail</label>
                <input id="email" name="email" type="email" maxlength="150" autocomplete="email" required>
                <label for="senha">Senha</label>
                <input id="senha" name="senha" type="password" minlength="12" maxlength="128" autocomplete="new-password" required>
                <p class="field-hint">Use de 12 a 128 caracteres, com letra maiúscula, minúscula e número.</p>
                <label for="confirmar_senha">Confirmar senha</label>
                <input id="confirmar_senha" name="confirmar_senha" type="password" minlength="12" maxlength="128" autocomplete="new-password" required>
                <label for="codigo_convite">Código de convite da estufa</label>
                <input id="codigo_convite" name="codigo_convite" type="password" maxlength="128" autocomplete="off" required>
                <label class="terms-check"><input name="aceitar_termos" type="checkbox" value="1" required> Li e aceito os <a href="termos.php" target="_blank" rel="noopener">Termos de Uso</a> e a <a href="privacidade.php" target="_blank" rel="noopener">Política de Privacidade</a>.</label>
                <button class="primary-button" type="submit">Criar conta <span>→</span></button>
                <p class="account-switch">Já possui uma conta? <a href="index.php">Fazer login</a></p>
            </form>
        </section>
    </main>
</body>
</html>
