<?php
require_once '../config/config.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste - Página de Contato</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Teste da Página de Contato</h1>
        <p>Se você consegue ver esta mensagem, a configuração básica está funcionando.</p>
        
        <div class="alert alert-info">
            <h4>Informações do Sistema:</h4>
            <ul>
                <li>PHP Version: <?= phpversion() ?></li>
                <li>Data/Hora: <?= date('d/m/Y H:i:s') ?></li>
                <li>Base URL: <?= BASE_URL ?></li>
            </ul>
        </div>
        
        <a href="contato.php" class="btn btn-primary">Ir para Página de Contato Original</a>
        <a href="index.php" class="btn btn-secondary">Voltar ao Início</a>
    </div>
</body>
</html>
