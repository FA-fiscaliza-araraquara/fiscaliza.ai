<?php
// Configuração e conexão com o banco de dados (MySQL/MariaDB via mysqli procedural).
// Ajuste as constantes abaixo se o seu MySQL tiver outro usuário ou senha.

define('DB_HOST', 'localhost');
define('DB_USUARIO', 'root');
define('DB_SENHA', '');
define('DB_NOME', getenv('FISCALIZA_DB') ?: 'fiscaliza');

// Horário de Brasília para todas as datas do sistema (o php.ini do XAMPP pode vir
// com outro fuso, o que mudaria o "hoje" usado na regra dos 16 anos).
date_default_timezone_set('America/Sao_Paulo');

/** Mostra a página amigável de erro do banco e encerra (sem detalhes técnicos na tela). */
function mostrar_erro_banco(): never
{
    $pagina_erro = __DIR__ . '/../includes/erro_banco.php';
    if (is_file($pagina_erro)) {
        require $pagina_erro;
    } else {
        if (!headers_sent()) {
            http_response_code(503);
        }
        echo 'Não foi possível conectar ao banco de dados.';
    }
    exit;
}

// Qualquer erro do banco não tratado (ex.: tabela inexistente, MySQL caiu no meio
// da requisição) vai para o log do PHP e o usuário vê só a página amigável.
set_exception_handler(function (Throwable $erro): void {
    if ($erro instanceof mysqli_sql_exception) {
        error_log('Fiscaliza.ai – erro de banco: ' . $erro->getMessage());
        mostrar_erro_banco();
    }
    throw $erro;
});

function db_conectar(): mysqli
{
    static $conn = null;
    if ($conn instanceof mysqli) {
        return $conn;
    }

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    try {
        $conn = mysqli_connect(DB_HOST, DB_USUARIO, DB_SENHA, DB_NOME);
        mysqli_set_charset($conn, 'utf8mb4');
    } catch (mysqli_sql_exception $erro) {
        error_log('Fiscaliza.ai – falha ao conectar: ' . $erro->getMessage());
        mostrar_erro_banco();
    }
    return $conn;
}
