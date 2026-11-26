<?php
// Sai da conta. Só aceita POST com token CSRF (o botão "Sair" do cabeçalho),
// para que outro site não consiga desconectar o usuário com um simples link.
require_once __DIR__ . '/../includes/seguranca.php';

iniciar_sessao();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_validar($_POST['csrf'] ?? null)) {
    redirecionar('/fiscaliza/index.php');
}

$_SESSION = [];
session_destroy();
iniciar_sessao();
session_regenerate_id(true);

flash_definir('info', 'Você saiu da sua conta.');
redirecionar('/fiscaliza/index.php');
