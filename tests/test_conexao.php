<?php

$conn = db_conectar();
teste('conexao_abre', $conn instanceof mysqli
    && iguais('fiscaliza_teste', mysqli_fetch_row(mysqli_query($conn, 'SELECT DATABASE()'))[0]));

teste('conexao_reutiliza', db_conectar() === db_conectar());

$stmt = mysqli_prepare($conn, 'SELECT nome FROM bairros WHERE nome = ?');
$nome = 'Jardim Ártico';
mysqli_stmt_bind_param($stmt, 's', $nome);
mysqli_stmt_execute($stmt);
$linha = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
teste('conexao_le_acentos', iguais('Jardim Ártico', $linha['nome'] ?? null));

$linha = mysqli_fetch_assoc(mysqli_query($conn, "SELECT nivel FROM usuarios WHERE email = 'admin@fiscaliza.ai'"));
teste('admin_inicial_existe', iguais('admin', $linha['nivel'] ?? null));
