<?php

require_once __DIR__ . '/../includes/denuncias.php';

$conn = db_conectar();

teste('estatisticas_banco_vazio', iguais(
    ['aberta' => 0, 'em_andamento' => 0, 'resolvida' => 0, 'total' => 0],
    estatisticas_publicas($conn)
));

// Dados de exemplo: autor = admin (id 1), categoria 1, bairro 1.
$exemplos = [
    ['Pendente',  'pendente',  null],
    ['Rejeitada', 'rejeitada', null],
    ['Aberta A',  'aberta',    '2026-11-20 10:00:00'],
    ['Aberta B',  'aberta',    '2026-11-24 10:00:00'],
    ['Resolvida', 'resolvida', '2026-11-22 10:00:00'],
];
$stmt = mysqli_prepare($conn, "INSERT INTO denuncias
    (titulo, descricao, endereco, categoria_id, bairro_id, autor_id, status, publicado_em)
    VALUES (?, 'Descrição de teste', 'Rua de teste, 100', 1, 1, 1, ?, ?)");
foreach ($exemplos as [$titulo, $status, $publicado_em]) {
    mysqli_stmt_bind_param($stmt, 'sss', $titulo, $status, $publicado_em);
    mysqli_stmt_execute($stmt);
}

teste('estatisticas_ignora_pendente_e_rejeitada', iguais(
    ['aberta' => 2, 'em_andamento' => 0, 'resolvida' => 1, 'total' => 3],
    estatisticas_publicas($conn)
));

$ultimas = ultimas_denuncias_publicadas($conn, 2);
$status_obtidos = array_column($ultimas, 'status');
teste('ultimas_so_publicas_e_ordenadas', count($ultimas) === 2
    && !in_array('pendente', $status_obtidos, true)
    && !in_array('rejeitada', $status_obtidos, true)
    && iguais('Aberta B', $ultimas[0]['titulo'])
    && iguais('Resolvida', $ultimas[1]['titulo'])
    && $ultimas[0]['categoria'] !== '' && $ultimas[0]['icone'] !== '' && $ultimas[0]['bairro'] !== '');

teste('rotulo_status', iguais('Em andamento', rotulo_status('em_andamento')));
