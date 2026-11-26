<?php
// Consultas e auxiliares de denúncias.

require_once __DIR__ . '/../config/conexao.php';

/** Status que aparecem publicamente (denúncias aprovadas por um agente). */
const STATUS_PUBLICOS = ['aberta', 'em_andamento', 'resolvida'];

function rotulo_status(string $status): string
{
    $rotulos = [
        'pendente'     => 'Pendente',
        'rejeitada'    => 'Rejeitada',
        'aberta'       => 'Aberta',
        'em_andamento' => 'Em andamento',
        'resolvida'    => 'Resolvida',
    ];
    return $rotulos[$status] ?? $status;
}

/** Quantidade de denúncias públicas por status, mais o total. */
function estatisticas_publicas(mysqli $conn): array
{
    $numeros = ['aberta' => 0, 'em_andamento' => 0, 'resolvida' => 0];
    $resultado = mysqli_query($conn, "SELECT status, COUNT(*) AS qtd FROM denuncias
        WHERE status IN ('aberta', 'em_andamento', 'resolvida') GROUP BY status");
    while ($linha = mysqli_fetch_assoc($resultado)) {
        $numeros[$linha['status']] = (int) $linha['qtd'];
    }
    $numeros['total'] = array_sum($numeros);
    return $numeros;
}

/** Últimas denúncias publicadas, da mais recente para a mais antiga. */
function ultimas_denuncias_publicadas(mysqli $conn, int $limite): array
{
    $stmt = mysqli_prepare($conn, "SELECT d.id, d.titulo, d.status, d.publicado_em,
            c.nome AS categoria, c.icone, b.nome AS bairro
        FROM denuncias d
        JOIN categorias c ON c.id = d.categoria_id
        JOIN bairros b ON b.id = d.bairro_id
        WHERE d.status IN ('aberta', 'em_andamento', 'resolvida')
        ORDER BY d.publicado_em DESC, d.id DESC
        LIMIT ?");
    mysqli_stmt_bind_param($stmt, 'i', $limite);
    mysqli_stmt_execute($stmt);
    return mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
}
