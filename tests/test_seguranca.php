<?php

require_once __DIR__ . '/../includes/seguranca.php';

$hash_admin = '2a8af6198ce9ed8a2b831d03d90c0780b757f8fa5c7d6fc154ff356eab539094';

teste('hash_admin_inicial', iguais($hash_admin, gerar_hash_senha('admin@fiscaliza.ai', 'Admin@123')));

teste('hash_ignora_maiusculas_e_espacos_do_email',
    iguais($hash_admin, gerar_hash_senha(' ADMIN@Fiscaliza.ai ', 'Admin@123')));

teste('hash_muda_com_login',
    gerar_hash_senha('ana@exemplo.com', 'Senha@123') !== gerar_hash_senha('beto@exemplo.com', 'Senha@123'));

teste('verificar_senha', verificar_senha('admin@fiscaliza.ai', 'Admin@123', $hash_admin)
    && !verificar_senha('admin@fiscaliza.ai', 'admin@123', $hash_admin));

$_SESSION = [];
teste('csrf_valido', csrf_validar(csrf_token()));

$_SESSION = [];
$sem_token_na_sessao = !csrf_validar('abc');
csrf_token();
teste('csrf_invalido', $sem_token_na_sessao && !csrf_validar('x') && !csrf_validar('') && !csrf_validar(null));

teste('nivel_permitido_sim', nivel_permitido(['nivel' => 'agente'], ['agente', 'admin']));

teste('nivel_permitido_nao', !nivel_permitido(['nivel' => 'cidadao'], ['agente', 'admin'])
    && !nivel_permitido(null, ['admin']));
