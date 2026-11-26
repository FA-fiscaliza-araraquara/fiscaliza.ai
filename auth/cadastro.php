<?php
require_once __DIR__ . '/../includes/usuarios.php';

iniciar_sessao();
if (usuario_logado()) {
    redirecionar('/fiscaliza/index.php');
}

$campos = ['nome', 'sobrenome', 'cpf', 'email', 'data_nascimento'];
$valores = array_fill_keys($campos, '');
$erros = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validar($_POST['csrf'] ?? null)) {
        flash_definir('danger', 'Sua sessão expirou. Tente novamente.');
        redirecionar('/fiscaliza/auth/cadastro.php');
    }

    $dados = [];
    foreach ([...$campos, 'senha', 'confirmacao'] as $campo) {
        $dados[$campo] = post_texto($campo);
    }
    foreach ($campos as $campo) {
        $valores[$campo] = trim($dados[$campo]);
    }

    $validacao = validar_cadastro($dados);
    $erros = $validacao['erros'];
    $limpos = $validacao['limpos'];
    $conn = db_conectar();

    if (!isset($erros['cpf']) && cpf_existe($conn, $limpos['cpf'])) {
        $erros['cpf'] = 'Este CPF já está cadastrado.';
    }
    if (!isset($erros['email']) && email_existe($conn, $limpos['email'])) {
        $erros['email'] = 'Este e-mail já está cadastrado.';
    }

    if (!$erros) {
        $id = criar_usuario($conn, $limpos, $dados['senha']);
        login_usuario(['id' => $id, 'nome' => $limpos['nome'], 'nivel' => 'cidadao']);
        flash_definir('success', 'Conta criada! Bem-vindo(a), ' . $limpos['nome'] . '.');
        redirecionar('/fiscaliza/index.php');
    }
}

// Data máxima permitida no calendário: quem completa 16 anos hoje.
$data_maxima = date('Y-m-d', strtotime('-16 years'));

$titulo_pagina = 'Criar conta';
require __DIR__ . '/../includes/cabecalho.php';

/** Atributos de acessibilidade ligando o campo à sua mensagem de erro. */
function aria_erro(array $erros, string $campo): string
{
    return isset($erros[$campo]) ? ' aria-invalid="true" aria-describedby="erro-' . e($campo) . '"' : '';
}
?>

<div class="conta-cartao conta-cartao-largo">
    <h1>Criar conta</h1>
    <p class="conta-subtitulo">Com uma conta você registra denúncias e acompanha cada uma até a solução.</p>

    <?php if ($erros): ?>
        <div class="alert alert-danger" role="alert">Corrija os campos destacados abaixo.</div>
    <?php endif; ?>

    <form method="post" action="/fiscaliza/auth/cadastro.php" novalidate>
        <?= csrf_campo() ?>
        <div class="row g-3">
            <div class="col-sm-5">
                <label class="form-label" for="nome">Nome</label>
                <input class="form-control<?= classe_erro($erros, 'nome') ?>" id="nome" name="nome" maxlength="60"
                       value="<?= e($valores['nome']) ?>" autocomplete="given-name" required<?= aria_erro($erros, 'nome') ?>>
                <?= mensagem_erro($erros, 'nome') ?>
            </div>
            <div class="col-sm-7">
                <label class="form-label" for="sobrenome">Sobrenome</label>
                <input class="form-control<?= classe_erro($erros, 'sobrenome') ?>" id="sobrenome" name="sobrenome" maxlength="100"
                       value="<?= e($valores['sobrenome']) ?>" autocomplete="family-name" required<?= aria_erro($erros, 'sobrenome') ?>>
                <?= mensagem_erro($erros, 'sobrenome') ?>
            </div>
            <div class="col-sm-6">
                <label class="form-label" for="cpf">CPF</label>
                <input class="form-control<?= classe_erro($erros, 'cpf') ?>" id="cpf" name="cpf" inputmode="numeric" maxlength="14"
                       placeholder="000.000.000-00" value="<?= e($valores['cpf']) ?>" required<?= aria_erro($erros, 'cpf') ?>>
                <?= mensagem_erro($erros, 'cpf') ?>
            </div>
            <div class="col-sm-6">
                <label class="form-label" for="data_nascimento">Data de nascimento</label>
                <input class="form-control<?= classe_erro($erros, 'data_nascimento') ?>" type="date" id="data_nascimento"
                       name="data_nascimento" min="1900-01-01" max="<?= e($data_maxima) ?>"
                       value="<?= e($valores['data_nascimento']) ?>" autocomplete="bday" required<?= aria_erro($erros, 'data_nascimento') ?>>
                <?= mensagem_erro($erros, 'data_nascimento') ?>
            </div>
            <div class="col-12">
                <label class="form-label" for="email">E-mail</label>
                <input class="form-control<?= classe_erro($erros, 'email') ?>" type="email" id="email" name="email" maxlength="150"
                       value="<?= e($valores['email']) ?>" autocomplete="email" required<?= aria_erro($erros, 'email') ?>>
                <?= mensagem_erro($erros, 'email') ?>
                <div class="form-text">Você vai usar este e-mail para entrar.</div>
            </div>
            <div class="col-sm-6">
                <label class="form-label" for="senha">Senha</label>
                <input class="form-control<?= classe_erro($erros, 'senha') ?>" type="password" id="senha" name="senha"
                       autocomplete="new-password" required<?= aria_erro($erros, 'senha') ?>>
                <?= mensagem_erro($erros, 'senha') ?>
                <div class="form-text">Mínimo de 8 caracteres, com letras e números.</div>
            </div>
            <div class="col-sm-6">
                <label class="form-label" for="confirmacao">Confirme a senha</label>
                <input class="form-control<?= classe_erro($erros, 'confirmacao') ?>" type="password" id="confirmacao" name="confirmacao"
                       autocomplete="new-password" required<?= aria_erro($erros, 'confirmacao') ?>>
                <?= mensagem_erro($erros, 'confirmacao') ?>
            </div>
        </div>
        <button class="btn btn-laranja btn-lg w-100 mt-4" type="submit">Criar conta</button>
    </form>

    <p class="conta-rodape">Já tem conta? <a href="/fiscaliza/auth/login.php">Entrar</a></p>
</div>

<script>
// Máscara do CPF enquanto digita (o servidor aceita com ou sem máscara).
document.getElementById('cpf').addEventListener('input', function () {
    const d = this.value.replace(/\D/g, '').slice(0, 11);
    this.value = d.replace(/(\d{3})(\d)/, '$1.$2')
                  .replace(/(\d{3})\.(\d{3})(\d)/, '$1.$2.$3')
                  .replace(/\.(\d{3})(\d{1,2})$/, '.$1-$2');
});
</script>

<?php require __DIR__ . '/../includes/rodape.php'; ?>
