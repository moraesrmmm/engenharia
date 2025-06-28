<?php 
require_once '../auth.php';
require_once '../../config/config.php';

// Verifica se foi passado um ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message'] = 'ID do projeto inválido!';
    header("Location: listar_projetos.php");
    exit;
}

$projeto_id = intval($_GET['id']);

try {
    // Busca os dados do projeto
    $stmt = $pdo->prepare("SELECT * FROM projetos WHERE id = ? AND ativo = TRUE");
    $stmt->execute([$projeto_id]);
    $projeto = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$projeto) {
        $_SESSION['error_message'] = 'Projeto não encontrado!';
        header("Location: listar_projetos.php");
        exit;
    }
    
    // Busca os andares do projeto
    $stmt_andares = $pdo->prepare("SELECT * FROM andares WHERE projeto_id = ? AND ativo = TRUE ORDER BY ordem, id");
    $stmt_andares->execute([$projeto_id]);
    $andares = $stmt_andares->fetchAll(PDO::FETCH_ASSOC);
    
    // Busca os cômodos para cada andar
    foreach ($andares as &$andar) {
        $stmt_comodos = $pdo->prepare("SELECT * FROM comodos WHERE projeto_id = ? AND andar_id = ? AND ativo = TRUE ORDER BY id");
        $stmt_comodos->execute([$projeto_id, $andar['id']]);
        $andar['comodos'] = $stmt_comodos->fetchAll(PDO::FETCH_ASSOC);
    }
    
} catch (Exception $e) {
    $_SESSION['error_message'] = 'Erro ao carregar projeto: ' . $e->getMessage();
    header("Location: listar_projetos.php");
    exit;
}

require_once '../includes/header.php'; 
?>

<!-- Mensagens de feedback -->
<?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle"></i> <?= $_SESSION['error_message'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['error_message']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle"></i> <?= $_SESSION['success_message'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

<div class="admin-card">
    <div class="admin-card-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0">
                    <i class="bi bi-pencil-square"></i> Editar Projeto
                </h4>
                <small class="opacity-75">Editando: <?= htmlspecialchars($projeto['titulo']) ?></small>
            </div>
            <div class="d-flex gap-2">
                <a href="listar_projetos.php" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
                <a href="../dashboard.php" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-house"></i> Dashboard
                </a>
            </div>
        </div>
    </div>
    
    <div class="admin-card-body">
        <!-- Progress Bar -->
        <div class="mb-4">
            <div class="progress" style="height: 8px;">
                <div class="progress-bar" role="progressbar" style="width: 0%" id="form-progress"></div>
            </div>
            <small class="text-muted">Progresso do formulário</small>
        </div>

        <form action="../backend/editar_projeto.php" method="POST" enctype="multipart/form-data" id="projeto-form">
            <input type="hidden" name="projeto_id" value="<?= $projeto['id'] ?>">
            
            <!-- Seção 1: Informações Básicas -->
            <div class="form-section active" data-section="1">
                <h5 class="section-title">
                    <i class="bi bi-info-circle text-primary"></i> Informações Básicas
                </h5>
                
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-bold">
                            <i class="bi bi-pencil"></i> Título do Projeto *
                        </label>
                        <input type="text" name="titulo" class="form-control" required 
                               value="<?= htmlspecialchars($projeto['titulo']) ?>"
                               placeholder="Ex: Casa Moderna em Condomínio Fechado">
                        <div class="invalid-feedback">Por favor, informe o título do projeto.</div>
                    </div>
                    
                    <div class="col-12">
                        <label class="form-label fw-bold">
                            <i class="bi bi-text-paragraph"></i> Descrição do Projeto *
                        </label>
                        <textarea name="descricao" class="form-control" rows="4" required
                                  placeholder="Descreva detalhadamente o projeto, suas características principais e diferenciais..."><?= htmlspecialchars($projeto['descricao']) ?></textarea>
                        <div class="invalid-feedback">Por favor, descreva o projeto.</div>
                    </div>
                    
                    <div class="col-12">
                        <label class="form-label fw-bold">
                            <i class="bi bi-house-fill"></i> Tipo de Projeto *
                        </label>
                        <select name="tipo_projeto" class="form-select" required>
                            <option value="">Selecione o tipo de projeto...</option>
                            <optgroup label="Residencial Térrea">
                                <option value="Casa Térrea" <?= ($projeto['tipo_projeto'] ?? '') == 'Casa Térrea' ? 'selected' : '' ?>>Casa Térrea</option>
                                <option value="Casa de Campo" <?= ($projeto['tipo_projeto'] ?? '') == 'Casa de Campo' ? 'selected' : '' ?>>Casa de Campo</option>
                                <option value="Casa de Praia" <?= ($projeto['tipo_projeto'] ?? '') == 'Casa de Praia' ? 'selected' : '' ?>>Casa de Praia</option>
                                <option value="Casa com Piscina" <?= ($projeto['tipo_projeto'] ?? '') == 'Casa com Piscina' ? 'selected' : '' ?>>Casa com Piscina</option>
                                <option value="Casa Geminada" <?= ($projeto['tipo_projeto'] ?? '') == 'Casa Geminada' ? 'selected' : '' ?>>Casa Geminada</option>
                                <option value="Chalé" <?= ($projeto['tipo_projeto'] ?? '') == 'Chalé' ? 'selected' : '' ?>>Chalé</option>
                            </optgroup>
                            <optgroup label="Residencial Múltiplos Pavimentos">
                                <option value="Sobrado" <?= ($projeto['tipo_projeto'] ?? '') == 'Sobrado' ? 'selected' : '' ?>>Sobrado</option>
                                <option value="Casa Duplex" <?= ($projeto['tipo_projeto'] ?? '') == 'Casa Duplex' ? 'selected' : '' ?>>Casa Duplex</option>
                                <option value="Casa Triplex" <?= ($projeto['tipo_projeto'] ?? '') == 'Casa Triplex' ? 'selected' : '' ?>>Casa Triplex</option>
                                <option value="Casa com Mezanino" <?= ($projeto['tipo_projeto'] ?? '') == 'Casa com Mezanino' ? 'selected' : '' ?>>Casa com Mezanino</option>
                                <option value="Casa com Loft" <?= ($projeto['tipo_projeto'] ?? '') == 'Casa com Loft' ? 'selected' : '' ?>>Casa com Loft</option>
                                <option value="Mansão" <?= ($projeto['tipo_projeto'] ?? '') == 'Mansão' ? 'selected' : '' ?>>Mansão</option>
                            </optgroup>
                            <optgroup label="Estilos Arquitetônicos">
                                <option value="Residência Moderna" <?= ($projeto['tipo_projeto'] ?? '') == 'Residência Moderna' ? 'selected' : '' ?>>Residência Moderna</option>
                                <option value="Residência Clássica" <?= ($projeto['tipo_projeto'] ?? '') == 'Residência Clássica' ? 'selected' : '' ?>>Residência Clássica</option>
                                <option value="Residência Minimalista" <?= ($projeto['tipo_projeto'] ?? '') == 'Residência Minimalista' ? 'selected' : '' ?>>Residência Minimalista</option>
                            </optgroup>
                            <optgroup label="Construção Especial">
                                <option value="Casa Container" <?= ($projeto['tipo_projeto'] ?? '') == 'Casa Container' ? 'selected' : '' ?>>Casa Container</option>
                                <option value="Casa Sustentável" <?= ($projeto['tipo_projeto'] ?? '') == 'Casa Sustentável' ? 'selected' : '' ?>>Casa Sustentável</option>
                                <option value="Casa Pré-Fabricada" <?= ($projeto['tipo_projeto'] ?? '') == 'Casa Pré-Fabricada' ? 'selected' : '' ?>>Casa Pré-Fabricada</option>
                                <option value="Casa com Edícula" <?= ($projeto['tipo_projeto'] ?? '') == 'Casa com Edícula' ? 'selected' : '' ?>>Casa com Edícula</option>
                            </optgroup>
                            <optgroup label="Apartamentos">
                                <option value="Kitnet" <?= ($projeto['tipo_projeto'] ?? '') == 'Kitnet' ? 'selected' : '' ?>>Kitnet</option>
                                <option value="Studio" <?= ($projeto['tipo_projeto'] ?? '') == 'Studio' ? 'selected' : '' ?>>Studio</option>
                                <option value="Apartamento" <?= ($projeto['tipo_projeto'] ?? '') == 'Apartamento' ? 'selected' : '' ?>>Apartamento</option>
                                <option value="Cobertura" <?= ($projeto['tipo_projeto'] ?? '') == 'Cobertura' ? 'selected' : '' ?>>Cobertura</option>
                            </optgroup>
                            <optgroup label="Predial">
                                <option value="Prédio Residencial" <?= ($projeto['tipo_projeto'] ?? '') == 'Prédio Residencial' ? 'selected' : '' ?>>Prédio Residencial</option>
                                <option value="Prédio Comercial" <?= ($projeto['tipo_projeto'] ?? '') == 'Prédio Comercial' ? 'selected' : '' ?>>Prédio Comercial</option>
                            </optgroup>
                            <optgroup label="Comercial">
                                <option value="Casa Comercial" <?= ($projeto['tipo_projeto'] ?? '') == 'Casa Comercial' ? 'selected' : '' ?>>Casa Comercial</option>
                                <option value="Escritório" <?= ($projeto['tipo_projeto'] ?? '') == 'Escritório' ? 'selected' : '' ?>>Escritório</option>
                                <option value="Loja" <?= ($projeto['tipo_projeto'] ?? '') == 'Loja' ? 'selected' : '' ?>>Loja</option>
                                <option value="Consultório" <?= ($projeto['tipo_projeto'] ?? '') == 'Consultório' ? 'selected' : '' ?>>Consultório</option>
                                <option value="Clínica" <?= ($projeto['tipo_projeto'] ?? '') == 'Clínica' ? 'selected' : '' ?>>Clínica</option>
                                <option value="Restaurante" <?= ($projeto['tipo_projeto'] ?? '') == 'Restaurante' ? 'selected' : '' ?>>Restaurante</option>
                                <option value="Cafeteria" <?= ($projeto['tipo_projeto'] ?? '') == 'Cafeteria' ? 'selected' : '' ?>>Cafeteria</option>
                                <option value="Academia" <?= ($projeto['tipo_projeto'] ?? '') == 'Academia' ? 'selected' : '' ?>>Academia</option>
                                <option value="Salão de Beleza" <?= ($projeto['tipo_projeto'] ?? '') == 'Salão de Beleza' ? 'selected' : '' ?>>Salão de Beleza</option>
                            </optgroup>
                            <optgroup label="Industrial">
                                <option value="Galpão" <?= ($projeto['tipo_projeto'] ?? '') == 'Galpão' ? 'selected' : '' ?>>Galpão</option>
                                <option value="Barracão" <?= ($projeto['tipo_projeto'] ?? '') == 'Barracão' ? 'selected' : '' ?>>Barracão</option>
                                <option value="Oficina" <?= ($projeto['tipo_projeto'] ?? '') == 'Oficina' ? 'selected' : '' ?>>Oficina</option>
                                <option value="Depósito" <?= ($projeto['tipo_projeto'] ?? '') == 'Depósito' ? 'selected' : '' ?>>Depósito</option>
                                <option value="Armazém" <?= ($projeto['tipo_projeto'] ?? '') == 'Armazém' ? 'selected' : '' ?>>Armazém</option>
                            </optgroup>
                            <option value="Outro" <?= ($projeto['tipo_projeto'] ?? '') == 'Outro' ? 'selected' : '' ?>>Outro</option>
                        </select>
                        <div class="invalid-feedback">Por favor, selecione o tipo de projeto.</div>
                        <small class="text-muted">Escolha a categoria que melhor descreve o projeto</small>
                    </div>
                    
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="destaque" id="destaque" value="1" 
                                   <?= $projeto['destaque'] ? 'checked' : '' ?>>
                            <label class="form-check-label fw-bold" for="destaque">
                                <i class="bi bi-star-fill text-warning"></i> Projeto em Destaque
                            </label>
                            <small class="text-muted d-block">Projetos em destaque aparecem na página inicial</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Seção 2: Dimensões do Terreno -->
            <div class="form-section" data-section="2">
                <hr class="section-divider">
                <h5 class="section-title">
                    <i class="bi bi-rulers text-primary"></i> Dimensões do Terreno
                </h5>
                
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">
                            <i class="bi bi-arrow-left-right"></i> Largura do Terreno (m) *
                        </label>
                        <input type="number" step="0.01" name="largura_terreno" class="form-control" required 
                               value="<?= $projeto['largura_terreno'] ?? '' ?>"
                               placeholder="0.00" id="largura-terreno" onchange="calcularAreaTerreno()">
                        <div class="invalid-feedback">Informe a largura do terreno em metros.</div>
                        <small class="text-muted">Largura em metros</small>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label fw-bold">
                            <i class="bi bi-arrow-up-down"></i> Comprimento do Terreno (m) *
                        </label>
                        <input type="number" step="0.01" name="comprimento_terreno" class="form-control" required 
                               value="<?= $projeto['comprimento_terreno'] ?? '' ?>"
                               placeholder="0.00" id="comprimento-terreno" onchange="calcularAreaTerreno()">
                        <div class="invalid-feedback">Informe o comprimento do terreno em metros.</div>
                        <small class="text-muted">Comprimento em metros</small>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label fw-bold">
                            <i class="bi bi-badge-ad"></i> Área do Terreno (m²) *
                        </label>
                        <input type="number" step="0.01" name="area_terreno" class="form-control" required readonly
                               value="<?= $projeto['area_terreno'] ?? '' ?>"
                               placeholder="0.00" style="background-color: #f8f9fa;" id="area-terreno-display">
                        <div class="invalid-feedback">Área calculada automaticamente.</div>
                        <small class="text-muted">Calculado automaticamente</small>
                    </div>
                </div>
            </div>
            <!-- Seção 3: Área Construída -->
            <div class="form-section" data-section="3">
                <hr class="section-divider">
                <h5 class="section-title">
                    <i class="bi bi-building text-primary"></i> Área Construída
                </h5>
                
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label fw-bold">
                            <i class="bi bi-building"></i> Área Construída (m²)
                        </label>
                        <input type="number" step="0.01" name="area_construida" class="form-control" readonly 
                               value="<?= $projeto['area_construida'] ?? '' ?>"
                               placeholder="0.00" style="background-color: #f8f9fa;" id="area-construida-display">
                        <small class="text-muted">Soma automática das áreas dos andares</small>
                    </div>
                </div>
            </div>

            <!-- Seção 4: Custos -->
            <div class="form-section" data-section="4">
                <hr class="section-divider">
                <h5 class="section-title">
                    <i class="bi bi-currency-dollar text-primary"></i> Custos do Projeto
                </h5>
                
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">
                            <i class="bi bi-currency-dollar"></i> Valor do Projeto (R$) *
                        </label>
                        <input type="number" step="0.01" name="valor_projeto" class="form-control" required 
                               value="<?= $projeto['valor_projeto'] ?>"
                               placeholder="0.00">
                        <div class="invalid-feedback">Informe o valor do projeto.</div>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label fw-bold">
                            <i class="bi bi-hammer"></i> Custo Mão de Obra (R$) *
                        </label>
                        <input type="number" step="0.01" name="custo_mao_obra" class="form-control" required 
                               value="<?= $projeto['custo_mao_obra'] ?>"
                               placeholder="0.00">
                        <div class="invalid-feedback">Informe o custo da mão de obra.</div>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label fw-bold">
                            <i class="bi bi-box-seam"></i> Custo Materiais (R$) *
                        </label>
                        <input type="number" step="0.01" name="custo_materiais" class="form-control" required 
                               value="<?= $projeto['custo_materiais'] ?>"
                               placeholder="0.00">
                        <div class="invalid-feedback">Informe o custo dos materiais.</div>
                    </div>
                </div>
            </div>

            <!-- Seção 5: Mídia -->
            <div class="form-section" data-section="5">
                <hr class="section-divider">
                <h5 class="section-title">
                    <i class="bi bi-camera text-primary"></i> Mídia do Projeto
                </h5>
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">
                            <i class="bi bi-youtube"></i> Vídeo do Projeto
                        </label>
                        <input type="url" name="video_url" class="form-control" 
                               value="<?= htmlspecialchars($projeto['video_url']) ?>"
                               placeholder="https://www.youtube.com/watch?v=...">
                        <small class="text-muted">URL do YouTube (opcional)</small>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label fw-bold">
                            <i class="bi bi-image"></i> Nova Imagem de Capa
                        </label>
                        <input type="file" name="capa_imagem" accept="image/*" class="form-control"
                               onchange="previewImagem(this)">
                        <small class="text-muted">Deixe em branco para manter a imagem atual</small>
                    </div>
                    
                    <div class="col-12">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="fw-bold d-block mb-2">Imagem Atual:</label>
                                <?php if ($projeto['capa_imagem']): ?>
                                    <img src="../../public/uploads/<?= htmlspecialchars($projeto['capa_imagem']) ?>" 
                                         alt="Imagem atual" 
                                         style="max-width: 100%; max-height: 200px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                                <?php else: ?>
                                    <div class="text-muted">Nenhuma imagem</div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label class="fw-bold d-block mb-2">Nova Imagem (Preview):</label>
                                <img id="preview-imagem" src="" alt="Preview" 
                                     style="max-width: 100%; max-height: 200px; display: none; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Seção 6: Andares e Cômodos -->
            <div class="form-section" data-section="6">
                <hr class="section-divider">
                <h5 class="section-title">
                    <i class="bi bi-building text-primary"></i> Andares do Projeto
                </h5>
                
                <div id="andares-container">
                    <?php foreach ($andares as $andar_index => $andar): ?>
                        <div class="andar-item border rounded-3 p-4 mb-4" style="background-color: #f8f9fa;" data-andar-index="<?= $andar_index ?>">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0 text-primary"><i class="bi bi-layers-fill"></i> <?= htmlspecialchars($andar['nome']) ?: 'Andar ' . ($andar_index + 1) ?></h6>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removerAndar(this)">
                                    <i class="bi bi-trash"></i> Remover Andar
                                </button>
                            </div>
                            
                            <input type="hidden" name="andares[<?= $andar_index ?>][id]" value="<?= $andar['id'] ?>">
                            
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Nome do Andar:</label>
                                    <input type="text" name="andares[<?= $andar_index ?>][nome]" class="form-control" 
                                           value="<?= htmlspecialchars($andar['nome']) ?>"
                                           placeholder="Ex: Térreo, Primeiro Andar, Segundo Andar">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Área do Andar (m²) *:</label>
                                    <input type="number" step="0.01" name="andares[<?= $andar_index ?>][area]" class="form-control" 
                                           value="<?= $andar['area'] ?>"
                                           placeholder="0.00" required onchange="updateProgress(); calcularAreaConstruida()">
                                    <div class="invalid-feedback">Informe a área do andar.</div>
                                </div>
                            </div>
                            
                            <div class="border-top pt-3">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="mb-0"><i class="bi bi-door-open text-success"></i> Cômodos deste Andar</h6>
                                    <button type="button" class="btn btn-sm btn-outline-success mb-2" onclick="adicionarComodo(<?= $andar_index ?>)">
                                        <i class="bi bi-plus-circle"></i> Adicionar Cômodo
                                    </button>
                                </div>
                                <div id="comodos-andar-<?= $andar_index ?>" class="comodos-container">
                                    <?php foreach ($andar['comodos'] as $comodo_index => $comodo): ?>
                                        <div class="comodo-item row g-3 align-items-end p-3 mb-3 border rounded" style="background-color: #ffffff;">
                                            <input type="hidden" name="andares[<?= $andar_index ?>][comodos][<?= $comodo_index ?>][id]" value="<?= $comodo['id'] ?>">
                                            
                                            <div class="col-md-3">
                                                <label class="form-label fw-bold">Tipo *:</label>
                                                <select name="andares[<?= $andar_index ?>][comodos][<?= $comodo_index ?>][tipo]" class="form-select" required>
                                                    <option value="">Selecione...</option>
                                                    <option value="Quarto" <?= $comodo['tipo'] == 'Quarto' ? 'selected' : '' ?>>Quarto</option>
                                                    <option value="Suíte" <?= $comodo['tipo'] == 'Suíte' ? 'selected' : '' ?>>Suíte</option>
                                                    <option value="Sala de Estar" <?= $comodo['tipo'] == 'Sala de Estar' ? 'selected' : '' ?>>Sala de Estar</option>
                                                    <option value="Sala de Jantar" <?= $comodo['tipo'] == 'Sala de Jantar' ? 'selected' : '' ?>>Sala de Jantar</option>
                                                    <option value="Cozinha" <?= $comodo['tipo'] == 'Cozinha' ? 'selected' : '' ?>>Cozinha</option>
                                                    <option value="Banheiro" <?= $comodo['tipo'] == 'Banheiro' ? 'selected' : '' ?>>Banheiro</option>
                                                    <option value="Lavanderia" <?= $comodo['tipo'] == 'Lavanderia' ? 'selected' : '' ?>>Lavanderia</option>
                                                    <option value="Closet" <?= $comodo['tipo'] == 'Closet' ? 'selected' : '' ?>>Closet</option>
                                                    <option value="Área Gourmet" <?= $comodo['tipo'] == 'Área Gourmet' ? 'selected' : '' ?>>Área Gourmet</option>
                                                    <option value="Jardim" <?= $comodo['tipo'] == 'Jardim' ? 'selected' : '' ?>>Jardim</option>
                                                    <option value="Garagem" <?= $comodo['tipo'] == 'Garagem' ? 'selected' : '' ?>>Garagem</option>
                                                    <option value="Churrasqueira" <?= $comodo['tipo'] == 'Churrasqueira' ? 'selected' : '' ?>>Churrasqueira</option>
                                                    <option value="Varanda" <?= $comodo['tipo'] == 'Varanda' ? 'selected' : '' ?>>Varanda</option>
                                                    <option value="Edícula" <?= $comodo['tipo'] == 'Edícula' ? 'selected' : '' ?>>Edícula</option>
                                                    <option value="Escritório" <?= $comodo['tipo'] == 'Escritório' ? 'selected' : '' ?>>Escritório</option>
                                                    <option value="Despensa" <?= $comodo['tipo'] == 'Despensa' ? 'selected' : '' ?>>Despensa</option>
                                                    <option value="Hall" <?= $comodo['tipo'] == 'Hall' ? 'selected' : '' ?>>Hall</option>
                                                    <option value="Corredor" <?= $comodo['tipo'] == 'Corredor' ? 'selected' : '' ?>>Corredor</option>
                                                </select>
                                                <div class="invalid-feedback">Selecione o tipo do cômodo.</div>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label fw-bold">Nome:</label>
                                                <input type="text" name="andares[<?= $andar_index ?>][comodos][<?= $comodo_index ?>][nome]" class="form-control" 
                                                       value="<?= htmlspecialchars($comodo['nome']) ?>"
                                                       placeholder="Ex: Quarto do Casal">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label fw-bold">Observações:</label>
                                                <input type="text" name="andares[<?= $andar_index ?>][comodos][<?= $comodo_index ?>][observacoes]" class="form-control" 
                                                       value="<?= htmlspecialchars($comodo['observacoes'] ?? '') ?>"
                                                       placeholder="Detalhes específicos do cômodo...">
                                            </div>
                                            <div class="col-md-2 text-end">
                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removerComodo(this)">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="mt-3">
                    <button type="button" class="btn btn-outline-primary" onclick="adicionarAndar()">
                        <i class="bi bi-plus-circle"></i> Adicionar Andar
                    </button>
                </div>
            </div>

            <!-- Botões de Ação -->
            <hr class="section-divider">
            <div class="d-flex gap-3 justify-content-end">
                <a href="listar_projetos.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-primary" id="salvar-btn">
                    <i class="bi bi-check-circle"></i> Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let andaresCount = <?= count($andares) ?>;
let comodosCount = 0;

// Função para calcular área do terreno automaticamente
function calcularAreaTerreno() {
    const largura = parseFloat(document.getElementById('largura-terreno').value) || 0;
    const comprimento = parseFloat(document.getElementById('comprimento-terreno').value) || 0;
    const area = largura * comprimento;
    document.getElementById('area-terreno-display').value = area.toFixed(2);
    
    updateProgress();
}

// Função para preview da imagem
function previewImagem(input) {
    const preview = document.getElementById('preview-imagem');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        }
        
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.style.display = 'none';
    }
    
    updateProgress();
}

function adicionarAndar() {
    const container = document.getElementById('andares-container');
    const andarIndex = andaresCount++;

    const div = document.createElement('div');
    div.className = 'andar-item border rounded-3 p-4 mb-4';
    div.style.backgroundColor = '#f8f9fa';
    div.setAttribute('data-andar-index', andarIndex);
    
    div.innerHTML = `
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-primary"><i class="bi bi-layers-fill"></i> Andar ${andarIndex + 1}</h6>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removerAndar(this)">
                <i class="bi bi-trash"></i> Remover Andar
            </button>
        </div>
        
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label fw-bold">Nome do Andar:</label>
                <input type="text" name="andares[${andarIndex}][nome]" class="form-control" 
                       placeholder="Ex: Térreo, Primeiro Andar, Segundo Andar">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold">Área do Andar (m²) *:</label>
                <input type="number" step="0.01" name="andares[${andarIndex}][area]" class="form-control" 
                       placeholder="0.00" required onchange="updateProgress(); calcularAreaConstruida()">
                <div class="invalid-feedback">Informe a área do andar.</div>
            </div>
        </div>
        
        <div class="border-top pt-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0"><i class="bi bi-door-open text-success"></i> Cômodos deste Andar</h6>
                <button type="button" class="btn btn-sm btn-outline-success mb-2" onclick="adicionarComodo(${andarIndex})">
                    <i class="bi bi-plus-circle"></i> Adicionar Cômodo
                </button>
            </div>
            <div id="comodos-andar-${andarIndex}" class="comodos-container">
                <!-- Cômodos serão adicionados aqui -->
            </div>
        </div>
    `;
    
    container.appendChild(div);
    
    // Adicionar primeiro cômodo automaticamente
    adicionarComodo(andarIndex);
    updateProgress();
    calcularAreaConstruida();
}

function adicionarComodo(andarIndex) {
    const container = document.getElementById(`comodos-andar-${andarIndex}`);
    const comodoIndex = container.children.length;

    const div = document.createElement('div');
    div.className = 'comodo-item row g-3 align-items-end p-3 mb-3 border rounded';
    div.style.backgroundColor = '#ffffff';
    
    div.innerHTML = `
        <div class="col-md-3">
            <label class="form-label fw-bold">Tipo *:</label>
            <select name="andares[${andarIndex}][comodos][${comodoIndex}][tipo]" class="form-select" required>
                <option value="">Selecione...</option>
                <option value="Quarto">Quarto</option>
                <option value="Suíte">Suíte</option>
                <option value="Sala de Estar">Sala de Estar</option>
                <option value="Sala de Jantar">Sala de Jantar</option>
                <option value="Cozinha">Cozinha</option>
                <option value="Banheiro">Banheiro</option>
                <option value="Lavanderia">Lavanderia</option>
                <option value="Closet">Closet</option>
                <option value="Área Gourmet">Área Gourmet</option>
                <option value="Jardim">Jardim</option>
                <option value="Garagem">Garagem</option>
                <option value="Churrasqueira">Churrasqueira</option>
                <option value="Varanda">Varanda</option>
                <option value="Edícula">Edícula</option>
                <option value="Escritório">Escritório</option>
                <option value="Despensa">Despensa</option>
                <option value="Hall">Hall</option>
                <option value="Corredor">Corredor</option>
            </select>
            <div class="invalid-feedback">Selecione o tipo do cômodo.</div>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-bold">Nome:</label>
            <input type="text" name="andares[${andarIndex}][comodos][${comodoIndex}][nome]" class="form-control" 
                   placeholder="Ex: Quarto do Casal">
        </div>
        <div class="col-md-4">
            <label class="form-label fw-bold">Observações:</label>
            <input type="text" name="andares[${andarIndex}][comodos][${comodoIndex}][observacoes]" class="form-control" 
                   placeholder="Detalhes específicos do cômodo...">
        </div>
        <div class="col-md-2 text-end">
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removerComodo(this)">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    `;
    
    container.appendChild(div);
    updateProgress();
}

function removerAndar(button) {
    if (confirm('Tem certeza que deseja remover este andar e todos os seus cômodos?')) {
        button.closest('.andar-item').remove();
        updateProgress();
        calcularAreaConstruida();
    }
}

function removerComodo(button) {
    button.closest('.comodo-item').remove();
    updateProgress();
}

function calcularAreaConstruida() {
    const areaInputs = document.querySelectorAll('input[name*="[area]"]');
    let totalArea = 0;
    
    areaInputs.forEach(input => {
        const valor = parseFloat(input.value) || 0;
        totalArea += valor;
    });
    
    const areaDisplay = document.getElementById('area-construida-display');
    if (areaDisplay) {
        areaDisplay.value = totalArea.toFixed(2);
    }
}

// Função para atualizar progresso do formulário
function updateProgress() {
    const campos = document.querySelectorAll('#projeto-form input[required], #projeto-form textarea[required], #projeto-form select[required]');
    let preenchidos = 0;
    
    campos.forEach(campo => {
        if (campo.value.trim() !== '' && campo.value !== '0' && campo.value !== '0.00') {
            preenchidos++;
        }
    });
    
    const progresso = Math.round((preenchidos / campos.length) * 100);
    const progressBar = document.getElementById('form-progress');
    if (progressBar) {
        progressBar.style.width = progresso + '%';
        
        // Muda cor conforme progresso
        progressBar.className = 'progress-bar';
        if (progresso >= 100) {
            progressBar.classList.add('bg-success');
        } else if (progresso >= 70) {
            progressBar.classList.add('bg-info');
        } else if (progresso >= 40) {
            progressBar.classList.add('bg-warning');
        } else {
            progressBar.classList.add('bg-danger');
        }
    }
}

// Event listeners para atualizar o progresso
document.addEventListener('DOMContentLoaded', function() {
    // Atualiza progresso inicial
    updateProgress();
    
    // Adiciona listeners aos campos existentes
    const campos = document.querySelectorAll('#projeto-form input, #projeto-form textarea, #projeto-form select');
    campos.forEach(campo => {
        campo.addEventListener('input', updateProgress);
        campo.addEventListener('change', updateProgress);
    });
    
    // Validação do formulário
    document.getElementById('projeto-form').addEventListener('submit', function(e) {
        const salvarBtn = document.getElementById('salvar-btn');
        
        if (!this.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
            
            // Mostra alerta de erro
            const alertHtml = `
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle"></i> Por favor, preencha todos os campos obrigatórios corretamente!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            document.querySelector('.admin-card-body').insertAdjacentHTML('afterbegin', alertHtml);
        } else {
            // Desabilita botão para evitar duplo envio
            salvarBtn.disabled = true;
            salvarBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Salvando...';
        }
        
        this.classList.add('was-validated');
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
