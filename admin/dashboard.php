<?php 
require_once 'includes/header.php';
require_once '../config/config.php';

// Estatísticas rápidas
$total_projetos = $pdo->query("SELECT COUNT(*) FROM projetos WHERE ativo = TRUE")->fetchColumn();
$projetos_recentes = $pdo->query("SELECT COUNT(*) FROM projetos WHERE ativo = TRUE AND criado_em >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetchColumn();
$projetos_destaque = $pdo->query("SELECT COUNT(*) FROM projetos WHERE ativo = TRUE AND destaque = TRUE")->fetchColumn();
?>

<!-- Mensagens de feedback -->
<?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle"></i> <?= $_SESSION['success_message'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle"></i> <?= $_SESSION['error_message'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['error_message']); ?>
<?php endif; ?>

<div class="row mb-4">
    <div class="col-md-6 col-lg-3 mb-3">
        <div class="admin-card text-center">
            <div class="admin-card-body">
                <i class="bi bi-house-gear display-4 text-primary mb-3"></i>
                <h3 class="text-primary"><?= $total_projetos ?></h3>
                <p class="text-muted mb-0">Total de Projetos</p>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3 mb-3">
        <div class="admin-card text-center">
            <div class="admin-card-body">
                <i class="bi bi-graph-up display-4 text-success mb-3"></i>
                <h3 class="text-success"><?= $projetos_recentes ?></h3>
                <p class="text-muted mb-0">Últimos 30 dias</p>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3 mb-3">
        <div class="admin-card text-center">
            <div class="admin-card-body">
                <i class="bi bi-star-fill display-4 text-warning mb-3"></i>
                <h3 class="text-warning"><?= $projetos_destaque ?></h3>
                <p class="text-muted mb-0">Em Destaque</p>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3 mb-3">
        <div class="admin-card text-center">
            <div class="admin-card-body">
                <i class="bi bi-eye display-4 text-info mb-3"></i>
                <h3 class="text-info">100%</h3>
                <p class="text-muted mb-0">Visibilidade</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 mb-4">
        <div class="admin-card">
            <div class="admin-card-header">
                <h5 class="mb-0">
                    <i class="bi bi-speedometer2"></i> Ações Rápidas
                </h5>
            </div>
            <div class="admin-card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <a href="views/cadastrar_projeto.php" class="btn btn-primary w-100 py-3">
                            <i class="bi bi-plus-circle fs-4"></i><br>
                            <strong>Novo Projeto</strong><br>
                            <small>Cadastrar novo projeto</small>
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="views/listar_projetos.php" class="btn btn-outline-primary w-100 py-3">
                            <i class="bi bi-list fs-4"></i><br>
                            <strong>Listar Projetos</strong><br>
                            <small>Gerenciar projetos</small>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4 mb-4">
        <div class="admin-card">
            <div class="admin-card-header">
                <h5 class="mb-0">
                    <i class="bi bi-info-circle"></i> Sistema
                </h5>
            </div>
            <div class="admin-card-body">
                <p><strong>Usuário:</strong> <?= $_SESSION['admin_usuario'] ?></p>
                <p><strong>Último login:</strong> Agora</p>
                <p><strong>Status:</strong> <span class="badge bg-success">Online</span></p>
                <hr>
                <a href="../public/index.php" target="_blank" class="btn btn-outline-primary btn-sm w-100">
                    <i class="bi bi-eye"></i> Ver Site
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
