<?php
// Testes que rodam código em um processo PHP separado, para simular
// uma requisição nova (sessão não iniciada, erro não capturado etc.).

function rodar_php(string $codigo): string
{
    $temporario = tempnam(sys_get_temp_dir(), 'fz');
    $arquivo = $temporario . '.php';
    file_put_contents($arquivo, $codigo);
    // Só a saída padrão (o que iria para a página). O canal de erro recebe o
    // error_log, que no Apache vai para o arquivo de log, não para a tela.
    $nulo = PHP_OS_FAMILY === 'Windows' ? 'NUL' : '/dev/null';
    $saida = shell_exec(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($arquivo) . " 2>$nulo");
    unlink($arquivo);
    unlink($temporario);
    return (string) $saida;
}

$raiz = var_export(realpath(__DIR__ . '/..'), true);

// Uma página restrita chama exigir_nivel() ANTES do cabeçalho; a sessão do
// usuário logado precisa ser lida mesmo sem ninguém ter chamado iniciar_sessao().
rodar_php("<?php require $raiz . '/includes/seguranca.php';
    session_id('fzteste1'); iniciar_sessao();
    \$_SESSION['usuario'] = ['id' => 7, 'nome' => 'Ana', 'nivel' => 'agente'];
    session_write_close();");
$saida = rodar_php("<?php require $raiz . '/includes/seguranca.php';
    session_id('fzteste1');
    echo json_encode(usuario_logado());
    session_destroy();");
teste('usuario_logado_inicia_sessao', iguais('{"id":7,"nome":"Ana","nivel":"agente"}', trim($saida)));

// Falha do banco DEPOIS de conectar (ex.: tabelas não importadas) deve mostrar
// a página amigável, sem "Fatal error", caminho de arquivo ou nome de tabela.
$saida = rodar_php("<?php require $raiz . '/config/conexao.php';
    mysqli_query(db_conectar(), 'SELECT * FROM tabela_que_nao_existe');");
teste('erro_sql_mostra_pagina_amigavel', str_contains($saida, 'Não foi possível conectar ao banco de dados')
    && !str_contains($saida, 'Fatal') && !str_contains($saida, 'tabela_que_nao_existe')
    && !str_contains($saida, '.php'));
