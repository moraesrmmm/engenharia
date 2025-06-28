<?php
require_once '../auth.php';
require_once '../../config/config.php';

// Paginação
$pagina_atual = intval($_GET['pagina'] ?? 1);
$itens_por_pagina = 20;
$offset = ($pagina_atual - 1) * $itens_por_pagina;

// Filtros
$filtro_status = $_GET['status'] ?? '';
$filtro_periodo = $_GET['periodo'] ?? '';

// Construir WHERE clause
$where_conditions = [];
$params = [];

if (!empty($filtro_status)) {
    $where_conditions[] = "v.status_pagamento = ?";
    $params[] = $filtro_status;
}

if (!empty($filtro_periodo)) {
    switch ($filtro_periodo) {
        case 'hoje':
            $where_conditions[] = "DATE(v.data_compra) = CURDATE()";
            break;
        case 'semana':
            $where_conditions[] = "v.data_compra >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            break;
        case 'mes':
            $where_conditions[] = "v.data_compra >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            break;
    }
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Buscar vendas
$sql = "
    SELECT v.*, p.titulo, p.capa_imagem
    FROM vendas v
    INNER JOIN projetos p ON v.projeto_id = p.id
    {$where_clause}
    ORDER BY v.data_compra DESC
    LIMIT {$itens_por_pagina} OFFSET {$offset}
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$vendas = $stmt->fetchAll();

// Contar total de vendas
$sql_count = "
    SELECT COUNT(*) as total
    FROM vendas v
    INNER JOIN projetos p ON v.projeto_id = p.id
    {$where_clause}
";

$stmt_count = $pdo->prepare($sql_count);
$stmt_count->execute($params);
$total_vendas = $stmt_count->fetch()['total'];
$total_paginas = ceil($total_vendas / $itens_por_pagina);

// Estatísticas
$stats = $pdo->query("
    SELECT 
        COUNT(*) as total_vendas,
        COUNT(CASE WHEN status_pagamento = 'approved' THEN 1 END) as vendas_aprovadas,
        COUNT(CASE WHEN status_pagamento = 'pending' THEN 1 END) as vendas_pendentes,
        COUNT(CASE WHEN status_pagamento = 'rejected' THEN 1 END) as vendas_rejeitadas,
        COALESCE(SUM(CASE WHEN status_pagamento = 'approved' THEN valor_pago END), 0) as faturamento_total,
        COUNT(CASE WHEN status_envio = 'enviado' THEN 1 END) as emails_enviados
    FROM vendas 
    WHERE data_compra >= DATE_SUB(NOW(), INTERVAL 30 DAY)
")->fetch();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Vendas - Admin</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?= asset('css/admin.css') ?>" rel="stylesheet">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <?php include '../includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="admin-main">
            <!-- Header -->
            <div class="admin-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2><i class="bi bi-cart-check"></i> Gerenciar Vendas</h2>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item active">Vendas</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>

            <!-- Estatísticas -->
            <div class="row g-3 mb-4">
                <div class="col-lg-2 col-md-4">
                    <div class="stat-card bg-primary">
                        <div class="stat-icon"><i class="bi bi-cart"></i></div>
                        <div class="stat-number"><?= $stats['total_vendas'] ?></div>
                        <div class="stat-label">Total Vendas</div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4">
                    <div class="stat-card bg-success">
                        <div class="stat-icon"><i class="bi bi-check-circle"></i></div>
                        <div class="stat-number"><?= $stats['vendas_aprovadas'] ?></div>
                        <div class="stat-label">Aprovadas</div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4">
                    <div class="stat-card bg-warning">
                        <div class="stat-icon"><i class="bi bi-clock"></i></div>
                        <div class="stat-number"><?= $stats['vendas_pendentes'] ?></div>
                        <div class="stat-label">Pendentes</div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4">
                    <div class="stat-card bg-danger">
                        <div class="stat-icon"><i class="bi bi-x-circle"></i></div>
                        <div class="stat-number"><?= $stats['vendas_rejeitadas'] ?></div>
                        <div class="stat-label">Rejeitadas</div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4">
                    <div class="stat-card bg-success">
                        <div class="stat-icon"><i class="bi bi-currency-dollar"></i></div>
                        <div class="stat-number">R$ <?= number_format($stats['faturamento_total'], 0, ',', '.') ?></div>
                        <div class="stat-label">Faturamento</div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4">
                    <div class="stat-card bg-info">
                        <div class="stat-icon"><i class="bi bi-envelope-check"></i></div>
                        <div class="stat-number"><?= $stats['emails_enviados'] ?></div>
                        <div class="stat-label">Emails Enviados</div>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Status do Pagamento</label>
                            <select name="status" class="form-select">
                                <option value="">Todos os status</option>
                                <option value="pending" <?= $filtro_status === 'pending' ? 'selected' : '' ?>>Pendente</option>
                                <option value="approved" <?= $filtro_status === 'approved' ? 'selected' : '' ?>>Aprovado</option>
                                <option value="rejected" <?= $filtro_status === 'rejected' ? 'selected' : '' ?>>Rejeitado</option>
                                <option value="cancelled" <?= $filtro_status === 'cancelled' ? 'selected' : '' ?>>Cancelado</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Período</label>
                            <select name="periodo" class="form-select">
                                <option value="">Todos os períodos</option>
                                <option value="hoje" <?= $filtro_periodo === 'hoje' ? 'selected' : '' ?>>Hoje</option>
                                <option value="semana" <?= $filtro_periodo === 'semana' ? 'selected' : '' ?>>Última semana</option>
                                <option value="mes" <?= $filtro_periodo === 'mes' ? 'selected' : '' ?>>Último mês</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bi bi-funnel"></i> Filtrar
                            </button>
                            <a href="vendas.php" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-clockwise"></i> Limpar
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Lista de Vendas -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Vendas Registradas (<?= $total_vendas ?>)</h5>
                </div>
                
                <div class="card-body p-0">
                    <?php if (empty($vendas)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-cart-x" style="font-size: 3rem; color: #ccc;"></i>
                            <h5 class="text-muted mt-3">Nenhuma venda encontrada</h5>
                            <p class="text-muted">Não há vendas registradas com os filtros selecionados.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Projeto</th>
                                        <th>Cliente</th>
                                        <th>Valor</th>
                                        <th>Status Pagamento</th>
                                        <th>Status Envio</th>
                                        <th>Data</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($vendas as $venda): ?>
                                        <tr>
                                            <td class="fw-bold">#<?= $venda['id'] ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="<?= asset('uploads/' . $venda['capa_imagem']) ?>" 
                                                         class="rounded me-2" width="40" height="30" style="object-fit: cover;">
                                                    <span><?= htmlspecialchars($venda['titulo']) ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <div class="fw-bold"><?= htmlspecialchars($venda['nome_cliente']) ?></div>
                                                    <small class="text-muted"><?= htmlspecialchars($venda['email_cliente']) ?></small>
                                                </div>
                                            </td>
                                            <td class="fw-bold">R$ <?= number_format($venda['valor_pago'], 2, ',', '.') ?></td>
                                            <td>
                                                <?php
                                                $badges = [
                                                    'pending' => 'bg-warning text-dark',
                                                    'approved' => 'bg-success',
                                                    'rejected' => 'bg-danger',
                                                    'cancelled' => 'bg-secondary'
                                                ];
                                                $textos = [
                                                    'pending' => 'Pendente',
                                                    'approved' => 'Aprovado',
                                                    'rejected' => 'Rejeitado',
                                                    'cancelled' => 'Cancelado'
                                                ];
                                                ?>
                                                <span class="badge <?= $badges[$venda['status_pagamento']] ?? 'bg-secondary' ?>">
                                                    <?= $textos[$venda['status_pagamento']] ?? $venda['status_pagamento'] ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php
                                                $badges_envio = [
                                                    'pendente' => 'bg-warning text-dark',
                                                    'enviado' => 'bg-success',
                                                    'erro' => 'bg-danger'
                                                ];
                                                ?>
                                                <span class="badge <?= $badges_envio[$venda['status_envio']] ?? 'bg-secondary' ?>">
                                                    <?= ucfirst($venda['status_envio']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div>
                                                    <div><?= date('d/m/Y', strtotime($venda['data_compra'])) ?></div>
                                                    <small class="text-muted"><?= date('H:i', strtotime($venda['data_compra'])) ?></small>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                                            onclick="verDetalhes(<?= $venda['id'] ?>)">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                    <?php if ($venda['status_pagamento'] === 'approved' && $venda['status_envio'] !== 'enviado'): ?>
                                                        <button type="button" class="btn btn-sm btn-outline-success" 
                                                                onclick="reenviarEmail(<?= $venda['id'] ?>)">
                                                            <i class="bi bi-envelope"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Paginação -->
                <?php if ($total_paginas > 1): ?>
                    <div class="card-footer">
                        <nav aria-label="Paginação de vendas">
                            <ul class="pagination justify-content-center mb-0">
                                <?php if ($pagina_atual > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?pagina=<?= $pagina_atual - 1 ?>&status=<?= $filtro_status ?>&periodo=<?= $filtro_periodo ?>">
                                            <i class="bi bi-chevron-left"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $pagina_atual - 2); $i <= min($total_paginas, $pagina_atual + 2); $i++): ?>
                                    <li class="page-item <?= $i == $pagina_atual ? 'active' : '' ?>">
                                        <a class="page-link" href="?pagina=<?= $i ?>&status=<?= $filtro_status ?>&periodo=<?= $filtro_periodo ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($pagina_atual < $total_paginas): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?pagina=<?= $pagina_atual + 1 ?>&status=<?= $filtro_status ?>&periodo=<?= $filtro_periodo ?>">
                                            <i class="bi bi-chevron-right"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function verDetalhes(vendaId) {
            // Implementar modal com detalhes da venda
            alert('Funcionalidade em desenvolvimento: Ver detalhes da venda #' + vendaId);
        }
        
        function reenviarEmail(vendaId) {
            if (confirm('Reenviar email com arquivo para o cliente?')) {
                fetch('../api/reenviar_email.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({venda_id: vendaId})
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Email reenviado com sucesso!');
                        location.reload();
                    } else {
                        alert('Erro: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao reenviar email.');
                });
            }
        }
    </script>
</body>
</html>
