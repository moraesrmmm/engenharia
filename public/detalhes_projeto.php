<?php
require_once './../config/config.php';
require_once './../includes/funcoes.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = intval($_GET['id']);

// Buscar projeto
$stmt = $pdo->prepare("SELECT * FROM projetos WHERE id = ? AND ativo = TRUE");
$stmt->execute([$id]);
$projeto = $stmt->fetch();

if (!$projeto) {
    header("Location: index.php");
    exit;
}

// Buscar cômodos
$comodos = $pdo->prepare("SELECT * FROM comodos WHERE projeto_id = ? AND ativo = TRUE ORDER BY tipo, nome");
$comodos->execute([$id]);
$comodosData = $comodos->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($projeto['titulo']) ?> - Projetos de Engenharia</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?= asset('css/style.css') ?>" rel="stylesheet">
    <link href="<?= asset('css/detalhes.css') ?>" rel="stylesheet">
</head>
<body>
    <!-- Hero Section -->
    <section class="projeto-hero">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10 text-center">
                    <?php if ($projeto['destaque']): ?>
                        <div class="destaque-badge">
                            <i class="bi bi-star-fill"></i> Projeto em Destaque
                        </div>
                    <?php endif; ?>
                    
                    <h1 class="projeto-title"><?= htmlspecialchars($projeto['titulo']) ?></h1>
                    <p class="projeto-subtitle">
                        <?= formatarDimensoes($projeto['largura'], $projeto['comprimento']) ?> • 
                        <?= formatarArea($projeto['area']) ?>
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Content Section -->
    <section class="projeto-content">
        <div class="container">
            <!-- Botão Voltar -->
            <a href="index.php" class="back-button">
                <i class="bi bi-arrow-left"></i>
                Voltar aos Projetos
            </a>

            <div class="row">
                <!-- Coluna Principal -->
                <div class="col-lg-8">
                    <!-- Imagem Principal -->
                    <div class="projeto-image-container">
                        <img src="<?= asset('uploads/' . htmlspecialchars($projeto['capa_imagem'])) ?>" 
                             class="projeto-image" 
                             alt="<?= htmlspecialchars($projeto['titulo']) ?>">
                    </div>

                    <!-- Descrição -->
                    <div class="description-card">
                        <h3 class="mb-4"><i class="bi bi-file-text"></i> Descrição do Projeto</h3>
                        <p class="description-text"><?= nl2br(htmlspecialchars($projeto['descricao'])) ?></p>
                    </div>

                    <!-- Vídeo (se existir) -->
                    <?php if (!empty($projeto['video_url'])): ?>
                        <div class="mb-5">
                            <h3 class="section-title">Vídeo do Projeto</h3>
                            <div class="video-container">
                                <iframe src="<?= htmlspecialchars(convertYouTubeUrl($projeto['video_url'])) ?>" 
                                        title="Vídeo do projeto" 
                                        allowfullscreen></iframe>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Sidebar com Informações -->
                <div class="col-lg-4">
                    <div class="info-cards-grid">
                        <!-- Dimensões -->
                        <div class="projeto-info-card">
                            <div class="info-icon">
                                <i class="bi bi-rulers"></i>
                            </div>
                            <div class="info-title">Dimensões</div>
                            <div class="info-value"><?= htmlspecialchars($projeto['largura_comprimento']) ?></div>
                        </div>

                        <!-- Área -->
                        <div class="projeto-info-card">
                            <div class="info-icon">
                                <i class="bi bi-bounding-box"></i>
                            </div>
                            <div class="info-title">Área Total</div>
                            <div class="info-value"><?= formatarArea($projeto['area']) ?></div>
                        </div>

                        <!-- Preço Total -->
                        <?php if ($projeto['valor_projeto']): ?>
                            <div class="projeto-info-card">
                                <div class="info-icon">
                                    <i class="bi bi-currency-dollar"></i>
                                </div>
                                <div class="info-title">Investimento</div>
                                <div class="info-value"><?= formatarMoeda($projeto['valor_projeto']) ?></div>
                            </div>
                        <?php endif; ?>

                        <!-- Mão de Obra -->
                        <?php if ($projeto['custo_mao_obra']): ?>
                            <div class="projeto-info-card">
                                <div class="info-icon">
                                    <i class="bi bi-people"></i>
                                </div>
                                <div class="info-title">Mão de Obra</div>
                                <div class="info-value"><?= formatarMoeda($projeto['custo_mao_obra']) ?></div>
                            </div>
                        <?php endif; ?>

                        <!-- Materiais -->
                        <?php if ($projeto['custo_materiais']): ?>
                            <div class="projeto-info-card">
                                <div class="info-icon">
                                    <i class="bi bi-tools"></i>
                                </div>
                                <div class="info-title">Materiais</div>
                                <div class="info-value"><?= formatarMoeda($projeto['custo_materiais']) ?></div>
                            </div>
                        <?php endif; ?>

                        <!-- Cômodos -->
                        <div class="projeto-info-card">
                            <div class="info-icon">
                                <i class="bi bi-house-door"></i>
                            </div>
                            <div class="info-title">Cômodos</div>
                            <div class="info-value"><?= count($comodosData) ?> ambientes</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Seção de Cômodos -->
            <?php if (count($comodosData) > 0): ?>
                <div class="comodos-section">
                    <h2 class="section-title">Ambientes do Projeto</h2>
                    <div class="comodos-grid">
                        <?php foreach ($comodosData as $comodo): ?>
                            <div class="comodo-card">
                                <div class="comodo-content">
                                    <div class="comodo-tipo">
                                        <?php
                                        // Ícones por tipo de cômodo
                                        $icones = [
                                            'Sala de Estar' => 'bi-tv',
                                            'Sala de Jantar' => 'bi-table',
                                            'Cozinha' => 'bi-cup-hot',
                                            'Quarto' => 'bi-bed',
                                            'Suíte' => 'bi-bed-fill',
                                            'Banheiro' => 'bi-droplet',
                                            'Lavanderia' => 'bi-washing-machine',
                                            'Área Gourmet' => 'bi-fire',
                                            'Escritório' => 'bi-laptop',
                                            'Closet' => 'bi-handbag',
                                            'Varanda' => 'bi-tree',
                                            'Garagem' => 'bi-car-front'
                                        ];
                                        $icone = $icones[$comodo['tipo']] ?? 'bi-house';
                                        ?>
                                        <i class="<?= $icone ?>"></i>
                                        <?= htmlspecialchars($comodo['tipo']) ?>
                                    </div>
                                    
                                    <?php if ($comodo['nome'] !== $comodo['tipo']): ?>
                                        <div class="comodo-nome"><?= htmlspecialchars($comodo['nome']) ?></div>
                                    <?php endif; ?>
                                    
                                    <div class="comodo-dimensoes">
                                        <i class="bi bi-arrows-angle-expand"></i>
                                        <?= formatarDimensoes($comodo['largura'], $comodo['comprimento']) ?>
                                    </div>
                                    
                                    <?php if (!empty($comodo['observacoes'])): ?>
                                        <div class="comodo-observacoes">
                                            <i class="bi bi-info-circle"></i>
                                            <?= nl2br(htmlspecialchars($comodo['observacoes'])) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Call to Action -->
            <div class="text-center mt-5 mb-4">
                <a href="contato.php" class="btn btn-lg back-button">
                    <i class="bi bi-envelope"></i>
                    Interessado? Entre em Contato
                </a>
            </div>
        </div>
    </section>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Animações ao scroll -->
    <script>
        // Adiciona animações quando elementos entram na tela
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

        // Observa todos os elementos com animação
        document.querySelectorAll('.projeto-info-card, .comodo-card, .description-card, .video-container').forEach(el => {
            observer.observe(el);
        });

        // Efeito parallax suave no hero
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            const hero = document.querySelector('.projeto-hero');
            if (hero) {
                hero.style.transform = `translateY(${scrolled * 0.5}px)`;
            }
        });
    </script>
</body>
</html>
