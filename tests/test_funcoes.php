<?php

require_once __DIR__ . '/../includes/funcoes.php';

teste('e_escapa_html', iguais('&lt;script&gt;&quot;x&quot;&#039;', e('<script>"x"\''))
    && iguais('', e(null)));

teste('limpar_texto_remove_tags_e_espacos', iguais('Poste apagado', limpar_texto("  <b>Poste</b>   apagado \n")));

teste('somente_digitos', iguais('52998224725', somente_digitos('529.982.247-25')));

teste('cpf_valido', validar_cpf('52998224725') && validar_cpf('529.982.247-25'));

teste('cpf_invalido', !validar_cpf('52998224724') && !validar_cpf('11111111111')
    && !validar_cpf('123') && !validar_cpf('') && !validar_cpf(null));

teste('formatar_data', iguais('26/11/2026 14:05', formatar_data('2026-11-26 14:05:00', true))
    && iguais('26/11/2026', formatar_data('2026-11-26'))
    && iguais('', formatar_data(null)));

$_SESSION = [];
flash_definir('success', 'Salvo');
teste('flash_ida_e_volta', iguais([['tipo' => 'success', 'mensagem' => 'Salvo']], flash_obter())
    && iguais([], flash_obter()));
