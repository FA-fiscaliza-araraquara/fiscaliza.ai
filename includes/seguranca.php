<?php
// Segurança: sessão, hash de senha (SHA-256 + salt do login), token CSRF,
// usuário logado e controle de acesso por nível.

require_once __DIR__ . '/funcoes.php';

function iniciar_sessao(): void
{
    // Já iniciada, ou já houve saída (não dá mais para enviar o cookie).
    if (session_status() === PHP_SESSION_ACTIVE || headers_sent()) {
        return;
    }
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
    session_start();
}

/** salt = SHA-256 do login (e-mail normalizado); hash = SHA-256(salt . senha). */
function gerar_hash_senha(string $email, string $senha): string
{
    $salt = hash('sha256', strtolower(trim($email)));
    return hash('sha256', $salt . $senha);
}

function verificar_senha(string $email, string $senha, string $hash_salvo): bool
{
    return hash_equals($hash_salvo, gerar_hash_senha($email, $senha));
}

function csrf_token(): string
{
    iniciar_sessao();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

/** Campo oculto para colocar dentro de todo <form method="post">. */
function csrf_campo(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_validar(?string $token): bool
{
    iniciar_sessao();
    if (empty($_SESSION['csrf']) || $token === null || $token === '') {
        return false;
    }
    return hash_equals($_SESSION['csrf'], $token);
}

/** Registra o login na sessão. $usuario precisa ter id, nome e nivel. */
function login_usuario(array $usuario): void
{
    session_regenerate_id(true);
    $_SESSION['usuario'] = [
        'id'    => (int) $usuario['id'],
        'nome'  => $usuario['nome'],
        'nivel' => $usuario['nivel'],
    ];
}

function usuario_logado(): ?array
{
    iniciar_sessao();
    return $_SESSION['usuario'] ?? null;
}

function nivel_permitido(?array $usuario, array $niveis): bool
{
    return $usuario !== null && in_array($usuario['nivel'] ?? null, $niveis, true);
}

function exigir_login(): void
{
    if (usuario_logado() === null) {
        flash_definir('warning', 'Faça login para continuar.');
        redirecionar('/fiscaliza/auth/login.php');
    }
}

/** Uso no topo de páginas restritas: exigir_nivel(['agente', 'admin']); */
function exigir_nivel(array $niveis): void
{
    exigir_login();
    if (!nivel_permitido(usuario_logado(), $niveis)) {
        flash_definir('danger', 'Você não tem permissão para acessar esta página.');
        redirecionar('/fiscaliza/index.php');
    }
}
