<?php
// Executor de testes do Fiscaliza.ai (procedural, sem frameworks).
// Uso: C:\xampp\php\php.exe tests/run.php

$GLOBALS['total_testes'] = 0;
$GLOBALS['total_falhas'] = 0;

function teste(string $nome, bool $condicao): void
{
    $GLOBALS['total_testes']++;
    if ($condicao) {
        echo "  OK      $nome\n";
    } else {
        $GLOBALS['total_falhas']++;
        echo "  FALHOU  $nome\n";
    }
}

function iguais(mixed $esperado, mixed $obtido): bool
{
    if ($esperado === $obtido) {
        return true;
    }
    echo "          esperado: " . var_export($esperado, true) . "\n";
    echo "          obtido:   " . var_export($obtido, true) . "\n";
    return false;
}

require __DIR__ . '/bootstrap.php';

$arquivos = glob(__DIR__ . '/test_*.php');
sort($arquivos);
foreach ($arquivos as $arquivo) {
    echo basename($arquivo) . "\n";
    require $arquivo;
}

echo "\n{$GLOBALS['total_testes']} testes, {$GLOBALS['total_falhas']} falhas\n";
exit($GLOBALS['total_falhas'] > 0 ? 1 : 0);
