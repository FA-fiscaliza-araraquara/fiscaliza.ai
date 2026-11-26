<?php
// Cabeçalho padrão de todas as páginas.
// Antes de incluir, defina: $titulo_pagina = 'Nome da página';

require_once __DIR__ . '/seguranca.php';
iniciar_sessao();

$usuario = usuario_logado();
$nivel = $usuario['nivel'] ?? null;
$pagina_atual = $_SERVER['SCRIPT_NAME'] ?? '';
?>
<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($titulo_pagina ?? 'Fiscaliza.ai') ?> | Fiscaliza.ai</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Semi+Condensed:wght@600;700&family=Barlow:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="/fiscaliza/assets/css/estilo.css" rel="stylesheet">
</head>
<body>
<a class="visually-hidden-focusable pular-conteudo" href="#conteudo">Pular para o conteúdo</a>

<nav class="navbar navbar-expand-lg navbar-dark barra-topo">
    <div class="container">
        <a class="navbar-brand placa-rua" href="/fiscaliza/index.php">Fiscaliza.ai</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu"
                aria-controls="menu" aria-expanded="false" aria-label="Abrir menu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="menu">
            <ul class="navbar-nav me-auto">
                <?= link_menu('/fiscaliza/index.php', 'house', 'Início', $pagina_atual) ?>
                <?= link_menu('/fiscaliza/denuncias/index.php', 'geo-alt', 'Denúncias', $pagina_atual) ?>
                <?php if ($nivel === 'cidadao'): ?>
                    <?= link_menu('/fiscaliza/denuncias/nova.php', 'plus-circle', 'Nova denúncia', $pagina_atual) ?>
                    <?= link_menu('/fiscaliza/denuncias/minhas.php', 'list-check', 'Minhas denúncias', $pagina_atual) ?>
                <?php endif; ?>
                <?php if ($nivel === 'agente' || $nivel === 'admin'): ?>
                    <?= link_menu('/fiscaliza/agente/moderacao.php', 'shield-check', 'Moderação', $pagina_atual) ?>
                    <?= link_menu('/fiscaliza/relatorios/index.php', 'bar-chart', 'Relatórios', $pagina_atual) ?>
                <?php endif; ?>
                <?php if ($nivel === 'admin'): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-gear" aria-hidden="true"></i> Administração
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/fiscaliza/admin/usuarios.php">Usuários</a></li>
                            <li><a class="dropdown-item" href="/fiscaliza/admin/categorias.php">Categorias</a></li>
                            <li><a class="dropdown-item" href="/fiscaliza/admin/bairros.php">Bairros</a></li>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav">
                <?php if ($usuario): ?>
                    <li class="nav-item"><span class="navbar-text me-lg-3">
                        <i class="bi bi-person-circle" aria-hidden="true"></i> <?= e($usuario['nome']) ?>
                    </span></li>
                    <li class="nav-item">
                        <form method="post" action="/fiscaliza/auth/logout.php" class="m-0">
                            <?= csrf_campo() ?>
                            <button type="submit" class="nav-link botao-sair">
                                <i class="bi bi-box-arrow-right" aria-hidden="true"></i> Sair
                            </button>
                        </form>
                    </li>
                <?php else: ?>
                    <?= link_menu('/fiscaliza/auth/login.php', 'box-arrow-in-right', 'Entrar', $pagina_atual) ?>
                    <li class="nav-item ms-lg-2"><a class="btn btn-laranja btn-sm mt-1 mt-lg-0" href="/fiscaliza/auth/cadastro.php">Cadastrar-se</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<main id="conteudo" class="container py-4">
<?php foreach (flash_obter() as $aviso): ?>
    <div class="alert alert-<?= e($aviso['tipo']) ?> alert-dismissible fade show" role="alert">
        <?= e($aviso['mensagem']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
<?php endforeach; ?>
