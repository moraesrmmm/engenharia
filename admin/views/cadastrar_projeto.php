<?php require_once '../includes/header.php'; ?>

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
                               placeholder="Ex: Casa Moderna em Condomínio Fechado">
                        <div class="invalid-feedback">Por favor, informe o título do projeto.</div>
                    </div>
                    
                    <div class="col-12">
                        <label class="form-label fw-bold">
                            <i class="bi bi-text-paragraph"></i> Descrição do Projeto *
                        </label>
                        <textarea name="descricao" class="form-control" rows="4" required
                                  placeholder="Descreva detalhadamente o projeto, suas características principais e diferenciais..."></textarea>
                        <div class="invalid-feedback">Por favor, descreva o projeto.</div>
                    </div>
                    
                    <div class="col-12">
                        <label class="form-label fw-bold">
                            <i class="bi bi-house-fill"></i> Tipo de Projeto *
                        </label>
                        <select name="tipo_projeto" class="form-select" required>
                            <option value="">Selecione o tipo de projeto...</option>
                            <optgroup label="Residencial Térrea">
                                <option value="Casa Térrea">Casa Térrea</option>
                                <option value="Casa de Campo">Casa de Campo</option>
                                <option value="Casa de Praia">Casa de Praia</option>
                                <option value="Casa com Piscina">Casa com Piscina</option>
                                <option value="Casa Geminada">Casa Geminada</option>
                                <option value="Chalé">Chalé</option>
                            </optgroup>
                            <optgroup label="Residencial Múltiplos Pavimentos">
                                <option value="Sobrado">Sobrado</option>
                                <option value="Casa Duplex">Casa Duplex</option>
                                <option value="Casa Triplex">Casa Triplex</option>
                                <option value="Casa com Mezanino">Casa com Mezanino</option>
                                <option value="Casa com Loft">Casa com Loft</option>
                                <option value="Mansão">Mansão</option>
                            </optgroup>
                            <optgroup label="Estilos Arquitetônicos">
                                <option value="Residência Moderna">Residência Moderna</option>
                                <option value="Residência Clássica">Residência Clássica</option>
                                <option value="Residência Minimalista">Residência Minimalista</option>
                            </optgroup>
                            <optgroup label="Construção Especial">
                                <option value="Casa Container">Casa Container</option>
                                <option value="Casa Sustentável">Casa Sustentável</option>
                                <option value="Casa Pré-Fabricada">Casa Pré-Fabricada</option>
                                <option value="Casa com Edícula">Casa com Edícula</option>
                            </optgroup>
                            <optgroup label="Apartamentos">
                                <option value="Kitnet">Kitnet</option>
                                <option value="Studio">Studio</option>
                                <option value="Apartamento">Apartamento</option>
                                <option value="Cobertura">Cobertura</option>
                            </optgroup>
                            <optgroup label="Predial">
                                <option value="Prédio Residencial">Prédio Residencial</option>
                                <option value="Prédio Comercial">Prédio Comercial</option>
                            </optgroup>
                            <optgroup label="Comercial">
                                <option value="Casa Comercial">Casa Comercial</option>
                                <option value="Escritório">Escritório</option>
                                <option value="Loja">Loja</option>
                                <option value="Consultório">Consultório</option>
                                <option value="Clínica">Clínica</option>
                                <option value="Restaurante">Restaurante</option>
                                <option value="Cafeteria">Cafeteria</option>
                                <option value="Academia">Academia</option>
                                <option value="Salão de Beleza">Salão de Beleza</option>
                            </optgroup>
                            <optgroup label="Industrial">
                                <option value="Galpão">Galpão</option>
                                <option value="Barracão">Barracão</option>
                                <option value="Oficina">Oficina</option>
                                <option value="Depósito">Depósito</option>
                                <option value="Armazém">Armazém</option>
                            </optgroup>
                            <option value="Outro">Outro</option>
                        </select>
                        <div class="invalid-feedback">Por favor, selecione o tipo de projeto.</div>
                        <small class="text-muted">Escolha a categoria que melhor descreve o projeto</small>
                    </div>
                    
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="destaque" id="destaque" value="1">
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
                               placeholder="0.00" id="largura-terreno" onchange="calcularAreaTerreno()">
                        <div class="invalid-feedback">Informe a largura do terreno.</div>
                        <small class="text-muted">Largura do terreno em metros</small>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label fw-bold">
                            <i class="bi bi-arrow-up-down"></i> Comprimento do Terreno (m) *
                        </label>
                        <input type="number" step="0.01" name="comprimento_terreno" class="form-control" required 
                               placeholder="0.00" id="comprimento-terreno" onchange="calcularAreaTerreno()">
                        <div class="invalid-feedback">Informe o comprimento do terreno.</div>
                        <small class="text-muted">Comprimento do terreno em metros</small>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label fw-bold">
                            <i class="bi bi-badge-ad"></i> Área do Terreno (m²) *
                        </label>
                        <input type="number" step="0.01" name="area_terreno" class="form-control" required readonly
                               placeholder="0.00" style="background-color: #f8f9fa;" id="area-terreno-display">
                        <div class="invalid-feedback">Área calculada automaticamente.</div>
                        <small class="text-muted">Calculado automaticamente</small>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label fw-bold">
                            <i class="bi bi-building"></i> Área Construída (m²)
                        </label>
                        <input type="number" step="0.01" name="area_construida" class="form-control" readonly 
                               placeholder="0.00" style="background-color: #f8f9fa;" id="area-construida-display">
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
                               placeholder="0.00">
                        <small class="text-muted">Valor total do projeto</small>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label fw-bold">
                            <i class="bi bi-tools"></i> Mão de Obra (R$)
                        </label>
                        <input type="number" step="0.01" name="custo_mao_obra" class="form-control" 
                               placeholder="0.00">
                        <small class="text-muted">Custo da mão de obra</small>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label fw-bold">
                            <i class="bi bi-bricks"></i> Materiais (R$)
                        </label>
                        <input type="number" step="0.01" name="custo_materiais" class="form-control" 
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
            <div class="d-flex gap-3 justify-content-end">
                <a href="../dashboard.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Cancelar
                </a>
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
    if (areaDisplay) {
        areaDisplay.value = totalArea.toFixed(2);
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

// Validação antes do envio
document.getElementById('projeto-form').addEventListener('submit', function(e) {
    if (!validarFormulario()) {
        e.preventDefault();
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>
