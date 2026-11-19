<?php
require 'proteger.php';
require_once __DIR__ . '/../controles-seguranca.php';

$usuarioId = (int) $_SESSION['usuario_id'];
$mensagem = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validar_csrf();
    $acao = (string) ($_POST['acao'] ?? 'perfil');
    try {
        if ($acao === 'senha') {
            $senhaAtual = (string) ($_POST['senha_atual'] ?? '');
            $novaSenha = (string) ($_POST['nova_senha'] ?? '');
            $confirmar = (string) ($_POST['confirmar_senha'] ?? '');
            $consultaSenha = banco()->prepare('SELECT senha_hash, estufa_id FROM usuarios WHERE id = :id LIMIT 1');
            $consultaSenha->execute(['id' => $usuarioId]);
            $conta = $consultaSenha->fetch();
            if (!$conta || !password_verify($senhaAtual, $conta['senha_hash']) || !senha_aceitavel($novaSenha) || !hash_equals($novaSenha, $confirmar)) {
                throw new InvalidArgumentException('Não foi possível alterar a senha. Revise os campos.');
            }
            banco()->prepare('UPDATE usuarios SET senha_hash = :hash, senha_alterada_em = NOW(), versao_sessao = versao_sessao + 1 WHERE id = :id')->execute(['hash' => password_hash($novaSenha, PASSWORD_DEFAULT), 'id' => $usuarioId]);
            registrar_auditoria('senha_alterada', $usuarioId, (int) $conta['estufa_id']);
            $_SESSION = [];
            session_destroy();
            header('Location: ../index.php?senha_alterada=1');
            exit;
        }

        $nome = texto_limitado($_POST['nome'] ?? '', 100);
        $usuario = texto_limitado($_POST['usuario'] ?? '', 50);
        if ($nome === '' || preg_match('/\A[A-Za-z0-9_.-]{3,50}\z/', $usuario) !== 1) {
            throw new InvalidArgumentException('Nome ou usuário inválido.');
        }

        $fotoNova = null;
        $fotoAntiga = null;
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['foto']['error'] !== UPLOAD_ERR_OK || $_FILES['foto']['size'] > 2 * 1024 * 1024) {
                throw new InvalidArgumentException('A foto deve ser válida e ter no máximo 2 MB.');
            }
            $tipos = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
            $mime = '';
            $dimensoes = @getimagesize($_FILES['foto']['tmp_name']);
            if (function_exists('finfo_open')) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = (string) finfo_file($finfo, $_FILES['foto']['tmp_name']);
                finfo_close($finfo);
            } elseif ($dimensoes && isset($dimensoes['mime'])) {
                $mime = (string) $dimensoes['mime'];
            }
            if (!isset($tipos[$mime]) || !$dimensoes || ($dimensoes['mime'] ?? '') !== $mime || $dimensoes[0] > 4096 || $dimensoes[1] > 4096) {
                throw new InvalidArgumentException('Use JPG, PNG ou WEBP com até 4096 × 4096 pixels.');
            }
            $pasta = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'perfis';
            if (!is_dir($pasta) && !mkdir($pasta, 0755, true)) throw new RuntimeException('Não foi possível preparar a pasta de fotos.');
            $nomeArquivo = 'perfil_' . bin2hex(random_bytes(16)) . '.' . $tipos[$mime];
            if (!move_uploaded_file($_FILES['foto']['tmp_name'], $pasta . DIRECTORY_SEPARATOR . $nomeArquivo)) {
                throw new RuntimeException('Não foi possível salvar a foto.');
            }
            $fotoNova = 'img/perfis/' . $nomeArquivo;
            $consultaFoto = banco()->prepare('SELECT foto_perfil FROM usuarios WHERE id = :id');
            $consultaFoto->execute(['id' => $usuarioId]);
            $fotoAntiga = $consultaFoto->fetchColumn() ?: null;
        }

        $sql = 'UPDATE usuarios SET nome = :nome, usuario = :usuario';
        $parametros = ['nome' => $nome, 'usuario' => $usuario, 'id' => $usuarioId];
        if ($fotoNova !== null) { $sql .= ', foto_perfil = :foto'; $parametros['foto'] = $fotoNova; }
        $sql .= ' WHERE id = :id';
        banco()->prepare($sql)->execute($parametros);
        if ($fotoNova && $fotoAntiga && str_starts_with($fotoAntiga, 'img/perfis/')) {
            $caminhoAntigo = dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $fotoAntiga);
            if (is_file($caminhoAntigo)) @unlink($caminhoAntigo);
        }
        $_SESSION['usuario'] = $usuario;
        $_SESSION['nome_exibicao'] = $nome;
        registrar_auditoria('perfil_atualizado', $usuarioId, (int) $_SESSION['estufa_id'], ['foto_alterada' => $fotoNova !== null]);
        $mensagem = 'Perfil atualizado com segurança.';
    } catch (Throwable $falha) {
        if (!empty($fotoNova)) {
            $caminhoNovo = dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $fotoNova);
            if (is_file($caminhoNovo)) @unlink($caminhoNovo);
        }
        $erro = $falha instanceof InvalidArgumentException ? $falha->getMessage() : 'Não foi possível salvar as alterações.';
        error_log('Falha ao atualizar perfil: ' . $falha->getMessage());
    }
}

$consulta = banco()->prepare('SELECT nome, usuario, email, foto_perfil FROM usuarios WHERE id = :id LIMIT 1');
$consulta->execute(['id' => $usuarioId]);
$perfil = $consulta->fetch();
if (!$perfil) { header('Location: ../logout.php'); exit; }
$paginaAtual = 'perfil';
require 'cabecalho.php';
?>
<main>
    <section class="heading"><div><p class="eyebrow">CONFIGURAÇÕES DA CONTA</p><h1>Meu perfil</h1><p>Atualize sua identificação, foto ou senha.</p></div></section>
    <?php if ($mensagem): ?><p class="alert alert-success"><?= htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
    <?php if ($erro): ?><p class="alert alert-error"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
    <form class="settings-form" method="post" enctype="multipart/form-data">
        <?= campo_csrf() ?><input type="hidden" name="acao" value="perfil">
        <div class="profile-photo-field"><div class="profile-photo-preview"><?php if ($perfil['foto_perfil']): ?><img src="../<?= htmlspecialchars($perfil['foto_perfil'], ENT_QUOTES, 'UTF-8') ?>" alt="Foto de perfil"><?php else: ?><span><?= htmlspecialchars(strtoupper(substr($perfil['nome'], 0, 1)), ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?></div><div><label for="foto">Foto de perfil</label><input id="foto" name="foto" type="file" accept="image/jpeg,image/png,image/webp"><p class="note">JPG, PNG ou WEBP. Até 2 MB e 4096 × 4096 pixels.</p></div></div>
        <div class="form-grid"><div><label for="nome">Nome de exibição</label><input id="nome" name="nome" maxlength="100" value="<?= htmlspecialchars($perfil['nome'], ENT_QUOTES, 'UTF-8') ?>" required></div><div><label for="usuario">Nome de usuário</label><input id="usuario" name="usuario" maxlength="50" pattern="[A-Za-z0-9_.-]{3,50}" value="<?= htmlspecialchars($perfil['usuario'], ENT_QUOTES, 'UTF-8') ?>" required></div><div><label for="email">E-mail da conta</label><input id="email" type="email" value="<?= htmlspecialchars($perfil['email'], ENT_QUOTES, 'UTF-8') ?>" readonly></div></div>
        <button class="primary-button" type="submit">Salvar perfil</button>
    </form>
    <form class="settings-form" method="post">
        <?= campo_csrf() ?><input type="hidden" name="acao" value="senha">
        <h2>Alterar senha</h2><p class="note">Ao alterar a senha, as outras sessões da conta serão invalidadas.</p>
        <div class="form-grid"><div><label for="senha_atual">Senha atual</label><input id="senha_atual" name="senha_atual" type="password" maxlength="128" autocomplete="current-password" required></div><div><label for="nova_senha">Nova senha</label><input id="nova_senha" name="nova_senha" type="password" minlength="12" maxlength="128" autocomplete="new-password" required></div><div><label for="confirmar_senha">Confirmar nova senha</label><input id="confirmar_senha" name="confirmar_senha" type="password" minlength="12" maxlength="128" autocomplete="new-password" required></div></div>
        <button class="primary-button" type="submit">Alterar senha</button>
    </form>
</main>
<?php require 'rodape.php'; ?>
