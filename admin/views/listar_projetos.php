<?php 
require_once '../includes/header.php';
require_once '../../config/config.php';

// Parâmetros de filtro
$filtros = [
    'search' => $_GET['search'] ?? '',
    'quartos' => $_GET['quartos'] ?? '',
    'preco_min' => $_GET['preco_min'] ?? '',
    'preco_max' => $_GET['preco_max'] ?? '',
    'area_min' => $_GET['area_min'] ?? '',
    'area_max' => $_GET['area_max'] ?? '',
    'destaque' => $_GET['destaque'] ?? ''
];

// Construir query dinâmica
$where_conditions = ["p.ativo = TRUE"];
$params = [];

if (!empty($filtros['search'])) {
    $where_conditions[] = "(p.titulo LIKE ? OR p.descricao LIKE ?)";
    $search_term = '%' . $filtros['search'] . '%';
    $params[] = $search_term;
    $params[] = $search_term;
}

if (!empty($filtros['quartos'])) {
    $where_conditions[] = "p.id IN (
        SELECT DISTINCT c.projeto_id 
        FROM comodos c 
        WHERE c.tipo IN ('Quarto', 'Suíte') 
        GROUP BY c.projeto_id 
        HAVING COUNT(*) = ?
    )";
    $params[] = (int)$filtros['quartos'];
}

if (!empty($filtros['preco_min'])) {
    $where_conditions[] = "p.preco_total >= ?";
    $params[] = (float)$filtros['preco_min'];
}

if (!empty($filtros['preco_max'])) {
    $where_conditions[] = "p.preco_total <= ?";
    $params[] = (float)$filtros['preco_max'];
}

if (!empty($filtros['area_min'])) {
    $where_conditions[] = "p.area >= ?";
    $params[] = (float)$filtros['area_min'];
}

if (!empty($filtros['area_max'])) {
    $where_conditions[] = "p.area <= ?";
    $params[] = (float)$filtros['area_max'];
}

if ($filtros['destaque'] === '1') {
    $where_conditions[] = "p.destaque = TRUE";
} elseif ($filtros['destaque'] === '0') {
    $where_conditions[] = "p.destaque = FALSE";
}

$where_clause = implode(' AND ', $where_conditions);

// Buscar projetos com filtros
$sql = "
    SELECT p.*, 
           COUNT(CASE WHEN c.tipo IN ('Quarto', 'Suíte') THEN 1 END) as quartos,
           COUNT(c.id) as total_comodos 
    FROM projetos p 
    LEFT JOIN comodos c ON p.id = c.projeto_id 
    WHERE {$where_clause}
    GROUP BY p.id 
    ORDER BY p.destaque DESC, p.criado_em DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$projetos = $stmt->fetchAll();

// Estatísticas gerais
$stats_stmt = $pdo->query("
    SELECT 
        COUNT(*) as total_projetos,
        COUNT(CASE WHEN destaque = TRUE THEN 1 END) as total_destaques,
        COALESCE(AVG(preco_total), 0) as preco_medio,
        COALESCE(AVG(area), 0) as area_media
    FROM projetos 
    WHERE ativo = TRUE
");
$stats = $stats_stmt->fetch();
?>

<div class="admin-card">
    <div class="projects-header text-center">
        <h2 class="mb-3">
            <i class="bi bi-collection"></i> Gerenciar Projetos
        </h2>
        <p class="mb-0 opacity-75">Organize, filtre e gerencie todos os seus projetos de engenharia</p>
    </div>
    
    <div class="admin-card-body">
        <!-- Estatísticas -->
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-number"><?= $stats['total_projetos'] ?></div>
                <div class="stat-label">Total de Projetos</div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?= $stats['total_destaques'] ?></div>
                <div class="stat-label">Em Destaque</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">R$ <?= number_format($stats['preco_medio'], 0, ',', '.') ?></div>
                <div class="stat-label">Preço Médio</div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?= number_format($stats['area_media'], 0, ',', '.') ?>m²</div>
                <div class="stat-label">Área Média</div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="filters-container">
            <form method="GET" id="filtrosForm">
                <!-- Busca -->
                <div class="search-container">
                    <input type="text" 
                           name="search" 
                           class="search-input" 
                           placeholder="Buscar por título ou descrição..."
                           value="<?= htmlspecialchars($filtros['search']) ?>">
                    <i class="bi bi-search search-icon"></i>
                </div>

                <!-- Filtros por Quartos -->
                <div class="mb-4">
                    <label class="form-label fw-bold mb-3">
                        <i class="bi bi-door-open"></i> Número de Quartos
                    </label>
                    <div class="d-flex flex-wrap gap-2">
                        <input type="radio" class="btn-check" name="quartos" value="" id="quartos_todos" 
                               <?= empty($filtros['quartos']) ? 'checked' : '' ?>>
                        <label class="filter-chip" for="quartos_todos">
                            <i class="bi bi-house"></i> Todos
                        </label>

                        <input type="radio" class="btn-check" name="quartos" value="1" id="quartos_1" 
                               <?= $filtros['quartos'] === '1' ? 'checked' : '' ?>>
                        <label class="filter-chip" for="quartos_1">
                            <i class="bi bi-1-circle"></i> 1 Quarto
                        </label>

                        <input type="radio" class="btn-check" name="quartos" value="2" id="quartos_2" 
                               <?= $filtros['quartos'] === '2' ? 'checked' : '' ?>>
                        <label class="filter-chip" for="quartos_2">
                            <i class="bi bi-2-circle"></i> 2 Quartos
                        </label>

                        <input type="radio" class="btn-check" name="quartos" value="3" id="quartos_3" 
                               <?= $filtros['quartos'] === '3' ? 'checked' : '' ?>>
                        <label class="filter-chip" for="quartos_3">
                            <i class="bi bi-3-circle"></i> 3 Quartos
                        </label>

                        <input type="radio" class="btn-check" name="quartos" value="4" id="quartos_4" 
                               <?= $filtros['quartos'] === '4' ? 'checked' : '' ?>>
                        <label class="filter-chip" for="quartos_4">
                            <i class="bi bi-4-circle"></i> 4+ Quartos
                        </label>
                    </div>
                </div>

                <!-- Filtros por Preço -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">
                            <i class="bi bi-currency-dollar"></i> Preço Mínimo
                        </label>
                        <input type="number" class="form-control" name="preco_min" 
                               placeholder="Ex: 100000" value="<?= htmlspecialchars($filtros['preco_min']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">
                            <i class="bi bi-currency-dollar"></i> Preço Máximo
                        </label>
                        <input type="number" class="form-control" name="preco_max" 
                               placeholder="Ex: 500000" value="<?= htmlspecialchars($filtros['preco_max']) ?>">
                    </div>
                </div>

                <!-- Filtros por Área -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">
                            <i class="bi bi-bounding-box"></i> Área Mínima (m²)
                        </label>
                        <input type="number" class="form-control" name="area_min" 
                               placeholder="Ex: 50" value="<?= htmlspecialchars($filtros['area_min']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">
                            <i class="bi bi-bounding-box"></i> Área Máxima (m²)
                        </label>
                        <input type="number" class="form-control" name="area_max" 
                               placeholder="Ex: 300" value="<?= htmlspecialchars($filtros['area_max']) ?>">
                    </div>
                </div>

                <!-- Filtro por Destaque -->
                <div class="mb-4">
                    <label class="form-label fw-bold mb-3">
                        <i class="bi bi-star"></i> Status de Destaque
                    </label>
                    <div class="d-flex flex-wrap gap-2">
                        <input type="radio" class="btn-check" name="destaque" value="" id="destaque_todos" 
                               <?= empty($filtros['destaque']) ? 'checked' : '' ?>>
                        <label class="filter-chip" for="destaque_todos">
                            <i class="bi bi-list"></i> Todos
                        </label>

                        <input type="radio" class="btn-check" name="destaque" value="1" id="destaque_sim" 
                               <?= $filtros['destaque'] === '1' ? 'checked' : '' ?>>
                        <label class="filter-chip" for="destaque_sim">
                            <i class="bi bi-star-fill"></i> Em Destaque
                        </label>

                        <input type="radio" class="btn-check" name="destaque" value="0" id="destaque_nao" 
                               <?= $filtros['destaque'] === '0' ? 'checked' : '' ?>>
                        <label class="filter-chip" for="destaque_nao">
                            <i class="bi bi-star"></i> Sem Destaque
                        </label>
                    </div>
                </div>

                <!-- Botões -->
                <div class="d-flex gap-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-funnel"></i> Aplicar Filtros
                    </button>
                    <a href="listar_projetos.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-clockwise"></i> Limpar
                    </a>
                    <a href="cadastrar_projeto.php" class="btn btn-success ms-auto">
                        <i class="bi bi-plus-circle"></i> Novo Projeto
                    </a>
                </div>
            </form>
        </div>

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

        <!-- Resultados -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0">
                <i class="bi bi-grid"></i> Resultados da Busca
                <span class="badge bg-primary ms-2"><?= count($projetos) ?> projeto(s)</span>
            </h5>
        </div>

        <?php if (empty($projetos)): ?>
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="bi bi-inbox"></i>
                </div>
                <h4 class="text-muted mb-3">Nenhum projeto encontrado</h4>
                <p class="text-muted mb-4">
                    <?php if (array_filter($filtros)): ?>
                        Tente ajustar os filtros ou limpar a busca.
                    <?php else: ?>
                        Que tal cadastrar o primeiro projeto?
                    <?php endif; ?>
                </p>
                <?php if (array_filter($filtros)): ?>
                    <a href="listar_projetos.php" class="btn btn-outline-primary me-3">
                        <i class="bi bi-arrow-clockwise"></i> Limpar Filtros
                    </a>
                <?php endif; ?>
                <a href="cadastrar_projeto.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Cadastrar Projeto
                </a>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($projetos as $projeto): ?>
                    <div class="col-xl-4 col-lg-6">
                        <div class="project-card <?= $projeto['destaque'] ? 'destaque' : '' ?>">
                            <!-- Toggle Destaque -->
                            <button onclick="toggleDestaque(<?= $projeto['id'] ?>, <?= $projeto['destaque'] ? 'false' : 'true' ?>)" 
                                    class="toggle-star <?= $projeto['destaque'] ? 'active' : '' ?>" 
                                    title="<?= $projeto['destaque'] ? 'Remover destaque' : 'Adicionar destaque' ?>">
                                <i class="bi bi-star<?= $projeto['destaque'] ? '-fill' : '' ?>"></i>
                            </button>

                            <!-- Badge de Destaque -->
                            <?php if ($projeto['destaque']): ?>
                                <div class="project-badge">
                                    <i class="bi bi-star-fill"></i> Destaque
                                </div>
                            <?php endif; ?>

                            <!-- Imagem -->
                            <div style="overflow: hidden; border-radius: 20px 20px 0 0;">
                                <img src="../../public/uploads/<?= htmlspecialchars($projeto['capa_imagem']) ?>" 
                                     class="project-image" 
                                     alt="<?= htmlspecialchars($projeto['titulo']) ?>">
                            </div>

                            <!-- Informações -->
                            <div class="project-info">
                                <h5 class="project-title"><?= htmlspecialchars($projeto['titulo']) ?></h5>
                                
                                <p class="project-description">
                                    <?= htmlspecialchars($projeto['descricao']) ?>
                                </p>

                                <div class="project-meta">
                                    <div class="meta-item">
                                        <i class="bi bi-rulers meta-icon"></i>
                                        <?= $projeto['largura_comprimento'] ?>
                                    </div>
                                    <div class="meta-item">
                                        <i class="bi bi-bounding-box meta-icon"></i>
                                        <?= number_format($projeto['area'], 2, ',', '.') ?>m²
                                    </div>
                                    <div class="meta-item">
                                        <i class="bi bi-door-open meta-icon"></i>
                                        <?= $projeto['quartos'] ?> quarto(s)
                                    </div>
                                    <div class="meta-item">
                                        <i class="bi bi-calendar meta-icon"></i>
                                        <?= date('d/m/Y', strtotime($projeto['criado_em'])) ?>
                                    </div>
                                </div>

                                <?php if ($projeto['preco_total']): ?>
                                    <div class="project-price">
                                        <i class="bi bi-currency-dollar"></i>
                                        R$ <?= number_format($projeto['preco_total'], 2, ',', '.') ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Ações -->
                            <div class="project-actions">
                                <a href="../../public/detalhes_projeto.php?id=<?= $projeto['id'] ?>" 
                                   target="_blank" class="btn btn-outline-primary">
                                    <i class="bi bi-eye"></i> Ver
                                </a>
                                <a href="editar_projeto.php?id=<?= $projeto['id'] ?>" 
                                   class="btn btn-outline-warning">
                                    <i class="bi bi-pencil"></i> Editar
                                </a>
                                <button onclick="confirmarExclusao(<?= $projeto['id'] ?>)" 
                                        class="btn btn-outline-danger">
                                    <i class="bi bi-trash"></i> Excluir
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Auto-submit dos filtros
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('filtrosForm');
    const inputs = form.querySelectorAll('input[type="radio"], input[name="search"]');
    
    inputs.forEach(input => {
        if (input.type === 'radio') {
            input.addEventListener('change', function() {
                if (this.checked) {
                    setTimeout(() => form.submit(), 100);
                }
            });
        }
    });
    
    // Debounce para o campo de busca
    let searchTimeout;
    const searchInput = form.querySelector('input[name="search"]');
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            form.submit();
        }, 500);
    });
});

function confirmarExclusao(id) {
    if (confirm('Tem certeza que deseja excluir este projeto? Esta ação não pode ser desfeita.')) {
        window.location.href = '../backend/excluir_projeto.php?id=' + id;
    }
}

function toggleDestaque(id, destaque) {
    // Criar formulário dinâmico para enviar via POST
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '../backend/toggle_destaque.php';
    
    const inputId = document.createElement('input');
    inputId.type = 'hidden';
    inputId.name = 'projeto_id';
    inputId.value = id;
    
    const inputDestaque = document.createElement('input');
    inputDestaque.type = 'hidden';
    inputDestaque.name = 'destaque';
    inputDestaque.value = destaque ? '1' : '0';
    
    form.appendChild(inputId);
    form.appendChild(inputDestaque);
    document.body.appendChild(form);
    form.submit();
}

// Animações ao scroll
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.animationPlayState = 'running';
        }
    });
}, observerOptions);

document.querySelectorAll('.project-card').forEach(card => {
    observer.observe(card);
});
</script>

<?php require_once '../includes/footer.php'; ?>
