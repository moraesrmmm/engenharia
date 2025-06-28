<?php
require_once '../../config/config.php';

$venda_id = intval($_GET['venda_id'] ?? 0);

if (!$venda_id) {
    header("Location: " . url('index.php'));
    exit;
}

// Buscar venda
$stmt = $pdo->prepare("
    SELECT v.*, p.titulo, p.capa_imagem 
    FROM vendas v 
    INNER JOIN projetos p ON v.projeto_id = p.id 
    WHERE v.id = ?
");
$stmt->execute([$venda_id]);
$venda = $stmt->fetch();

if (!$venda) {
    header("Location: " . url('index.php'));
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento Não Aprovado - Projetos de Engenharia</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?= asset('css/style.css') ?>" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-lg border-danger">
                    <div class="card-header bg-danger text-white text-center">
                        <h3 class="mb-0">
                            <i class="bi bi-x-circle-fill"></i>
                            Pagamento Não Aprovado
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 text-center">
                                <img src="<?= asset('uploads/' . htmlspecialchars($venda['capa_imagem'])) ?>" 
                                     class="img-fluid rounded mb-3" 
                                     alt="<?= htmlspecialchars($venda['titulo']) ?>">
                            </div>
                            <div class="col-md-8">
                                <h4 class="text-danger">
                                    <i class="bi bi-house-fill"></i>
                                    <?= htmlspecialchars($venda['titulo']) ?>
                                </h4>
                                
                                <div class="alert alert-danger">
                                    <h6><i class="bi bi-exclamation-triangle"></i> O que aconteceu?</h6>
                                    <p class="mb-2">Infelizmente seu pagamento não foi aprovado. Isso pode acontecer por diversos motivos:</p>
                                    <ul class="mb-0">
                                        <li>Dados do cartão incorretos</li>
                                        <li>Limite insuficiente</li>
                                        <li>Cartão bloqueado ou vencido</li>
                                        <li>Problemas com o banco emissor</li>
                                    </ul>
                                </div>
                                
                                <div class="alert alert-info">
                                    <h6><i class="bi bi-lightbulb"></i> O que fazer agora?</h6>
                                    <ul class="mb-0">
                                        <li>Verifique os dados do seu cartão</li>
                                        <li>Entre em contato com seu banco</li>
                                        <li>Tente novamente com outro cartão</li>
                                        <li>Use PIX ou boleto bancário</li>
                                    </ul>
                                </div>
                                
                                <div class="row g-3">
                                    <div class="col-6">
                                        <strong>Cliente:</strong><br>
                                        <?= htmlspecialchars($venda['nome_cliente']) ?>
                                    </div>
                                    <div class="col-6">
                                        <strong>Email:</strong><br>
                                        <?= htmlspecialchars($venda['email_cliente']) ?>
                                    </div>
                                    <div class="col-6">
                                        <strong>Valor:</strong><br>
                                        R$ <?= number_format($venda['valor_pago'], 2, ',', '.') ?>
                                    </div>
                                    <div class="col-6">
                                        <strong>Status:</strong><br>
                                        <span class="badge bg-danger">Pagamento Rejeitado</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="text-center">
                            <div class="d-flex gap-3 justify-content-center">
                                <a href="<?= url('detalhes_projeto.php?id=' . $venda['projeto_id']) ?>" class="btn btn-success">
                                    <i class="bi bi-arrow-repeat"></i> Tentar Novamente
                                </a>
                                <a href="<?= url('index.php') ?>" class="btn btn-primary">
                                    <i class="bi bi-house"></i> Voltar ao Início
                                </a>
                                <a href="<?= url('contato.php') ?>" class="btn btn-outline-secondary">
                                    <i class="bi bi-envelope"></i> Contato
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
