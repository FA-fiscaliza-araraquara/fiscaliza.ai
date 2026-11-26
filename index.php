<?php
require_once __DIR__ . '/includes/denuncias.php';

$conn = db_conectar();
$numeros = estatisticas_publicas($conn);
$ultimas = ultimas_denuncias_publicadas($conn, 6);

$titulo_pagina = 'Início';
require __DIR__ . '/includes/cabecalho.php';

$link_denunciar = ($nivel === 'cidadao') ? '/fiscaliza/denuncias/nova.php' : '/fiscaliza/auth/login.php';
?>

<section class="destaque">
    <div class="row align-items-center g-4">
        <div class="col-lg-7">
            <h1>Viu um problema na sua rua? Denuncie.</h1>
            <p class="destaque-texto">
                Buraco, poste apagado, vazamento, mato alto ou entulho na calçada: registre com foto
                e endereço, e acompanhe até a prefeitura resolver.
            </p>
            <div class="d-flex flex-wrap gap-2">
                <a class="btn btn-laranja btn-lg" href="<?= e($link_denunciar) ?>">
                    <i class="bi bi-megaphone" aria-hidden="true"></i> Fazer uma denúncia
                </a>
                <a class="btn btn-outline-secondary btn-lg" href="/fiscaliza/denuncias/index.php">Ver denúncias</a>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="painel-numeros" aria-label="Denúncias publicadas em Araraquara">
                <div class="numero numero-aberta">
                    <strong><?= $numeros['aberta'] ?></strong>
                    <span>abertas</span>
                </div>
                <div class="numero numero-em_andamento">
                    <strong><?= $numeros['em_andamento'] ?></strong>
                    <span>em andamento</span>
                </div>
                <div class="numero numero-resolvida">
                    <strong><?= $numeros['resolvida'] ?></strong>
                    <span>resolvidas</span>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="como-funciona" aria-labelledby="titulo-como-funciona">
    <h2 id="titulo-como-funciona">Como funciona</h2>
    <ol class="passos">
        <li>
            <h3>Você denuncia</h3>
            <p>Escolha o tipo de problema, informe o endereço e o bairro e anexe até 5 fotos.</p>
        </li>
        <li>
            <h3>Um agente verifica</h3>
            <p>Um agente da prefeitura confere se o relato é verdadeiro antes de publicá-lo.</p>
        </li>
        <li>
            <h3>A prefeitura resolve</h3>
            <p>Cada mudança de situação aparece no histórico da denúncia para você acompanhar.</p>
        </li>
    </ol>
</section>

<section aria-labelledby="titulo-ultimas">
    <div class="d-flex justify-content-between align-items-baseline mb-3">
        <h2 id="titulo-ultimas" class="mb-0">Últimas denúncias</h2>
        <a href="/fiscaliza/denuncias/index.php">Ver todas</a>
    </div>

    <?php if (count($ultimas) === 0): ?>
        <div class="vazio">
            <p class="mb-1">Nenhuma denúncia publicada ainda.</p>
            <p class="mb-0">Viu algum problema na cidade? <a href="<?= e($link_denunciar) ?>">Faça a primeira denúncia.</a></p>
        </div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($ultimas as $denuncia): ?>
                <div class="col-md-6 col-lg-4">
                    <a class="card h-100 cartao-denuncia" href="/fiscaliza/denuncias/ver.php?id=<?= (int) $denuncia['id'] ?>">
                        <div class="card-body">
                            <p class="cartao-categoria">
                                <i class="bi bi-<?= e($denuncia['icone']) ?>" aria-hidden="true"></i>
                                <?= e($denuncia['categoria']) ?>
                            </p>
                            <h3 class="h5"><?= e($denuncia['titulo']) ?></h3>
                            <p class="cartao-bairro"><i class="bi bi-geo-alt" aria-hidden="true"></i> <?= e($denuncia['bairro']) ?></p>
                        </div>
                        <div class="card-footer d-flex justify-content-between align-items-center">
                            <span class="badge-status badge-status-<?= e($denuncia['status']) ?>"><?= e(rotulo_status($denuncia['status'])) ?></span>
                            <small><?= e(formatar_data($denuncia['publicado_em'])) ?></small>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/includes/rodape.php'; ?>
