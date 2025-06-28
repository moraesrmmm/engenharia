<?php
require_once './../config/config.php';
require_once './../includes/funcoes.php';

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
    if ($filtros['quartos'] == '4') {
        $where_conditions[] = "p.id IN (
            SELECT DISTINCT c.projeto_id 
            FROM comodos c 
            WHERE c.tipo IN ('Quarto', 'Suíte') 
            GROUP BY c.projeto_id 
            HAVING COUNT(*) >= 4
        )";
    } else {
        $where_conditions[] = "p.id IN (
            SELECT DISTINCT c.projeto_id 
            FROM comodos c 
            WHERE c.tipo IN ('Quarto', 'Suíte') 
            GROUP BY c.projeto_id 
            HAVING COUNT(*) = ?
        )";
        $params[] = (int)$filtros['quartos'];
    }
}

if (!empty($filtros['preco_min'])) {
    $where_conditions[] = "p.valor_projeto >= ?";
    $params[] = (float)$filtros['preco_min'];
}

if (!empty($filtros['preco_max'])) {
    $where_conditions[] = "p.valor_projeto <= ?";
    $params[] = (float)$filtros['preco_max'];
}

if (!empty($filtros['area_min'])) {
    $where_conditions[] = "p.area_terreno >= ?";
    $params[] = (float)$filtros['area_min'];
}

if (!empty($filtros['area_max'])) {
    $where_conditions[] = "p.area_terreno <= ?";
    $params[] = (float)$filtros['area_max'];
}

if ($filtros['destaque'] === '1') {
    $where_conditions[] = "p.destaque = TRUE";
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
        COALESCE(AVG(valor_projeto), 0) as preco_medio,
        COALESCE(AVG(area_terreno), 0) as area_media
    FROM projetos 
    WHERE ativo = TRUE
");
$stats = $stats_stmt->fetch();

// Função para gerar URL com filtros
function gerarUrlFiltro($filtro, $valor) {
    global $filtros;
    $params = $filtros;
    $params[$filtro] = $valor;
    return 'projetos.php?' . http_build_query(array_filter($params));
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nossos Projetos - Engenharia & Arquitetura</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?= asset('css/style.css') ?>" rel="stylesheet">
</head>
<body>
    <!-- Hero Section -->
    <section class="projetos-hero">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10 text-center">
                    <h1 class="projetos-title">Nossos Projetos</h1>
                    <p class="projetos-subtitle">
                        Explore nossa coleção de <?= $stats['total_projetos'] ?> projetos únicos e encontre a casa dos seus sonhos
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Filtros -->
    <div class="container">
        <div class="public-filters">
            <!-- Busca -->
            <form method="GET" id="filtrosForm">
                <div class="public-search-container">
                    <input type="text" 
                           name="search" 
                           class="public-search-input" 
                           placeholder="Buscar projetos por nome ou descrição..."
                           value="<?= htmlspecialchars($filtros['search']) ?>">
                    <i class="bi bi-search public-search-icon"></i>
                </div>

                <!-- Filtros por Quartos -->
                <div class="text-center mb-4">
                    <h5 class="mb-3">
                        <i class="bi bi-door-open"></i> Filtrar por Número de Quartos
                    </h5>
                    <div class="d-flex flex-wrap justify-content-center">
                        <a href="<?= gerarUrlFiltro('quartos', '') ?>" 
                           class="public-filter-chip <?= empty($filtros['quartos']) ? 'active' : '' ?>">
                            <i class="bi bi-house"></i> Todos
                        </a>
                        <a href="<?= gerarUrlFiltro('quartos', '1') ?>" 
                           class="public-filter-chip <?= $filtros['quartos'] === '1' ? 'active' : '' ?>">
                            <i class="bi bi-1-circle"></i> 1 Quarto
                        </a>
                        <a href="<?= gerarUrlFiltro('quartos', '2') ?>" 
                           class="public-filter-chip <?= $filtros['quartos'] === '2' ? 'active' : '' ?>">
                            <i class="bi bi-2-circle"></i> 2 Quartos
                        </a>
                        <a href="<?= gerarUrlFiltro('quartos', '3') ?>" 
                           class="public-filter-chip <?= $filtros['quartos'] === '3' ? 'active' : '' ?>">
                            <i class="bi bi-3-circle"></i> 3 Quartos
                        </a>
                        <a href="<?= gerarUrlFiltro('quartos', '4') ?>" 
                           class="public-filter-chip <?= $filtros['quartos'] === '4' ? 'active' : '' ?>">
                            <i class="bi bi-4-circle"></i> 4+ Quartos
                        </a>
                    </div>
                </div>

                <!-- Filtros Avançados -->
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label fw-bold">
                            <i class="bi bi-currency-dollar"></i> Preço Mínimo
                        </label>
                        <input type="number" class="form-control" name="preco_min" 
                               placeholder="Ex: 100000" value="<?= htmlspecialchars($filtros['preco_min']) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">
                            <i class="bi bi-currency-dollar"></i> Preço Máximo
                        </label>
                        <input type="number" class="form-control" name="preco_max" 
                               placeholder="Ex: 500000" value="<?= htmlspecialchars($filtros['preco_max']) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">
                            <i class="bi bi-bounding-box"></i> Área Mín (m²)
                        </label>
                        <input type="number" class="form-control" name="area_min" 
                               placeholder="Ex: 50" value="<?= htmlspecialchars($filtros['area_min']) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">
                            <i class="bi bi-bounding-box"></i> Área Máx (m²)
                        </label>
                        <input type="number" class="form-control" name="area_max" 
                               placeholder="Ex: 300" value="<?= htmlspecialchars($filtros['area_max']) ?>">
                    </div>
                </div>

                <!-- Filtro por Destaque -->
                <div class="text-center mt-4">
                    <div class="d-flex flex-wrap justify-content-center">
                        <a href="<?= gerarUrlFiltro('destaque', '') ?>" 
                           class="public-filter-chip <?= empty($filtros['destaque']) ? 'active' : '' ?>">
                            <i class="bi bi-list"></i> Todos os Projetos
                        </a>
                        <a href="<?= gerarUrlFiltro('destaque', '1') ?>" 
                           class="public-filter-chip <?= $filtros['destaque'] === '1' ? 'active' : '' ?>">
                            <i class="bi bi-star-fill"></i> Projetos em Destaque
                        </a>
                    </div>
                </div>

                <!-- Botões -->
                <div class="d-flex gap-3 justify-content-center mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-funnel"></i> Aplicar Filtros
                    </button>
                    <a href="projetos.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-clockwise"></i> Limpar Filtros
                    </a>
                </div>
            </form>
        </div>

        <!-- Estatísticas -->
        <div class="public-stats-grid">
            <div class="public-stat-item">
                <div class="public-stat-number"><?= $stats['total_projetos'] ?></div>
                <div class="public-stat-label">Projetos Disponíveis</div>
            </div>
            <div class="public-stat-item">
                <div class="public-stat-number"><?= $stats['total_destaques'] ?></div>
                <div class="public-stat-label">Projetos em Destaque</div>
            </div>
            <div class="public-stat-item">
                <div class="public-stat-number">R$ <?= number_format($stats['preco_medio'], 0, ',', '.') ?></div>
                <div class="public-stat-label">Investimento Médio</div>
            </div>
            <div class="public-stat-item">
                <div class="public-stat-number"><?= number_format($stats['area_media'], 0, ',', '.') ?>m²</div>
                <div class="public-stat-label">Área Média</div>
            </div>
        </div>

        <!-- Resultados -->
        <div class="results-header">
            <h3 class="results-title">
                <i class="bi bi-grid"></i> Projetos Encontrados
            </h3>
            <span class="results-count ms-3"><?= count($projetos) ?> resultado(s)</span>
        </div>

        <?php if (empty($projetos)): ?>
            <div class="public-empty-state">
                <div class="public-empty-icon">
                    <i class="bi bi-house-x"></i>
                </div>
                <h3 class="text-muted mb-3">Nenhum projeto encontrado</h3>
                <p class="text-muted mb-4">
                    <?php if (array_filter($filtros)): ?>
                        Tente ajustar os filtros de busca ou explore todos os nossos projetos.
                    <?php else: ?>
                        Ainda não temos projetos cadastrados. Volte em breve!
                    <?php endif; ?>
                </p>
                <?php if (array_filter($filtros)): ?>
                    <a href="projetos.php" class="btn btn-primary btn-lg">
                        <i class="bi bi-arrow-clockwise"></i> Ver Todos os Projetos
                    </a>
                <?php else: ?>
                    <a href="<?= url('index.php') ?>" class="btn btn-primary btn-lg">
                        <i class="bi bi-house"></i> Voltar ao Início
                    </a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="row g-4 mb-5">
                <?php foreach ($projetos as $projeto): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="public-project-card <?= $projeto['destaque'] ? 'destaque' : '' ?>">
                            <!-- Badge de Destaque -->
                            <?php if ($projeto['destaque']): ?>
                                <div class="public-project-badge">
                                    <i class="bi bi-star-fill"></i> Destaque
                                </div>
                            <?php endif; ?>

                            <!-- Imagem -->
                            <div style="overflow: hidden;">
                                <img src="<?= asset('uploads/' . htmlspecialchars($projeto['capa_imagem'])) ?>" 
                                     class="public-project-image" 
                                     alt="<?= htmlspecialchars($projeto['titulo']) ?>">
                            </div>

                            <!-- Informações -->
                            <div class="public-project-info">
                                <h4 class="public-project-title"><?= htmlspecialchars($projeto['titulo']) ?></h4>
                                
                                <p class="public-project-description">
                                    <?= htmlspecialchars($projeto['descricao']) ?>
                                </p>

                                <div class="public-project-meta">
                                    <div class="public-meta-item">
                                        <i class="bi bi-rulers public-meta-icon"></i>
                                        <?= $projeto['largura_comprimento'] ?>
                                    </div>
                                    <div class="public-meta-item">
                                        <i class="bi bi-bounding-box public-meta-icon"></i>
                                        <?= formatarArea($projeto['area_terreno']) ?>
                                    </div>
                                    <div class="public-meta-item">
                                        <i class="bi bi-door-open public-meta-icon"></i>
                                        <?= $projeto['quartos'] ?> quarto(s)
                                    </div>
                                    <div class="public-meta-item">
                                        <i class="bi bi-house-door public-meta-icon"></i>
                                        <?= $projeto['total_comodos'] ?> cômodos
                                    </div>
                                </div>

                                <?php if ($projeto['valor_projeto']): ?>
                                    <div class="public-project-price">
                                        <i class="bi bi-currency-dollar"></i>
                                        <?= formatarMoeda($projeto['valor_projeto']) ?>
                                    </div>
                                <?php endif; ?>

                                <a href="<?= url('detalhes_projeto.php?id=' . $projeto['id']) ?>" class="public-project-button">
                                    <i class="bi bi-eye"></i> Ver Detalhes Completos
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Botão Voltar -->
        <div class="text-center">
            <a href="<?= url('index.php') ?>" class="back-to-home">
                <i class="bi bi-arrow-left"></i>
                Voltar ao Início
            </a>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Debounce para o campo de busca
        let searchTimeout;
        const searchInput = document.querySelector('input[name="search"]');
        
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    document.getElementById('filtrosForm').submit();
                }, 800);
            });
        }

        // Auto-submit para campos numéricos
        const numericInputs = document.querySelectorAll('input[type="number"]');
        numericInputs.forEach(input => {
            input.addEventListener('change', function() {
                setTimeout(() => {
                    document.getElementById('filtrosForm').submit();
                }, 300);
            });
        });

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

        document.querySelectorAll('.public-project-card, .public-stat-item').forEach(el => {
            observer.observe(el);
        });

        // Efeito parallax suave no hero
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            const hero = document.querySelector('.projetos-hero');
            if (hero) {
                hero.style.transform = `translateY(${scrolled * 0.3}px)`;
            }
        });
    </script>
</body>
</html>
