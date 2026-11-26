<?php
require_once __DIR__ . '/../includes/usuarios.php';

iniciar_sessao();
if (usuario_logado()) {
    redirecionar('/fiscaliza/index.php');
}

$erro = null;
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validar($_POST['csrf'] ?? null)) {
        flash_definir('danger', 'Sua sessão expirou. Tente novamente.');
        redirecionar('/fiscaliza/auth/login.php');
    }
    $email = trim(post_texto('email'));
    $resultado = autenticar(db_conectar(), $email, post_texto('senha'));
    if ($resultado['usuario'] !== null) {
        login_usuario($resultado['usuario']);
        flash_definir('success', 'Olá, ' . $resultado['usuario']['nome'] . '!');
        redirecionar('/fiscaliza/index.php');
    }
    $erro = $resultado['erro'];
}

$titulo_pagina = 'Entrar';
require __DIR__ . '/../includes/cabecalho.php';
?>

<div class="conta-cartao">
    <h1>Entrar</h1>
    <p class="conta-subtitulo">Acesse sua conta para registrar e acompanhar denúncias.</p>

    <?php if ($erro): ?>
        <div class="alert alert-danger" role="alert"><?= e($erro) ?></div>
    <?php endif; ?>

    <form method="post" action="/fiscaliza/auth/login.php" novalidate>
        <?= csrf_campo() ?>
        <div class="mb-3">
            <label class="form-label" for="email">E-mail</label>
            <input class="form-control" type="email" id="email" name="email" value="<?= e($email) ?>"
                   autocomplete="email" required autofocus>
        </div>
        <div class="mb-4">
            <label class="form-label" for="senha">Senha</label>
            <input class="form-control" type="password" id="senha" name="senha"
                   autocomplete="current-password" required>
        </div>
        <button class="btn btn-laranja btn-lg w-100" type="submit">Entrar</button>
    </form>

    <p class="conta-rodape">Ainda não tem conta? <a href="/fiscaliza/auth/cadastro.php">Crie sua conta</a></p>
</div>

<?php require __DIR__ . '/../includes/rodape.php'; ?>
