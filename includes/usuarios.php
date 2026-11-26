<?php
// Regras de usuário: validação do cadastro, criação de conta e autenticação.

require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/seguranca.php';

/** O login é o e-mail, sempre em minúsculas e sem espaços nas pontas. */
function normalizar_email(?string $email): string
{
    return strtolower(trim($email ?? ''));
}

/** Data Y-m-d válida, a partir de 1900 e não futura. */
function data_nascimento_valida(string $data): bool
{
    if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $data, $partes)
        || !checkdate((int) $partes[2], (int) $partes[3], (int) $partes[1])) {
        return false;
    }
    return $data >= '1900-01-01' && $data <= date('Y-m-d');
}

/**
 * Valida os campos do cadastro (sem consultar o banco).
 * Retorna ['erros' => [campo => mensagem], 'limpos' => [dados prontos para gravar]].
 */
function validar_cadastro(array $dados): array
{
    $limpos = [
        'nome'            => limpar_texto($dados['nome'] ?? ''),
        'sobrenome'       => limpar_texto($dados['sobrenome'] ?? ''),
        'cpf'             => somente_digitos($dados['cpf'] ?? ''),
        'email'           => normalizar_email($dados['email'] ?? ''),
        'data_nascimento' => trim($dados['data_nascimento'] ?? ''),
    ];
    $senha = (string) ($dados['senha'] ?? '');
    $confirmacao = (string) ($dados['confirmacao'] ?? '');
    $erros = [];

    $tamanho_nome = mb_strlen($limpos['nome']);
    if ($tamanho_nome < 2 || $tamanho_nome > 60) {
        $erros['nome'] = 'Informe seu nome (2 a 60 letras).';
    }

    $tamanho_sobrenome = mb_strlen($limpos['sobrenome']);
    if ($tamanho_sobrenome < 2 || $tamanho_sobrenome > 100) {
        $erros['sobrenome'] = 'Informe seu sobrenome (2 a 100 letras).';
    }

    if (!validar_cpf($limpos['cpf'])) {
        $erros['cpf'] = 'CPF inválido.';
    }

    if (strlen($limpos['email']) > 150 || !filter_var($limpos['email'], FILTER_VALIDATE_EMAIL)) {
        $erros['email'] = 'E-mail inválido.';
    }

    if (!data_nascimento_valida($limpos['data_nascimento'])) {
        $erros['data_nascimento'] = 'Data de nascimento inválida.';
    } elseif ($limpos['data_nascimento'] > date('Y-m-d', strtotime('-16 years'))) {
        $erros['data_nascimento'] = 'Você precisa ter pelo menos 16 anos para se cadastrar.';
    }

    if (strlen($senha) < 8 || !preg_match('/\p{L}/u', $senha) || !preg_match('/\d/', $senha)) {
        $erros['senha'] = 'A senha precisa ter pelo menos 8 caracteres, com letras e números.';
    }

    if ($senha !== $confirmacao) {
        $erros['confirmacao'] = 'As senhas não conferem.';
    }

    return ['erros' => $erros, 'limpos' => $limpos];
}

function email_existe(mysqli $conn, string $email): bool
{
    $email = normalizar_email($email);
    $stmt = mysqli_prepare($conn, 'SELECT 1 FROM usuarios WHERE email = ?');
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    return mysqli_fetch_row(mysqli_stmt_get_result($stmt)) !== null;
}

function cpf_existe(mysqli $conn, string $cpf): bool
{
    $cpf = somente_digitos($cpf);
    $stmt = mysqli_prepare($conn, 'SELECT 1 FROM usuarios WHERE cpf = ?');
    mysqli_stmt_bind_param($stmt, 's', $cpf);
    mysqli_stmt_execute($stmt);
    return mysqli_fetch_row(mysqli_stmt_get_result($stmt)) !== null;
}

/** Grava um novo usuário a partir dos dados limpos de validar_cadastro(). Retorna o id. */
function criar_usuario(mysqli $conn, array $limpos, string $senha, string $nivel = 'cidadao'): int
{
    $hash = gerar_hash_senha($limpos['email'], $senha);
    $stmt = mysqli_prepare($conn, 'INSERT INTO usuarios
        (nome, sobrenome, cpf, email, data_nascimento, senha_hash, nivel)
        VALUES (?, ?, ?, ?, ?, ?, ?)');
    mysqli_stmt_bind_param($stmt, 'sssssss', $limpos['nome'], $limpos['sobrenome'], $limpos['cpf'],
        $limpos['email'], $limpos['data_nascimento'], $hash, $nivel);
    mysqli_stmt_execute($stmt);
    return (int) mysqli_insert_id($conn);
}

/**
 * Confere e-mail e senha.
 * Retorna ['usuario' => ['id','nome','nivel'] ou null, 'erro' => mensagem ou null].
 */
function autenticar(mysqli $conn, string $email, string $senha): array
{
    $email = normalizar_email($email);
    $stmt = mysqli_prepare($conn, 'SELECT id, nome, nivel, senha_hash, ativo FROM usuarios WHERE email = ?');
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $linha = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if ($linha === null || !verificar_senha($email, $senha, $linha['senha_hash'])) {
        return ['usuario' => null, 'erro' => 'E-mail ou senha incorretos.'];
    }
    if ((int) $linha['ativo'] !== 1) {
        return ['usuario' => null, 'erro' => 'Esta conta está desativada. Procure a administração.'];
    }
    return [
        'usuario' => ['id' => (int) $linha['id'], 'nome' => $linha['nome'], 'nivel' => $linha['nivel']],
        'erro'    => null,
    ];
}
