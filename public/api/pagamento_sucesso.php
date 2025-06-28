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
    <title>Pagamento Realizado - Projetos de Engenharia</title>
    
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
                <div class="card shadow-lg border-success">
                    <div class="card-header bg-success text-white text-center">
                        <h3 class="mb-0">
                            <i class="bi bi-check-circle-fill"></i>
                            Pagamento Realizado com Sucesso!
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
                                <h4 class="text-success">
                                    <i class="bi bi-house-fill"></i>
                                    <?= htmlspecialchars($venda['titulo']) ?>
                                </h4>
                                
                                <div class="alert alert-success">
                                    <h6><i class="bi bi-info-circle"></i> Próximos Passos:</h6>
                                    <ol class="mb-0">
                                        <li>Seu pagamento foi confirmado com sucesso</li>
                                        <li>Os arquivos do projeto serão enviados para <strong><?= htmlspecialchars($venda['email_cliente']) ?></strong></li>
                                        <li>Você receberá um email em até 24 horas com o link para download</li>
                                        <li>O arquivo contém plantas, documentos e especificações técnicas</li>
                                    </ol>
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
                                        <strong>Valor Pago:</strong><br>
                                        R$ <?= number_format($venda['valor_pago'], 2, ',', '.') ?>
                                    </div>
                                    <div class="col-6">
                                        <strong>Data da Compra:</strong><br>
                                        <?= date('d/m/Y H:i', strtotime($venda['data_compra'])) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="text-center">
                            <div class="alert alert-info">
                                <i class="bi bi-envelope"></i>
                                <strong>Importante:</strong> Verifique sua caixa de spam caso não receba o email em algumas horas.
                                Em caso de dúvidas, entre em contato conosco através do WhatsApp ou email.
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
