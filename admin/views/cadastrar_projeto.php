<?php require_once '../includes/header.php'; ?>

<?php
// Recuperar dados do formulário em caso de erro
$form_data = $_SESSION['form_data'] ?? [];
// Limpar dados após uso
if (isset($_SESSION['form_data'])) {
    unset($_SESSION['form_data']);
}

// Função helper para marcar option selecionada
function isSelected($value, $form_value) {
    return $value === $form_value ? 'selected' : '';
}

// Função helper para marcar checkbox/radio
function isChecked($value, $form_value) {
    return $value == $form_value ? 'checked' : '';
}
?>

<!-- Mensagens de feedback -->
<?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle"></i> <?= $_SESSION['error_message'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['error_message']); ?>
<?php endif; ?>

<div class="admin-card">
    <div class="admin-card-header">
        <h4 class="mb-0">
            <i class="bi bi-plus-circle"></i> Cadastrar Novo Projeto
        </h4>
        <small class="opacity-75">Preencha as informações do projeto</small>
    </div>
    
    <div class="admin-card-body">
        <!-- Progress Bar -->
        <div class="mb-4">
            <div class="progress" style="height: 8px;">
                <div class="progress-bar" role="progressbar" style="width: 0%" id="form-progress"></div>
            </div>
            <small class="text-muted">Progresso do formulário</small>
        </div>

        <form action="../backend/salvar_projeto.php" method="POST" enctype="multipart/form-data" id="projeto-form">
            
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
                               value="<?= htmlspecialchars($form_data['titulo'] ?? '') ?>"
                               placeholder="Ex: Casa Moderna em Condomínio Fechado">
                        <div class="invalid-feedback">Por favor, informe o título do projeto.</div>
                    </div>
                    
                    <div class="col-12">
                        <label class="form-label fw-bold">
                            <i class="bi bi-text-paragraph"></i> Descrição do Projeto *
                        </label>
                        <textarea name="descricao" class="form-control" rows="4" required
                                  placeholder="Descreva detalhadamente o projeto, suas características principais e diferenciais..."><?= htmlspecialchars($form_data['descricao'] ?? '') ?></textarea>
                        <div class="invalid-feedback">Por favor, descreva o projeto.</div>
                    </div>
                    
                    <div class="col-12">
                        <label class="form-label fw-bold">
                            <i class="bi bi-house-fill"></i> Tipo de Projeto *
                        </label>
                        <select name="tipo_projeto" class="form-select" required>
                            <option value="">Selecione o tipo de projeto...</option>
                            <optgroup label="Residencial Térrea">
                                <option value="Casa Térrea" <?= isSelected('Casa Térrea', $form_data['tipo_projeto'] ?? '') ?>>Casa Térrea</option>
                                <option value="Casa de Campo" <?= isSelected('Casa de Campo', $form_data['tipo_projeto'] ?? '') ?>>Casa de Campo</option>
                                <option value="Casa de Praia" <?= isSelected('Casa de Praia', $form_data['tipo_projeto'] ?? '') ?>>Casa de Praia</option>
                                <option value="Casa com Piscina" <?= isSelected('Casa com Piscina', $form_data['tipo_projeto'] ?? '') ?>>Casa com Piscina</option>
                                <option value="Casa Geminada" <?= isSelected('Casa Geminada', $form_data['tipo_projeto'] ?? '') ?>>Casa Geminada</option>
                                <option value="Chalé" <?= isSelected('Chalé', $form_data['tipo_projeto'] ?? '') ?>>Chalé</option>
                            </optgroup>
                            <optgroup label="Residencial Múltiplos Pavimentos">
                                <option value="Sobrado" <?= isSelected('Sobrado', $form_data['tipo_projeto'] ?? '') ?>>Sobrado</option>
                                <option value="Casa Duplex" <?= isSelected('Casa Duplex', $form_data['tipo_projeto'] ?? '') ?>>Casa Duplex</option>
                                <option value="Casa Triplex" <?= isSelected('Casa Triplex', $form_data['tipo_projeto'] ?? '') ?>>Casa Triplex</option>
                                <option value="Casa com Mezanino" <?= isSelected('Casa com Mezanino', $form_data['tipo_projeto'] ?? '') ?>>Casa com Mezanino</option>
                                <option value="Casa com Loft" <?= isSelected('Casa com Loft', $form_data['tipo_projeto'] ?? '') ?>>Casa com Loft</option>
                                <option value="Mansão" <?= isSelected('Mansão', $form_data['tipo_projeto'] ?? '') ?>>Mansão</option>
                            </optgroup>
                            <optgroup label="Estilos Arquitetônicos">
                                <option value="Residência Moderna" <?= isSelected('Residência Moderna', $form_data['tipo_projeto'] ?? '') ?>>Residência Moderna</option>
                                <option value="Residência Clássica" <?= isSelected('Residência Clássica', $form_data['tipo_projeto'] ?? '') ?>>Residência Clássica</option>
                                <option value="Residência Minimalista" <?= isSelected('Residência Minimalista', $form_data['tipo_projeto'] ?? '') ?>>Residência Minimalista</option>
                            </optgroup>
                            <optgroup label="Construção Especial">
                                <option value="Casa Container" <?= isSelected('Casa Container', $form_data['tipo_projeto'] ?? '') ?>>Casa Container</option>
                                <option value="Casa Sustentável" <?= isSelected('Casa Sustentável', $form_data['tipo_projeto'] ?? '') ?>>Casa Sustentável</option>
                                <option value="Casa Pré-Fabricada" <?= isSelected('Casa Pré-Fabricada', $form_data['tipo_projeto'] ?? '') ?>>Casa Pré-Fabricada</option>
                                <option value="Casa com Edícula" <?= isSelected('Casa com Edícula', $form_data['tipo_projeto'] ?? '') ?>>Casa com Edícula</option>
                            </optgroup>
                            <optgroup label="Apartamentos">
                                <option value="Kitnet" <?= isSelected('Kitnet', $form_data['tipo_projeto'] ?? '') ?>>Kitnet</option>
                                <option value="Studio" <?= isSelected('Studio', $form_data['tipo_projeto'] ?? '') ?>>Studio</option>
                                <option value="Apartamento" <?= isSelected('Apartamento', $form_data['tipo_projeto'] ?? '') ?>>Apartamento</option>
                                <option value="Cobertura" <?= isSelected('Cobertura', $form_data['tipo_projeto'] ?? '') ?>>Cobertura</option>
                            </optgroup>
                            <optgroup label="Predial">
                                <option value="Prédio Residencial" <?= isSelected('Prédio Residencial', $form_data['tipo_projeto'] ?? '') ?>>Prédio Residencial</option>
                                <option value="Prédio Comercial" <?= isSelected('Prédio Comercial', $form_data['tipo_projeto'] ?? '') ?>>Prédio Comercial</option>
                            </optgroup>
                            <optgroup label="Comercial">
                                <option value="Casa Comercial" <?= isSelected('Casa Comercial', $form_data['tipo_projeto'] ?? '') ?>>Casa Comercial</option>
                                <option value="Escritório" <?= isSelected('Escritório', $form_data['tipo_projeto'] ?? '') ?>>Escritório</option>
                                <option value="Loja" <?= isSelected('Loja', $form_data['tipo_projeto'] ?? '') ?>>Loja</option>
                                <option value="Consultório" <?= isSelected('Consultório', $form_data['tipo_projeto'] ?? '') ?>>Consultório</option>
                                <option value="Clínica" <?= isSelected('Clínica', $form_data['tipo_projeto'] ?? '') ?>>Clínica</option>
                                <option value="Restaurante" <?= isSelected('Restaurante', $form_data['tipo_projeto'] ?? '') ?>>Restaurante</option>
                                <option value="Cafeteria" <?= isSelected('Cafeteria', $form_data['tipo_projeto'] ?? '') ?>>Cafeteria</option>
                                <option value="Academia" <?= isSelected('Academia', $form_data['tipo_projeto'] ?? '') ?>>Academia</option>
                                <option value="Salão de Beleza" <?= isSelected('Salão de Beleza', $form_data['tipo_projeto'] ?? '') ?>>Salão de Beleza</option>
                            </optgroup>
                            <optgroup label="Industrial">
                                <option value="Galpão" <?= isSelected('Galpão', $form_data['tipo_projeto'] ?? '') ?>>Galpão</option>
                                <option value="Barracão" <?= isSelected('Barracão', $form_data['tipo_projeto'] ?? '') ?>>Barracão</option>
                                <option value="Oficina" <?= isSelected('Oficina', $form_data['tipo_projeto'] ?? '') ?>>Oficina</option>
                                <option value="Depósito" <?= isSelected('Depósito', $form_data['tipo_projeto'] ?? '') ?>>Depósito</option>
                                <option value="Armazém" <?= isSelected('Armazém', $form_data['tipo_projeto'] ?? '') ?>>Armazém</option>
                            </optgroup>
                            <option value="Outro" <?= isSelected('Outro', $form_data['tipo_projeto'] ?? '') ?>>Outro</option>
                        </select>
                        <div class="invalid-feedback">Por favor, selecione o tipo de projeto.</div>
                        <small class="text-muted">Escolha a categoria que melhor descreve o projeto</small>
                    </div>
                    
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="destaque" id="destaque" value="1"
                                   <?= isChecked('1', $form_data['destaque'] ?? '') ?>>
                            <label class="form-check-label fw-bold" for="destaque">
                                <i class="bi bi-star-fill text-warning"></i> Projeto em Destaque
                            </label>
                            <small class="text-muted d-block">Projetos em destaque aparecem na página inicial</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Seção 2: Área do Terreno -->
            <div class="form-section" data-section="2">
                <hr class="section-divider">
                <h5 class="section-title">
                    <i class="bi bi-rulers text-primary"></i> Dimensões e Áreas do Projeto
                </h5>
                
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-bold">
                            <i class="bi bi-arrow-left-right"></i> Largura do Terreno (m) *
                        </label>
                        <input type="number" step="0.01" name="largura_terreno" class="form-control" required 
                               value="<?= htmlspecialchars($form_data['largura_terreno'] ?? '') ?>"
                               placeholder="0.00" id="largura-terreno" onchange="calcularAreaTerreno()">
                        <div class="invalid-feedback">Informe a largura do terreno.</div>
                        <small class="text-muted">Largura do terreno em metros</small>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label fw-bold">
                            <i class="bi bi-arrow-up-down"></i> Comprimento do Terreno (m) *
                        </label>
                        <input type="number" step="0.01" name="comprimento_terreno" class="form-control" required 
                               value="<?= htmlspecialchars($form_data['comprimento_terreno'] ?? '') ?>"
                               placeholder="0.00" id="comprimento-terreno" onchange="calcularAreaTerreno()">
                        <div class="invalid-feedback">Informe o comprimento do terreno.</div>
                        <small class="text-muted">Comprimento do terreno em metros</small>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label fw-bold">
                            <i class="bi bi-badge-ad"></i> Área do Terreno (m²) *
                        </label>
                        <input type="number" step="0.01" name="area_terreno" class="form-control" required readonly
                               value="<?= htmlspecialchars($form_data['area_terreno'] ?? '') ?>"
                               placeholder="0.00" style="background-color: #f8f9fa;" id="area-terreno-display">
                        <div class="invalid-feedback">Área calculada automaticamente.</div>
                        <small class="text-muted">Calculado automaticamente</small>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label fw-bold">
                            <i class="bi bi-building"></i> Área Construída (m²)
                        </label>
                        <input type="number" step="0.01" name="area_construida" class="form-control" readonly 
                               value="<?= htmlspecialchars($form_data['area_construida'] ?? '') ?>"
                               placeholder="0.00" style="background-color: #f8f9fa;" id="area-construida-display">
                        <!-- Campo hidden para enviar o valor calculado -->
                        <input type="hidden" name="area_construida_calculated" id="area-construida-hidden"
                               value="<?= htmlspecialchars($form_data['area_construida'] ?? '') ?>">
                        <small class="text-muted">Soma automática dos andares</small>
                    </div>
                </div>
            </div>

            <!-- Seção 3: Valores -->
            <div class="form-section" data-section="3">
                <hr class="section-divider">
                <h5 class="section-title">
                    <i class="bi bi-currency-dollar text-primary"></i> Valores e Custos
                </h5>
                
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">
                            <i class="bi bi-cash-stack"></i> Valor do Projeto (R$)
                        </label>
                        <input type="number" step="0.01" name="valor_projeto" class="form-control" 
                               value="<?= htmlspecialchars($form_data['valor_projeto'] ?? '') ?>"
                               placeholder="0.00">
                        <small class="text-muted">Valor total do projeto</small>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label fw-bold">
                            <i class="bi bi-tools"></i> Mão de Obra (R$)
                        </label>
                        <input type="number" step="0.01" name="custo_mao_obra" class="form-control" 
                               value="<?= htmlspecialchars($form_data['custo_mao_obra'] ?? '') ?>"
                               placeholder="0.00">
                        <small class="text-muted">Custo da mão de obra</small>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label fw-bold">
                            <i class="bi bi-bricks"></i> Materiais (R$)
                        </label>
                        <input type="number" step="0.01" name="custo_materiais" class="form-control" 
                               value="<?= htmlspecialchars($form_data['custo_materiais'] ?? '') ?>"
                               placeholder="0.00">
                        <small class="text-muted">Custo dos materiais</small>
                    </div>
                </div>
            </div>

            <!-- Seção 4: Mídia -->
            <div class="form-section" data-section="4">
                <hr class="section-divider">
                <h5 class="section-title">
                    <i class="bi bi-camera text-primary"></i> Mídia e Documentação
                </h5>
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">
                            <i class="bi bi-youtube"></i> Vídeo do Projeto
                        </label>
                        <input type="url" name="video_url" class="form-control" 
                               value="<?= htmlspecialchars($form_data['video_url'] ?? '') ?>"
                               placeholder="https://www.youtube.com/watch?v=...">
                        <small class="text-muted">URL do YouTube (opcional)</small>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label fw-bold">
                            <i class="bi bi-image"></i> Imagem de Capa *
                        </label>
                        <input type="file" name="capa_imagem" accept="image/*" class="form-control" required
                               onchange="previewImagem(this)">
                        <div class="invalid-feedback">Selecione uma imagem de capa.</div>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label fw-bold">
                            <i class="bi bi-file-zip"></i> Arquivo do Projeto (ZIP)
                        </label>
                        <input type="file" name="arquivo_projeto" accept=".zip" class="form-control"
                               onchange="previewArquivo(this)">
                        <small class="text-muted">Arquivo ZIP com plantas, documentos, etc. (opcional)</small>
                        <div id="arquivo-info" class="mt-2" style="display: none;">
                            <div class="alert alert-info d-flex align-items-center">
                                <i class="bi bi-file-zip me-2"></i>
                                <span id="arquivo-nome"></span>
                                <span id="arquivo-tamanho" class="ms-auto badge bg-secondary"></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-12">
                        <div class="text-center mt-3">
                            <img id="preview-imagem" src="" alt="Preview" 
                                 style="max-width: 300px; max-height: 200px; display: none; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Seção 5: Andares e Cômodos -->
            <div class="form-section" data-section="5">
                <hr class="section-divider">
                <h5 class="section-title">
                    <i class="bi bi-building text-primary"></i> Andares do Projeto
                </h5>
                
                <div id="andares-container">
                    <!-- Andares serão adicionados dinamicamente aqui -->
                </div>
                
                <div class="mt-3">
                    <button type="button" class="btn btn-outline-primary" onclick="adicionarAndar()">
                        <i class="bi bi-plus-circle"></i> Adicionar Andar
                    </button>
                </div>
            </div>

            <!-- Botões de Ação -->
            <hr class="section-divider">
            <div class="d-flex gap-3 justify-content-between">
                <div class="d-flex gap-2">
                    <a href="../backend/limpar_formulario.php?redirect=../dashboard.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Cancelar
                    </a>
                    <a href="../backend/limpar_formulario.php?redirect=../views/cadastrar_projeto.php" class="btn btn-outline-warning">
                        <i class="bi bi-eraser"></i> Limpar
                    </a>
                    <button type="button" class="btn btn-outline-info" onclick="limparFormularioRapido()">
                        <i class="bi bi-arrow-clockwise"></i> Reset
                    </button>
                </div>
                <button type="submit" class="btn btn-primary" id="salvar-btn">
                    <i class="bi bi-check-circle"></i> Salvar Projeto
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let andaresCount = 0;
let comodosCount = 0;

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
    const areaHidden = document.getElementById('area-construida-hidden');
    
    if (areaDisplay) {
        areaDisplay.value = totalArea.toFixed(2);
    }
    
    if (areaHidden) {
        areaHidden.value = totalArea.toFixed(2);
    }
}

function calcularAreaTerreno() {
    const largura = parseFloat(document.getElementById('largura-terreno').value) || 0;
    const comprimento = parseFloat(document.getElementById('comprimento-terreno').value) || 0;
    const area = largura * comprimento;
    const areaDisplay = document.getElementById('area-terreno-display');
    
    if (areaDisplay) {
        areaDisplay.value = area.toFixed(2);
    }
    
    updateProgress();
}

function previewImagem(input) {
    const preview = document.getElementById('preview-imagem');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        }
        
        reader.readAsDataURL(input.files[0]);
    }
}

function previewArquivo(input) {
    const info = document.getElementById('arquivo-info');
    const nome = document.getElementById('arquivo-nome');
    const tamanho = document.getElementById('arquivo-tamanho');
    
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const size = (file.size / 1024 / 1024).toFixed(2); // MB
        
        nome.textContent = file.name;
        tamanho.textContent = size + ' MB';
        info.style.display = 'block';
    } else {
        info.style.display = 'none';
    }
}

function validarFormulario() {
    const form = document.getElementById('projeto-form');
    const requiredFields = form.querySelectorAll('input[required], textarea[required], select[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (field.value.trim() === '') {
            field.classList.add('is-invalid');
            isValid = false;
        } else {
            field.classList.remove('is-invalid');
        }
    });
    
    // Verificar se há pelo menos um andar
    const andares = document.querySelectorAll('.andar-item');
    if (andares.length === 0) {
        alert('Adicione pelo menos um andar ao projeto!');
        return false;
    }
    
    // Verificar se cada andar tem pelo menos um cômodo
    let andarSemComodo = false;
    andares.forEach((andar, index) => {
        const comodos = andar.querySelectorAll('.comodo-item');
        if (comodos.length === 0) {
            alert(`O andar ${index + 1} deve ter pelo menos um cômodo!`);
            andarSemComodo = true;
        }
    });
    
    return isValid && !andarSemComodo;
}

// Adicionar primeiro andar automaticamente
document.addEventListener('DOMContentLoaded', function() {
    adicionarAndar();
    updateProgress();
    calcularAreaConstruida();
    
    // Monitor de progresso
    const form = document.getElementById('projeto-form');
    const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
    
    inputs.forEach(input => {
        input.addEventListener('input', updateProgress);
        input.addEventListener('change', updateProgress);
    });
});

function updateProgress() {
    const form = document.getElementById('projeto-form');
    const requiredFields = form.querySelectorAll('input[required], textarea[required], select[required]');
    let filledFields = 0;
    
    requiredFields.forEach(field => {
        if (field.value.trim() !== '') {
            filledFields++;
        }
    });
    
    const progress = (filledFields / requiredFields.length) * 100;
    document.getElementById('form-progress').style.width = progress + '%';
}

// Função para limpar formulário rapidamente (sem recarregar página)
function limparFormularioRapido() {
    if (confirm('Tem certeza que deseja limpar todos os campos do formulário?')) {
        const form = document.getElementById('projeto-form');
        
        // Limpa todos os inputs, textareas e selects
        form.querySelectorAll('input, textarea, select').forEach(field => {
            if (field.type === 'checkbox' || field.type === 'radio') {
                field.checked = false;
            } else if (field.type === 'file') {
                field.value = '';
            } else {
                field.value = '';
            }
        });
        
        // Limpa preview de imagem
        const preview = document.getElementById('preview-imagem');
        if (preview) {
            preview.style.display = 'none';
            preview.src = '';
        }
        
        // Limpa info do arquivo
        const arquivoInfo = document.getElementById('arquivo-info');
        if (arquivoInfo) {
            arquivoInfo.style.display = 'none';
        }
        
        // Limpa andares dinâmicos
        const andaresContainer = document.getElementById('andares-container');
        andaresContainer.innerHTML = '';
        andaresCount = 0;
        
        // Atualiza progresso
        updateProgress();
        
        // Remove classe de validação
        form.classList.remove('was-validated');
        
        // Mostra notificação de sucesso
        const alertHtml = `
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i> Formulário limpo com sucesso!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        document.querySelector('.admin-card-body').insertAdjacentHTML('afterbegin', alertHtml);
        
        // Remove o alerta automaticamente após 3 segundos
        setTimeout(() => {
            const alert = document.querySelector('.alert-success');
            if (alert) {
                alert.remove();
            }
        }, 3000);
    }
}

// Validação antes do envio
document.getElementById('projeto-form').addEventListener('submit', function(e) {
    if (!validarFormulario()) {
        e.preventDefault();
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>
