<?php
// Página exibida quando não é possível conectar ao banco de dados.
// Não usa sessão nem banco: precisa funcionar justamente quando eles falham.
if (!headers_sent()) {
    http_response_code(503);
}
?>
<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sistema indisponível | Fiscaliza.ai</title>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Semi+Condensed:wght@600;700&family=Barlow:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/fiscaliza/assets/css/estilo.css" rel="stylesheet">
</head>
<body>
<nav class="navbar barra-topo">
    <div class="container"><span class="navbar-brand placa-rua">Fiscaliza.ai</span></div>
</nav>
<main class="container py-5">
    <div class="pagina-erro">
        <h1>Sistema temporariamente indisponível</h1>
        <p>Não foi possível conectar ao banco de dados. Verifique se o MySQL está ligado no XAMPP e recarregue a página.</p>
    </div>
</main>
</body>
</html>
