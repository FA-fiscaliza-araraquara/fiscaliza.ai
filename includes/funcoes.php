<?php
// Funções utilitárias gerais: escape de saída, sanitização, validações,
// mensagens flash e redirecionamento.

/** Escapa texto para exibição segura no HTML (prevenção de XSS). */
function e(?string $texto): string
{
    return htmlspecialchars($texto ?? '', ENT_QUOTES, 'UTF-8');
}

/** Remove tags HTML, espaços nas pontas e espaços repetidos. */
function limpar_texto(?string $texto): string
{
    $texto = strip_tags($texto ?? '');
    $texto = preg_replace('/\s+/u', ' ', $texto);
    return trim($texto);
}

function somente_digitos(?string $texto): string
{
    return preg_replace('/\D/', '', $texto ?? '');
}

/** Valida CPF (com ou sem máscara) pelos dígitos verificadores. */
function validar_cpf(?string $cpf): bool
{
    $cpf = somente_digitos($cpf);
    if (strlen($cpf) !== 11 || preg_match('/^(\d)\1{10}$/', $cpf)) {
        return false;
    }
    for ($posicao = 9; $posicao < 11; $posicao++) {
        $soma = 0;
        for ($i = 0; $i < $posicao; $i++) {
            $soma += (int) $cpf[$i] * (($posicao + 1) - $i);
        }
        $digito = ((10 * $soma) % 11) % 10;
        if ((int) $cpf[$posicao] !== $digito) {
            return false;
        }
    }
    return true;
}

/** Converte data do MySQL (Y-m-d ou Y-m-d H:i:s) para d/m/Y [H:i]. */
function formatar_data(?string $data_mysql, bool $com_hora = false): string
{
    if ($data_mysql === null || $data_mysql === '') {
        return '';
    }
    $timestamp = strtotime($data_mysql);
    return date($com_hora ? 'd/m/Y H:i' : 'd/m/Y', $timestamp);
}

/** Guarda uma mensagem para exibir na próxima página ($tipo = classe de alerta do Bootstrap). */
function flash_definir(string $tipo, string $mensagem): void
{
    $_SESSION['flash'][] = ['tipo' => $tipo, 'mensagem' => $mensagem];
}

/** Retorna as mensagens guardadas e as apaga. */
function flash_obter(): array
{
    $mensagens = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $mensagens;
}

function redirecionar(string $url): never
{
    header('Location: ' . $url);
    exit;
}

/** Item de menu com destaque quando é a página atual. */
function link_menu(string $url, string $icone, string $texto, string $pagina_atual): string
{
    $ativo = ($pagina_atual === $url);
    return '<li class="nav-item"><a class="nav-link' . ($ativo ? ' active' : '') . '"'
        . ($ativo ? ' aria-current="page"' : '') . ' href="' . e($url) . '">'
        . '<i class="bi bi-' . e($icone) . '" aria-hidden="true"></i> ' . e($texto) . '</a></li>';
}
