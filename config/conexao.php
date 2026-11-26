<?php
// Configuração e conexão com o banco de dados (MySQL/MariaDB via mysqli procedural).
// Ajuste as constantes abaixo se o seu MySQL tiver outro usuário ou senha.

define('DB_HOST', 'localhost');
define('DB_USUARIO', 'root');
define('DB_SENHA', '');
define('DB_NOME', getenv('FISCALIZA_DB') ?: 'fiscaliza');

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
        $pagina_erro = __DIR__ . '/../includes/erro_banco.php';
        if (is_file($pagina_erro)) {
            require $pagina_erro;
        } else {
            http_response_code(503);
            echo 'Não foi possível conectar ao banco de dados.';
        }
        exit;
    }
    return $conn;
}
