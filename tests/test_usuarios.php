<?php

require_once __DIR__ . '/../includes/usuarios.php';

$conn = db_conectar();

$validos = [
    'nome' => 'Ana', 'sobrenome' => 'Souza', 'cpf' => '529.982.247-25',
    'email' => ' Ana@Exemplo.com ', 'data_nascimento' => '2000-05-10',
    'senha' => 'segura123', 'confirmacao' => 'segura123',
];
$msg_data = 'Data de nascimento inválida.';
$msg_senha = 'A senha precisa ter pelo menos 8 caracteres, com letras e números.';

/** Valida $validos com alguns campos trocados e devolve só os erros. */
function erros_com(array $base, array $trocas): array
{
    return validar_cadastro(array_merge($base, $trocas))['erros'];
}

$r = validar_cadastro($validos);
teste('cadastro_valido', iguais([], $r['erros'])
    && iguais('52998224725', $r['limpos']['cpf'])
    && iguais('ana@exemplo.com', $r['limpos']['email']));

teste('cadastro_nome_curto', iguais('Informe seu nome (2 a 60 letras).', erros_com($validos, ['nome' => 'A'])['nome'] ?? null));

teste('cadastro_cpf_invalido', iguais('CPF inválido.', erros_com($validos, ['cpf' => '111.111.111-11'])['cpf'] ?? null));

teste('cadastro_email_invalido', iguais('E-mail inválido.', erros_com($validos, ['email' => 'ana@'])['email'] ?? null));

teste('cadastro_data_futura_e_invalida',
    iguais($msg_data, erros_com($validos, ['data_nascimento' => '2999-01-01'])['data_nascimento'] ?? null)
    && iguais($msg_data, erros_com($validos, ['data_nascimento' => '2001-02-30'])['data_nascimento'] ?? null)
    && iguais($msg_data, erros_com($validos, ['data_nascimento' => '1899-12-31'])['data_nascimento'] ?? null));

teste('cadastro_menor_de_16',
    iguais('Você precisa ter pelo menos 16 anos para se cadastrar.',
        erros_com($validos, ['data_nascimento' => date('Y-m-d', strtotime('-16 years +1 day'))])['data_nascimento'] ?? null)
    && !isset(erros_com($validos, ['data_nascimento' => date('Y-m-d', strtotime('-16 years'))])['data_nascimento']));

teste('cadastro_senha_fraca',
    iguais($msg_senha, erros_com($validos, ['senha' => 'curta1', 'confirmacao' => 'curta1'])['senha'] ?? null)
    && iguais($msg_senha, erros_com($validos, ['senha' => 'somenteletras', 'confirmacao' => 'somenteletras'])['senha'] ?? null)
    && iguais($msg_senha, erros_com($validos, ['senha' => '12345678', 'confirmacao' => '12345678'])['senha'] ?? null));

teste('cadastro_confirmacao_diferente',
    iguais('As senhas não conferem.', erros_com($validos, ['confirmacao' => 'outra1234'])['confirmacao'] ?? null));

$limpos = validar_cadastro($validos)['limpos'];
$id_ana = criar_usuario($conn, $limpos, 'segura123');
teste('criar_e_detectar_duplicados', $id_ana > 0
    && email_existe($conn, 'ANA@exemplo.com')
    && cpf_existe($conn, '529.982.247-25')
    && !email_existe($conn, 'outra@exemplo.com'));

teste('email_existe_normaliza', email_existe($conn, ' Ana@Exemplo.com '));

$stmt = mysqli_prepare($conn, 'SELECT nivel, senha_hash FROM usuarios WHERE id = ?');
mysqli_stmt_bind_param($stmt, 'i', $id_ana);
mysqli_stmt_execute($stmt);
$gravado = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
teste('criar_usuario_nivel_cidadao', iguais('cidadao', $gravado['nivel'])
    && iguais(gerar_hash_senha('ana@exemplo.com', 'segura123'), $gravado['senha_hash']));

$r = autenticar($conn, 'ana@exemplo.com', 'segura123');
teste('autenticar_ok', iguais(null, $r['erro'])
    && iguais('Ana', $r['usuario']['nome'] ?? null)
    && iguais('cidadao', $r['usuario']['nivel'] ?? null)
    && iguais($id_ana, $r['usuario']['id'] ?? null));

teste('autenticar_ignora_maiusculas', iguais('Ana', autenticar($conn, ' ANA@exemplo.com', 'segura123')['usuario']['nome'] ?? null));

$senha_errada = autenticar($conn, 'ana@exemplo.com', 'errada123');
$email_inexistente = autenticar($conn, 'ninguem@exemplo.com', 'segura123');
teste('autenticar_mensagem_igual', iguais('E-mail ou senha incorretos.', $senha_errada['erro'])
    && iguais($senha_errada['erro'], $email_inexistente['erro'])
    && $senha_errada['usuario'] === null && $email_inexistente['usuario'] === null);

mysqli_query($conn, "UPDATE usuarios SET ativo = 0 WHERE email = 'ana@exemplo.com'");
$r = autenticar($conn, 'ana@exemplo.com', 'segura123');
teste('autenticar_conta_inativa', iguais('Esta conta está desativada. Procure a administração.', $r['erro'])
    && $r['usuario'] === null);

teste('autenticar_admin_inicial', iguais('admin', autenticar($conn, 'admin@fiscaliza.ai', 'Admin@123')['usuario']['nivel'] ?? null));
