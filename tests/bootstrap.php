<?php
// Prepara o ambiente de testes: usa o banco fiscaliza_teste, recriado do zero
// a partir de database/fiscaliza.sql a cada execução.

putenv('FISCALIZA_DB=fiscaliza_teste');

$sql = file_get_contents(__DIR__ . '/../database/fiscaliza.sql');
// Troca o nome do banco só nas 3 linhas que o definem. Se alguma não for
// trocada, aborta: nunca rodar o script de testes contra o banco real.
$sql = preg_replace(
    '/^(DROP DATABASE IF EXISTS|CREATE DATABASE|USE) fiscaliza\b/m',
    '$1 fiscaliza_teste',
    $sql,
    -1,
    $trocas
);
if ($trocas !== 3) {
    fwrite(STDERR, "bootstrap: esperava 3 trocas do nome do banco, fez $trocas. Abortando.\n");
    exit(2);
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn_setup = mysqli_connect('localhost', 'root', '');
mysqli_set_charset($conn_setup, 'utf8mb4');
mysqli_multi_query($conn_setup, $sql);
do {
    $resultado = mysqli_store_result($conn_setup);
    if ($resultado instanceof mysqli_result) {
        mysqli_free_result($resultado);
    }
} while (mysqli_more_results($conn_setup) && mysqli_next_result($conn_setup));
mysqli_close($conn_setup);
unset($conn_setup, $sql, $resultado);

require_once __DIR__ . '/../config/conexao.php';
