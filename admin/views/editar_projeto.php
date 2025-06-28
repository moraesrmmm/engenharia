<?php 
session_start();
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
    
    // Busca os cômodos do projeto
    $stmt_comodos = $pdo->prepare("SELECT * FROM comodos WHERE projeto_id = ? AND ativo = TRUE ORDER BY id");
    $stmt_comodos->execute([$projeto_id]);
    $comodos = $stmt_comodos->fetchAll(PDO::FETCH_ASSOC);
    
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
                               value="<?= $projeto['largura'] ?>"
                               placeholder="0.00" onchange="calcularArea()">
                        <div class="invalid-feedback">Informe a largura em metros.</div>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label fw-bold">
                            <i class="bi bi-arrow-up-down"></i> Comprimento (m) *
                        </label>
                        <input type="number" step="0.01" name="comprimento" class="form-control" required 
                               value="<?= $projeto['comprimento'] ?>"
                               placeholder="0.00" onchange="calcularArea()">
                        <div class="invalid-feedback">Informe o comprimento em metros.</div>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label fw-bold">
                            <i class="bi bi-badge-ad"></i> Área Total (m²) *
                        </label>
                        <input type="number" step="0.01" name="area" class="form-control" required 
                               value="<?= $projeto['area'] ?>"
                               placeholder="0.00" readonly style="background-color: #f8f9fa;">
                        <small class="text-muted">Calculado automaticamente</small>
                        <div class="invalid-feedback">A área deve ser calculada.</div>
                    </div>
                </div>
            </div>

            <!-- Seção 3: Custos -->
            <div class="form-section" data-section="3">
                <hr class="section-divider">
                <h5 class="section-title">
                    <i class="bi bi-currency-dollar text-primary"></i> Custos do Projeto
                </h5>
                
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">
                            <i class="bi bi-currency-dollar"></i> Preço Total (R$) *
                        </label>
                        <input type="number" step="0.01" name="preco_total" class="form-control" required 
                               value="<?= $projeto['preco_total'] ?>"
                               placeholder="0.00">
                        <div class="invalid-feedback">Informe o preço total do projeto.</div>
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

            <!-- Seção 4: Mídia -->
            <div class="form-section" data-section="4">
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

            <!-- Seção 5: Cômodos -->
            <div class="form-section" data-section="5">
                <hr class="section-divider">
                <h5 class="section-title">
                    <i class="bi bi-house-door text-primary"></i> Cômodos do Projeto
                </h5>
                
                <div id="comodos-container">
                    <?php foreach ($comodos as $index => $comodo): ?>
                        <div class="comodo-item border rounded p-3 mb-3" style="background-color: #f8f9fa;">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0"><i class="bi bi-door-open"></i> Cômodo <?= $index + 1 ?></h6>
                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="removerComodo(this)">
                                    <i class="bi bi-trash"></i> Remover
                                </button>
                            </div>
                            
                            <input type="hidden" name="comodos[<?= $index ?>][id]" value="<?= $comodo['id'] ?>">
                            
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Tipo</label>
                                    <select name="comodos[<?= $index ?>][tipo]" class="form-select" required>
                                        <option value="">Selecione...</option>
                                        <option value="Quarto" <?= $comodo['tipo'] == 'Quarto' ? 'selected' : '' ?>>Quarto</option>
                                        <option value="Sala" <?= $comodo['tipo'] == 'Sala' ? 'selected' : '' ?>>Sala</option>
                                        <option value="Cozinha" <?= $comodo['tipo'] == 'Cozinha' ? 'selected' : '' ?>>Cozinha</option>
                                        <option value="Banheiro" <?= $comodo['tipo'] == 'Banheiro' ? 'selected' : '' ?>>Banheiro</option>
                                        <option value="Garagem" <?= $comodo['tipo'] == 'Garagem' ? 'selected' : '' ?>>Garagem</option>
                                        <option value="Área Gourmet" <?= $comodo['tipo'] == 'Área Gourmet' ? 'selected' : '' ?>>Área Gourmet</option>
                                        <option value="Área de Serviço" <?= $comodo['tipo'] == 'Área de Serviço' ? 'selected' : '' ?>>Área de Serviço</option>
                                        <option value="Escritório" <?= $comodo['tipo'] == 'Escritório' ? 'selected' : '' ?>>Escritório</option>
                                        <option value="Varanda" <?= $comodo['tipo'] == 'Varanda' ? 'selected' : '' ?>>Varanda</option>
                                        <option value="Closet" <?= $comodo['tipo'] == 'Closet' ? 'selected' : '' ?>>Closet</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Nome</label>
                                    <input type="text" name="comodos[<?= $index ?>][nome]" class="form-control" 
                                           value="<?= htmlspecialchars($comodo['nome']) ?>"
                                           placeholder="Ex: Quarto Casal">
                                </div>
                                
                                <div class="col-md-2">
                                    <label class="form-label fw-bold">Largura (m)</label>
                                    <input type="number" step="0.01" name="comodos[<?= $index ?>][largura]" class="form-control" 
                                           value="<?= $comodo['largura'] ?>"
                                           placeholder="0.00">
                                </div>
                                
                                <div class="col-md-2">
                                    <label class="form-label fw-bold">Comprimento (m)</label>
                                    <input type="number" step="0.01" name="comodos[<?= $index ?>][comprimento]" class="form-control" 
                                           value="<?= $comodo['comprimento'] ?>"
                                           placeholder="0.00">
                                </div>
                                
                                <div class="col-md-2">
                                    <label class="form-label fw-bold">Área (m²)</label>
                                    <input type="number" step="0.01" name="comodos[<?= $index ?>][area]" class="form-control" 
                                           value="<?= number_format($comodo['largura'] * $comodo['comprimento'], 2, '.', '') ?>"
                                           readonly style="background-color: #e9ecef;">
                                </div>
                                
                                <div class="col-12">
                                    <label class="form-label fw-bold">Observações</label>
                                    <textarea name="comodos[<?= $index ?>][observacoes]" class="form-control" rows="2" 
                                              placeholder="Observações sobre o cômodo..."><?= htmlspecialchars($comodo['observacoes']) ?></textarea>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <button type="button" class="btn btn-outline-primary" onclick="adicionarComodo()">
                    <i class="bi bi-plus-circle"></i> Adicionar Cômodo
                </button>
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
let comodoIndex = <?= count($comodos) ?>;

// Função para calcular área automaticamente
function calcularArea() {
    const largura = parseFloat(document.querySelector('input[name="largura"]').value) || 0;
    const comprimento = parseFloat(document.querySelector('input[name="comprimento"]').value) || 0;
    const area = largura * comprimento;
    document.querySelector('input[name="area"]').value = area.toFixed(2);
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

// Função para adicionar novo cômodo
function adicionarComodo() {
    const container = document.getElementById('comodos-container');
    
    const comodoHtml = `
        <div class="comodo-item border rounded p-3 mb-3" style="background-color: #f8f9fa;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0"><i class="bi bi-door-open"></i> Cômodo ${comodoIndex + 1}</h6>
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="removerComodo(this)">
                    <i class="bi bi-trash"></i> Remover
                </button>
            </div>
            
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-bold">Tipo</label>
                    <select name="comodos[${comodoIndex}][tipo]" class="form-select" required>
                        <option value="">Selecione...</option>
                        <option value="Quarto">Quarto</option>
                        <option value="Sala">Sala</option>
                        <option value="Cozinha">Cozinha</option>
                        <option value="Banheiro">Banheiro</option>
                        <option value="Garagem">Garagem</option>
                        <option value="Área Gourmet">Área Gourmet</option>
                        <option value="Área de Serviço">Área de Serviço</option>
                        <option value="Escritório">Escritório</option>
                        <option value="Varanda">Varanda</option>
                        <option value="Closet">Closet</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label fw-bold">Nome</label>
                    <input type="text" name="comodos[${comodoIndex}][nome]" class="form-control" 
                           placeholder="Ex: Quarto Casal">
                </div>
                
                <div class="col-md-2">
                    <label class="form-label fw-bold">Largura (m)</label>
                    <input type="number" step="0.01" name="comodos[${comodoIndex}][largura]" class="form-control" 
                           placeholder="0.00" onchange="calcularAreaComodo(this)">
                </div>
                
                <div class="col-md-2">
                    <label class="form-label fw-bold">Comprimento (m)</label>
                    <input type="number" step="0.01" name="comodos[${comodoIndex}][comprimento]" class="form-control" 
                           placeholder="0.00" onchange="calcularAreaComodo(this)">
                </div>
                
                <div class="col-md-2">
                    <label class="form-label fw-bold">Área (m²)</label>
                    <input type="number" step="0.01" name="comodos[${comodoIndex}][area]" class="form-control" 
                           readonly style="background-color: #e9ecef;">
                </div>
                
                <div class="col-12">
                    <label class="form-label fw-bold">Observações</label>
                    <textarea name="comodos[${comodoIndex}][observacoes]" class="form-control" rows="2" 
                              placeholder="Observações sobre o cômodo..."></textarea>
                </div>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', comodoHtml);
    comodoIndex++;
    updateProgress();
    
    // Adiciona event listeners aos novos campos
    const novoComodo = container.lastElementChild;
    const novosInputs = novoComodo.querySelectorAll('input, textarea, select');
    novosInputs.forEach(input => {
        input.addEventListener('input', updateProgress);
        input.addEventListener('change', updateProgress);
    });
}

// Função para remover cômodo
function removerComodo(button) {
    if (confirm('Tem certeza que deseja remover este cômodo?')) {
        button.closest('.comodo-item').remove();
        updateProgress();
        
        // Renumera os cômodos
        const comodos = document.querySelectorAll('.comodo-item');
        comodos.forEach((comodo, index) => {
            comodo.querySelector('h6').innerHTML = `<i class="bi bi-door-open"></i> Cômodo ${index + 1}`;
        });
    }
}

// Função para calcular área do cômodo
function calcularAreaComodo(input) {
    const comodoItem = input.closest('.comodo-item');
    const largura = parseFloat(comodoItem.querySelector('input[name*="[largura]"]').value) || 0;
    const comprimento = parseFloat(comodoItem.querySelector('input[name*="[comprimento]"]').value) || 0;
    const area = largura * comprimento;
    comodoItem.querySelector('input[name*="[area]"]').value = area.toFixed(2);
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
    
    // Inicializa áreas dos cômodos existentes
    document.querySelectorAll('.comodo-item').forEach(comodo => {
        calcularAreaComodo(comodo.querySelector('input[name*="[largura]"]'));
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
