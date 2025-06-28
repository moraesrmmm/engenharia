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

            <!-- Seção 2: Dimensões -->
            <div class="form-section" data-section="2">
                <hr class="section-divider">
                <h5 class="section-title">
                    <i class="bi bi-rulers text-primary"></i> Dimensões e Área
                </h5>
                
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">
                            <i class="bi bi-arrow-left-right"></i> Largura (m) *
                        </label>
                        <input type="number" step="0.01" name="largura" class="form-control" required 
                               placeholder="0.00" onchange="calcularArea()">
                        <div class="invalid-feedback">Informe a largura em metros.</div>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label fw-bold">
                            <i class="bi bi-arrow-up-down"></i> Comprimento (m) *
                        </label>
                        <input type="number" step="0.01" name="comprimento" class="form-control" required 
                               placeholder="0.00" onchange="calcularArea()">
                        <div class="invalid-feedback">Informe o comprimento em metros.</div>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label fw-bold">
                            <i class="bi bi-badge-ad"></i> Área Total (m²) *
                        </label>
                        <input type="number" step="0.01" name="area" class="form-control" required 
                               placeholder="0.00" readonly style="background-color: #f8f9fa;">
                        <small class="text-muted">Calculado automaticamente</small>
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
                            <i class="bi bi-cash-stack"></i> Preço Total (R$)
                        </label>
                        <input type="number" step="0.01" name="preco_total" class="form-control" 
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

            <!-- Seção 5: Cômodos -->
            <div class="form-section" data-section="5">
                <hr class="section-divider">
                <h5 class="section-title">
                    <i class="bi bi-house-door text-primary"></i> Cômodos do Projeto
                </h5>
                
                <div id="comodos-container"></div>
                
                <button type="button" class="btn btn-outline-primary" onclick="adicionarComodo()">
                    <i class="bi bi-plus-circle"></i> Adicionar Cômodo
                </button>
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
let comodosCount = 0;

function adicionarComodo() {
    const container = document.getElementById('comodos-container');
    const index = comodosCount++;

    const div = document.createElement('div');
    div.className = 'comodo-item';
    div.innerHTML = `
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0"><i class="bi bi-door-open text-primary"></i> Cômodo ${index + 1}</h6>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.comodo-item').remove()">
                <i class="bi bi-trash"></i>
            </button>
        </div>
        
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label fw-bold">Tipo:</label>
                <select name="tipo[]" class="form-select" required>
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
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">Nome:</label>
                <input type="text" name="nome[]" class="form-control" placeholder="Ex: Quarto Casal">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-bold">Largura:</label>
                <input type="number" step="0.01" name="largura_comodo[]" class="form-control" placeholder="0.00">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-bold">Comprimento:</label>
                <input type="number" step="0.01" name="comprimento_comodo[]" class="form-control" placeholder="0.00">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-bold">Observações:</label>
                <input type="text" name="observacoes[]" class="form-control" placeholder="Detalhes...">
            </div>
        </div>
    `;
    container.appendChild(div);
    updateProgress();
}

// Adicionar primeiro cômodo automaticamente
document.addEventListener('DOMContentLoaded', function() {
    adicionarComodo();
    updateProgress();
    
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
        alert('Por favor, preencha todos os campos obrigatórios!');
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>

  <!-- Seção de cômodos -->
  <hr class="my-4">
  <h4>Cômodos do Projeto</h4>

  <div id="comodos-container"></div>

  <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="adicionarComodo()">+ Adicionar Cômodo</button>

  <hr class="my-4">

  <button type="submit" class="btn btn-success">Salvar Projeto</button>
</form>

<script>
function adicionarComodo() {
  const container = document.getElementById('comodos-container');
  const index = container.children.length;

  const div = document.createElement('div');
  div.className = 'row g-2 align-items-end mt-3';
  div.innerHTML = `
    <div class="col-md-2">
      <label>Tipo:</label>
      <select name="tipo[]" class="form-select" required>
        <option value="">--</option>
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
      </select>
    </div>
    <div class="col-md-2">
      <label>Nome:</label>
      <input type="text" name="nome[]" class="form-control">
    </div>
    <div class="col-md-2">
      <label>Largura:</label>
      <input type="number" step="0.01" name="largura_comodo[]" class="form-control">
    </div>
    <div class="col-md-2">
      <label>Comprimento:</label>
      <input type="number" step="0.01" name="comprimento_comodo[]" class="form-control">
    </div>
    <div class="col-md-3">
      <label>Observações:</label>
      <input type="text" name="observacoes[]" class="form-control">
    </div>
    <div class="col-md-1 text-end">
      <button type="button" class="btn btn-sm btn-danger" onclick="this.closest('.row').remove()">x</button>
    </div>
  `;
  container.appendChild(div);
}
</script>

<?php require_once './../includes/footer.php'; ?>
