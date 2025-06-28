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
    <title>Pagamento Pendente - Projetos de Engenharia</title>
    
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
                <div class="card shadow-lg border-warning">
                    <div class="card-header bg-warning text-dark text-center">
                        <h3 class="mb-0">
                            <i class="bi bi-clock-fill"></i>
                            Pagamento Pendente
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
                                <h4 class="text-warning">
                                    <i class="bi bi-house-fill"></i>
                                    <?= htmlspecialchars($venda['titulo']) ?>
                                </h4>
                                
                                <div class="alert alert-warning">
                                    <h6><i class="bi bi-info-circle"></i> Pagamento em Análise</h6>
                                    <p class="mb-2">Seu pagamento está sendo processado. Isso pode acontecer com:</p>
                                    <ul class="mb-0">
                                        <li>Pagamentos via PIX</li>
                                        <li>Boleto bancário</li>
                                        <li>Cartões que precisam de aprovação</li>
                                        <li>Transferências bancárias</li>
                                    </ul>
                                </div>
                                
                                <div class="alert alert-info">
                                    <h6><i class="bi bi-lightbulb"></i> Próximos Passos</h6>
                                    <ul class="mb-0">
                                        <li>Aguarde a confirmação do pagamento</li>
                                        <li>Você receberá um email quando for aprovado</li>
                                        <li>Os arquivos serão enviados automaticamente</li>
                                        <li>Tempo máximo: 2 dias úteis</li>
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
                                        <span class="badge bg-warning text-dark">Aguardando Pagamento</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="text-center">
                            <div class="alert alert-info">
                                <i class="bi bi-envelope"></i>
                                <strong>Importante:</strong> Você receberá notificações por email sobre o status do seu pagamento.
                                Em caso de dúvidas, entre em contato conosco.
                            </div>
                            
                            <div class="d-flex gap-3 justify-content-center">
                                <a href="<?= url('index.php') ?>" class="btn btn-primary">
                                    <i class="bi bi-house"></i> Voltar ao Início
                                </a>
                                <a href="<?= url('projetos.php') ?>" class="btn btn-outline-primary">
                                    <i class="bi bi-grid"></i> Ver Outros Projetos
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
