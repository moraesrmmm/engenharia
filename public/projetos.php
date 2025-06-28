<?php
require_once '../config/config.php';
require_once '../includes/funcoes.php';
require_once '../includes/header.php';

// Parâmetros de filtro com validações
$filtros = [
    'search' => isset($_GET['search']) ? trim($_GET['search']) : '',
    'quartos' => isset($_GET['quartos']) && is_numeric($_GET['quartos']) ? $_GET['quartos'] : '',
    'tipo_projeto' => isset($_GET['tipo_projeto']) ? trim($_GET['tipo_projeto']) : '',
    'preco_min' => isset($_GET['preco_min']) && is_numeric($_GET['preco_min']) ? (float)$_GET['preco_min'] : '',
    'preco_max' => isset($_GET['preco_max']) && is_numeric($_GET['preco_max']) ? (float)$_GET['preco_max'] : '',
    'largura_terreno' => isset($_GET['largura_terreno']) && is_numeric($_GET['largura_terreno']) ? (float)$_GET['largura_terreno'] : '',
    'destaque' => isset($_GET['destaque']) && in_array($_GET['destaque'], ['0', '1']) ? $_GET['destaque'] : ''
];

// Buscar larguras de terreno disponíveis com validação
try {
    $larguras_stmt = $pdo->query("
        SELECT DISTINCT largura_terreno 
        FROM projetos 
        WHERE ativo = TRUE AND largura_terreno > 0 AND largura_terreno IS NOT NULL
        ORDER BY largura_terreno ASC
    ");
    $larguras_disponiveis = $larguras_stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
} catch (PDOException $e) {
    error_log("Erro ao buscar larguras: " . $e->getMessage());
    $larguras_disponiveis = [];
}

// Buscar tipos de projeto disponíveis com validação
try {
    $tipos_stmt = $pdo->query("
        SELECT DISTINCT tipo_projeto 
        FROM projetos 
        WHERE ativo = TRUE AND tipo_projeto IS NOT NULL AND tipo_projeto != ''
        ORDER BY tipo_projeto ASC
    ");
    $tipos_disponiveis = $tipos_stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
} catch (PDOException $e) {
    error_log("Erro ao buscar tipos: " . $e->getMessage());
    $tipos_disponiveis = [];
}

// Construir query dinâmica
$where_conditions = ["p.ativo = TRUE"];
$params = [];

if (!empty($filtros['search'])) {
    $where_conditions[] = "(p.titulo LIKE ? OR p.descricao LIKE ?)";
    $search_term = '%' . $filtros['search'] . '%';
    $params[] = $search_term;
    $params[] = $search_term;
}

if (!empty($filtros['quartos']) && is_numeric($filtros['quartos'])) {
    $num_quartos = (int)$filtros['quartos'];
    
    if ($num_quartos >= 4) {
        $where_conditions[] = "(
            COALESCE((
                SELECT COUNT(*) 
                FROM comodos c 
                WHERE c.projeto_id = p.id AND c.tipo IN ('Quarto', 'Suíte')
            ), 0) +
            COALESCE((
                SELECT COUNT(*) 
                FROM comodos c 
                INNER JOIN andares a ON c.andar_id = a.id 
                WHERE a.projeto_id = p.id AND c.tipo IN ('Quarto', 'Suíte')
            ), 0)
        ) >= 4";
    } else {
        $where_conditions[] = "(
            COALESCE((
                SELECT COUNT(*) 
                FROM comodos c 
                WHERE c.projeto_id = p.id AND c.tipo IN ('Quarto', 'Suíte')
            ), 0) +
            COALESCE((
                SELECT COUNT(*) 
                FROM comodos c 
                INNER JOIN andares a ON c.andar_id = a.id 
                WHERE a.projeto_id = p.id AND c.tipo IN ('Quarto', 'Suíte')
            ), 0)
        ) = ?";
        $params[] = $num_quartos;
    }
}

if (!empty($filtros['preco_min']) && $filtros['preco_min'] > 0) {
    $where_conditions[] = "p.valor_projeto >= ?";
    $params[] = $filtros['preco_min'];
}

if (!empty($filtros['preco_max']) && $filtros['preco_max'] > 0) {
    $where_conditions[] = "p.valor_projeto <= ?";
    $params[] = $filtros['preco_max'];
}

if (!empty($filtros['tipo_projeto'])) {
    $where_conditions[] = "p.tipo_projeto = ?";
    $params[] = $filtros['tipo_projeto'];
}

if (!empty($filtros['largura_terreno']) && $filtros['largura_terreno'] > 0) {
    $where_conditions[] = "p.largura_terreno = ?";
    $params[] = $filtros['largura_terreno'];
}

if ($filtros['destaque'] === '1') {
    $where_conditions[] = "p.destaque = TRUE";
}

$where_clause = implode(' AND ', $where_conditions);

// Buscar projetos com filtros (com validações para evitar erros)
$sql = "
    SELECT 
        p.*,

        COALESCE((
            SELECT COUNT(*) 
            FROM comodos c 
            WHERE c.projeto_id = p.id AND c.tipo IN ('Quarto', 'Suíte')
        ), 0) +
        COALESCE((
            SELECT COUNT(*) 
            FROM comodos c
            INNER JOIN andares a ON c.andar_id = a.id 
            WHERE a.projeto_id = p.id AND c.tipo IN ('Quarto', 'Suíte')
        ), 0) AS quartos,

        COALESCE((
            SELECT COUNT(*) 
            FROM comodos c 
            WHERE c.projeto_id = p.id
        ), 0) +
        COALESCE((
            SELECT COUNT(*) 
            FROM comodos c
            INNER JOIN andares a ON c.andar_id = a.id 
            WHERE a.projeto_id = p.id
        ), 0) AS total_comodos

    FROM projetos p 
    WHERE {$where_clause}
    ORDER BY p.destaque DESC, p.criado_em DESC
";

// Executar query com tratamento de erro
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $projetos = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Erro ao buscar projetos: " . $e->getMessage());
    error_log("SQL: " . $sql);
    error_log("Params: " . print_r($params, true));
    $projetos = [];
}

// Estatísticas gerais com tratamento de erro
try {
    $stats_stmt = $pdo->query("
        SELECT 
            COUNT(*) as total_projetos,
            COUNT(CASE WHEN destaque = TRUE THEN 1 END) as total_destaques,
            COALESCE(AVG(NULLIF(valor_projeto, 0)), 0) as preco_medio,
            COALESCE(AVG(NULLIF(area_terreno, 0)), 0) as area_media
        FROM projetos 
        WHERE ativo = TRUE
    ");
    $stats = $stats_stmt->fetch();
    
    // Garantir que os valores sejam válidos
    $stats = [
        'total_projetos' => (int)($stats['total_projetos'] ?? 0),
        'total_destaques' => (int)($stats['total_destaques'] ?? 0),
        'preco_medio' => (float)($stats['preco_medio'] ?? 0),
        'area_media' => (float)($stats['area_media'] ?? 0)
    ];
} catch (PDOException $e) {
    error_log("Erro ao buscar estatísticas: " . $e->getMessage());
    $stats = [
        'total_projetos' => 0,
        'total_destaques' => 0,
        'preco_medio' => 0,
        'area_media' => 0
    ];
}

// Verificar se existem projetos com quartos para mostrar o filtro
try {
    $quartos_existem_stmt = $pdo->query("
        SELECT COUNT(*) as tem_quartos
        FROM projetos p
        WHERE p.ativo = TRUE 
        AND (
            EXISTS(SELECT 1 FROM comodos c WHERE c.projeto_id = p.id AND c.tipo IN ('Quarto', 'Suíte'))
            OR EXISTS(
                SELECT 1 FROM comodos c 
                INNER JOIN andares a ON c.andar_id = a.id 
                WHERE a.projeto_id = p.id AND c.tipo IN ('Quarto', 'Suíte')
            )
        )
    ");
    $tem_quartos = $quartos_existem_stmt->fetch()['tem_quartos'] > 0;
} catch (PDOException $e) {
    error_log("Erro ao verificar quartos: " . $e->getMessage());
    $tem_quartos = false;
}

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

                <!-- Filtros por Quartos (apenas se existirem quartos) -->
                <?php if ($tem_quartos): ?>
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
                <?php endif; ?>

                <!-- Filtros Avançados -->
                <div class="row g-3">
                    <div class="col-lg-2 col-md-6">
                        <label class="form-label fw-bold">
                            <i class="bi bi-currency-dollar"></i> Preço Mínimo
                        </label>
                        <input type="number" class="form-control" name="preco_min" 
                               placeholder="Ex: 100000" value="<?= htmlspecialchars($filtros['preco_min']) ?>">
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label class="form-label fw-bold">
                            <i class="bi bi-currency-dollar"></i> Preço Máximo
                        </label>
                        <input type="number" class="form-control" name="preco_max" 
                               placeholder="Ex: 500000" value="<?= htmlspecialchars($filtros['preco_max']) ?>">
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label fw-bold">
                            <i class="bi bi-house-gear"></i> Tipo de Projeto
                        </label>
                        <select class="form-select" name="tipo_projeto">
                            <option value="">Todos os tipos</option>
                            <?php foreach ($tipos_disponiveis as $tipo): ?>
                                <option value="<?= htmlspecialchars($tipo) ?>" 
                                        <?= $filtros['tipo_projeto'] === $tipo ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($tipo) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label fw-bold">
                            <i class="bi bi-rulers"></i> Largura do Terreno
                        </label>
                        <select class="form-select" name="largura_terreno">
                            <option value="">Qualquer largura</option>
                            <?php foreach ($larguras_disponiveis as $largura): ?>
                                <option value="<?= $largura ?>" 
                                        <?= $filtros['largura_terreno'] == $largura ? 'selected' : '' ?>>
                                    <?= $largura ?>m
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-12">
                        <label class="form-label fw-bold">
                            <i class="bi bi-star"></i> Destaque
                        </label>
                        <select class="form-select" name="destaque">
                            <option value="">Todos</option>
                            <option value="1" <?= $filtros['destaque'] === '1' ? 'selected' : '' ?>>
                                Em destaque
                            </option>
                        </select>
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
                                    <?php if (!empty($projeto['tipo_projeto'])): ?>
                                        <div class="public-meta-item">
                                            <i class="bi bi-house-gear public-meta-icon"></i>
                                            <?= htmlspecialchars($projeto['tipo_projeto']) ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($projeto['largura_terreno'] && $projeto['comprimento_terreno']): ?>
                                        <div class="public-meta-item">
                                            <i class="bi bi-rulers public-meta-icon"></i>
                                            <?= $projeto['largura_terreno'] ?>m x <?= $projeto['comprimento_terreno'] ?>m
                                        </div>
                                    <?php endif; ?>
                                    <div class="public-meta-item">
                                        <i class="bi bi-bounding-box public-meta-icon"></i>
                                        <?= formatarArea($projeto['area_terreno']) ?>
                                    </div>
                                    <div class="public-meta-item">
                                        <i class="bi bi-door-open public-meta-icon"></i>
                                        <?= max(0, (int)$projeto['quartos']) ?> quarto(s)
                                    </div>
                                    <div class="public-meta-item">
                                        <i class="bi bi-house-door public-meta-icon"></i>
                                        <?= max(0, (int)$projeto['total_comodos']) ?> cômodos
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

        // Auto-submit para campos numéricos e selects com validação
        const numericInputs = document.querySelectorAll('input[type="number"]');
        numericInputs.forEach(input => {
            input.addEventListener('change', function() {
                // Validar se o valor é positivo
                if (this.value !== '' && parseFloat(this.value) < 0) {
                    this.value = '';
                    return;
                }
                
                setTimeout(() => {
                    document.getElementById('filtrosForm').submit();
                }, 300);
            });
        });

        const selectInputs = document.querySelectorAll('select[name="tipo_projeto"], select[name="largura_terreno"], select[name="destaque"]');
        selectInputs.forEach(select => {
            select.addEventListener('change', function() {
                document.getElementById('filtrosForm').submit();
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

<?php require_once '../includes/footer.php'; ?>
