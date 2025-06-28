<?php
require_once '../config/config.php';
require_once '../includes/funcoes.php';
require_once '../includes/header.php';

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

// Buscar andares e cômodos
$stmt_andares = $pdo->prepare("SELECT * FROM andares WHERE projeto_id = ? AND ativo = TRUE ORDER BY ordem, nome");
$stmt_andares->execute([$id]);
$andares = $stmt_andares->fetchAll();

// Buscar cômodos agrupados por andar
$comodos_por_andar = [];
if (!empty($andares)) {
    foreach ($andares as $andar) {
        $stmt_comodos = $pdo->prepare("SELECT * FROM comodos WHERE andar_id = ? AND ativo = TRUE ORDER BY tipo, nome");
        $stmt_comodos->execute([$andar['id']]);
        $comodos_por_andar[$andar['id']] = $stmt_comodos->fetchAll();
    }
}

// Buscar cômodos sem andar (compatibilidade com dados antigos)
$comodos = $pdo->prepare("SELECT * FROM comodos WHERE projeto_id = ? AND (andar_id IS NULL OR andar_id = 0) AND ativo = TRUE ORDER BY tipo, nome");
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
                        <?php if ($projeto['largura_terreno'] && $projeto['comprimento_terreno']): ?>
                            <?= number_format($projeto['largura_terreno'], 1, ',', '.') ?>m × 
                            <?= number_format($projeto['comprimento_terreno'], 1, ',', '.') ?>m • 
                        <?php endif; ?>
                        <?= formatarArea($projeto['area_terreno']) ?>
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
                        <!-- Tipo de Projeto -->
                        <?php if (!empty($projeto['tipo_projeto'])): ?>
                            <div class="projeto-info-card">
                                <div class="info-icon">
                                    <i class="bi bi-house-fill"></i>
                                </div>
                                <div class="info-title">Tipo de Projeto</div>
                                <div class="info-value"><?= htmlspecialchars($projeto['tipo_projeto']) ?></div>
                            </div>
                        <?php endif; ?>

                        <!-- Dimensões do Terreno -->
                        <div class="projeto-info-card">
                            <div class="info-icon">
                                <i class="bi bi-rulers"></i>
                            </div>
                            <div class="info-title">Dimensões do Terreno</div>
                            <div class="info-value">
                                <?php if ($projeto['largura_terreno'] && $projeto['comprimento_terreno']): ?>
                                    <?= number_format($projeto['largura_terreno'], 1, ',', '.') ?>m × 
                                    <?= number_format($projeto['comprimento_terreno'], 1, ',', '.') ?>m
                                <?php else: ?>
                                    Não informado
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Área do Terreno -->
                        <div class="projeto-info-card">
                            <div class="info-icon">
                                <i class="bi bi-bounding-box"></i>
                            </div>
                            <div class="info-title">Área do Terreno</div>
                            <div class="info-value"><?= formatarArea($projeto['area_terreno']) ?></div>
                        </div>

                        <!-- Área Construída -->
                        <?php if ($projeto['area_construida']): ?>
                            <div class="projeto-info-card">
                                <div class="info-icon">
                                    <i class="bi bi-building"></i>
                                </div>
                                <div class="info-title">Área Construída</div>
                                <div class="info-value"><?= formatarArea($projeto['area_construida']) ?></div>
                            </div>
                        <?php endif; ?>

                        <!-- Preço Total -->
                        <?php if ($projeto['valor_projeto']): ?>
                            <div class="projeto-info-card precos-card">
                                <div class="info-icon">
                                    <i class="bi bi-currency-dollar"></i>
                                </div>
                                <div class="info-title">Investimento</div>
                                <div class="info-value"><?= formatarMoeda($projeto['valor_projeto']) ?></div>
                                
                                <!-- Botão de compra estratégico -->
                                <?php if (!empty($projeto['arquivo_projeto'])): ?>
                                    <div class="mt-3">
                                        <button class="btn btn-success btn-sm w-100" onclick="iniciarCompra(<?= $projeto['id'] ?>)">
                                            <i class="bi bi-download"></i> Comprar Projeto
                                        </button>
                                        <small class="text-muted d-block mt-1">Receba plantas e documentos</small>
                                    </div>
                                <?php endif; ?>
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
                            <div class="info-value">
                                <?php 
                                // Contar todos os cômodos (dos andares + cômodos antigos)
                                $total_comodos = count($comodosData);
                                foreach ($comodos_por_andar as $comodos_andar) {
                                    $total_comodos += count($comodos_andar);
                                }
                                echo $total_comodos;
                                ?> ambientes
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Seção de Andares -->
            <?php if (count($andares) > 0): ?>
                <div class="andares-section mb-5">
                    <h2 class="section-title">Andares do Projeto</h2>
                    <div class="andares-grid">
                        <?php foreach ($andares as $andar): ?>
                            <div class="andar-card mb-4">
                                <div class="andar-header">
                                    <div class="andar-info">
                                        <h4 class="andar-nome">
                                            <i class="bi bi-layers-fill text-primary"></i>
                                            <?= htmlspecialchars($andar['nome']) ?>
                                        </h4>
                                        <div class="andar-area">
                                            <i class="bi bi-rulers"></i>
                                            Área: <?= number_format($andar['area'], 2, ',', '.') ?> m²
                                        </div>
                                    </div>
                                </div>
                                
                                <?php if (!empty($andar['observacoes'])): ?>
                                    <div class="andar-observacoes">
                                        <i class="bi bi-info-circle text-info"></i>
                                        <?= nl2br(htmlspecialchars($andar['observacoes'])) ?>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Cômodos deste andar -->
                                <?php if (isset($comodos_por_andar[$andar['id']]) && count($comodos_por_andar[$andar['id']]) > 0): ?>
                                    <div class="comodos-andar">
                                        <h5 class="comodos-andar-title">
                                            <i class="bi bi-door-open"></i>
                                            Ambientes deste andar (<?= count($comodos_por_andar[$andar['id']]) ?>)
                                        </h5>
                                        <div class="comodos-grid-mini">
                                            <?php foreach ($comodos_por_andar[$andar['id']] as $comodo): ?>
                                                <div class="comodo-mini-card">
                                                    <div class="comodo-mini-content">
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
                                                            <span><?= htmlspecialchars($comodo['tipo']) ?></span>
                                                        </div>
                                                        
                                                        <?php if ($comodo['nome'] !== $comodo['tipo']): ?>
                                                            <div class="comodo-nome"><?= htmlspecialchars($comodo['nome']) ?></div>
                                                        <?php endif; ?>
                                                        
                                                        <?php if (!empty($comodo['observacoes'])): ?>
                                                            <div class="comodo-obs">
                                                                <i class="bi bi-info-circle"></i>
                                                                <?= htmlspecialchars($comodo['observacoes']) ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Seção de Cômodos (compatibilidade com dados antigos) -->
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
                <?php if (!empty($projeto['arquivo_projeto'])): ?>
                    <div class="row g-3 justify-content-center">
                        <div class="col-lg-4">
                            <button class="btn btn-success btn-lg w-100" onclick="iniciarCompra(<?= $projeto['id'] ?>)">
                                <i class="bi bi-download"></i>
                                Comprar Projeto Completo
                            </button>
                            <small class="text-muted d-block mt-2">
                                Plantas, documentos e especificações técnicas
                            </small>
                        </div>
                        <div class="col-lg-4">
                            <a href="contato.php" class="btn btn-outline-primary btn-lg w-100">
                                <i class="bi bi-envelope"></i>
                                Interessado? Entre em Contato
                            </a>
                            <small class="text-muted d-block mt-2">
                                Tire suas dúvidas e solicite orçamentos
                            </small>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="contato.php" class="btn btn-lg back-button">
                        <i class="bi bi-envelope"></i>
                        Interessado? Entre em Contato
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Modal de Compra -->
    <div class="modal fade" id="modalCompra" tabindex="-1" aria-labelledby="modalCompraLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="modalCompraLabel">
                        <i class="bi bi-cart-plus"></i> Comprar Projeto: <?= htmlspecialchars($projeto['titulo']) ?>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <img src="<?= asset('uploads/' . htmlspecialchars($projeto['capa_imagem'])) ?>" 
                                 class="img-fluid rounded mb-3" 
                                 alt="<?= htmlspecialchars($projeto['titulo']) ?>">
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold">O que você receberá:</h6>
                            <ul class="list-unstyled">
                                <li><i class="bi bi-check-circle text-success"></i> Plantas baixas completas</li>
                                <li><i class="bi bi-check-circle text-success"></i> Cortes e fachadas</li>
                                <li><i class="bi bi-check-circle text-success"></i> Memorial descritivo</li>
                                <li><i class="bi bi-check-circle text-success"></i> Especificações técnicas</li>
                                <li><i class="bi bi-check-circle text-success"></i> Lista de materiais</li>
                            </ul>
                            
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i>
                                <strong>Arquivo Digital:</strong> Você receberá por email um arquivo ZIP com todos os documentos em formato PDF e DWG.
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <form id="formCompra">
                        <input type="hidden" id="projeto_id" value="<?= $projeto['id'] ?>">
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Nome Completo *</label>
                                <input type="text" class="form-control" id="nome_cliente" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Email *</label>
                                <input type="email" class="form-control" id="email_cliente" required>
                                <small class="text-muted">O arquivo será enviado para este email</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Telefone (WhatsApp)</label>
                                <input type="tel" class="form-control" id="telefone_cliente" placeholder="(11) 99999-9999">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Valor</label>
                                <div class="input-group">
                                    <span class="input-group-text">R$</span>
                                    <input type="text" class="form-control" value="<?= number_format($projeto['valor_projeto'], 2, ',', '.') ?>" readonly>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="aceito_termos" required>
                                <label class="form-check-label" for="aceito_termos">
                                    Concordo com os <a href="#" data-bs-toggle="modal" data-bs-target="#modalTermos">termos e condições</a> de compra
                                </label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" onclick="processarPagamento()">
                        <i class="bi bi-credit-card"></i> Pagar com Mercado Pago
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Termos -->
    <div class="modal fade" id="modalTermos" tabindex="-1" aria-labelledby="modalTermosLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTermosLabel">Termos e Condições de Compra</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6>1. Sobre o Produto</h6>
                    <p>O produto consiste em arquivos digitais contendo plantas, documentos e especificações técnicas do projeto arquitetônico selecionado.</p>
                    
                    <h6>2. Entrega</h6>
                    <p>Os arquivos serão enviados automaticamente para o email informado após a confirmação do pagamento, em até 24 horas.</p>
                    
                    <h6>3. Direitos Autorais</h6>
                    <p>Os projetos são de propriedade intelectual da empresa. A compra concede direito de uso pessoal, sendo proibida a revenda ou distribuição.</p>
                    
                    <h6>4. Suporte</h6>
                    <p>Em caso de dúvidas sobre os arquivos, oferecemos suporte técnico por 30 dias após a compra.</p>
                    
                    <h6>5. Política de Reembolso</h6>
                    <p>Por se tratar de produto digital, não há possibilidade de reembolso após o envio dos arquivos.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Entendi</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- JavaScript para compra -->
    <script>
        function iniciarCompra(projetoId) {
            document.getElementById('projeto_id').value = projetoId;
            const modal = new bootstrap.Modal(document.getElementById('modalCompra'));
            modal.show();
        }

        function processarPagamento() {
            // Validar formulário
            const form = document.getElementById('formCompra');
            const nome = document.getElementById('nome_cliente').value.trim();
            const email = document.getElementById('email_cliente').value.trim();
            const aceito = document.getElementById('aceito_termos').checked;
            
            if (!nome || !email || !aceito) {
                alert('Por favor, preencha todos os campos obrigatórios e aceite os termos.');
                return;
            }
            
            // Validar email
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                alert('Por favor, insira um email válido.');
                return;
            }
            
            // Preparar dados
            const dados = {
                projeto_id: document.getElementById('projeto_id').value,
                nome_cliente: nome,
                email_cliente: email,
                telefone_cliente: document.getElementById('telefone_cliente').value.trim()
            };
            
            // Mostrar loading
            const btnPagar = event.target;
            const textoOriginal = btnPagar.innerHTML;
            btnPagar.innerHTML = '<i class="bi bi-hourglass-split"></i> Processando...';
            btnPagar.disabled = true;
            
            // Enviar para backend
            fetch('<?= url("api/iniciar_pagamento.php") ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(dados)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Redirecionar para o Mercado Pago
                    window.location.href = data.checkout_url;
                } else {
                    alert('Erro: ' + (data.message || 'Não foi possível processar o pagamento'));
                    btnPagar.innerHTML = textoOriginal;
                    btnPagar.disabled = false;
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao processar pagamento. Tente novamente.');
                btnPagar.innerHTML = textoOriginal;
                btnPagar.disabled = false;
            });
        }
    </script>
    
    <!-- Estilos adicionais para andares -->
    <style>
        .andares-section {
            margin-top: 3rem;
        }
        
        .andar-card {
            background: #ffffff;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            padding: 2rem;
            border: 1px solid #e9ecef;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .andar-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        
        .andar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f8f9fa;
        }
        
        .andar-nome {
            color: #2c3e50;
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
        }
        
        .andar-nome i {
            margin-right: 0.5rem;
            font-size: 1.3rem;
        }
        
        .andar-area {
            color: #6c757d;
            font-size: 1.1rem;
            margin-top: 0.5rem;
        }
        
        .andar-area i {
            margin-right: 0.5rem;
        }
        
        .andar-observacoes {
            background: #e8f4fd;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            border-left: 4px solid #17a2b8;
        }
        
        .comodos-andar {
            margin-top: 1.5rem;
        }
        
        .comodos-andar-title {
            color: #28a745;
            margin-bottom: 1rem;
            font-size: 1.2rem;
            font-weight: 600;
        }
        
        .comodos-andar-title i {
            margin-right: 0.5rem;
        }
        
        .comodos-grid-mini {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }
        
        .comodo-mini-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1rem;
            border: 1px solid #dee2e6;
            transition: all 0.3s ease;
        }
        
        .comodo-mini-card:hover {
            background: #e9ecef;
            transform: scale(1.02);
        }
        
        .comodo-tipo {
            display: flex;
            align-items: center;
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
        }
        
        .comodo-tipo i {
            margin-right: 0.5rem;
            color: #007bff;
        }
        
        .comodo-nome {
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 0.5rem;
        }
        
        .comodo-obs {
            font-size: 0.8rem;
            color: #6c757d;
            font-style: italic;
        }
        
        .comodo-obs i {
            margin-right: 0.3rem;
        }
        
        /* Estilos para botões de compra */
        .precos-card {
            position: relative;
            overflow: visible;
        }
        
        .precos-card .btn-success {
            background: linear-gradient(45deg, #28a745, #20c997);
            border: none;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }
        
        .precos-card .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
            background: linear-gradient(45deg, #218838, #1e7e34);
        }
        
        .compra-destaque {
            animation: pulse-buy 2s infinite;
        }
        
        @keyframes pulse-buy {
            0% { box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3); }
            50% { box-shadow: 0 6px 25px rgba(40, 167, 69, 0.6); }
            100% { box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3); }
        }
        
        /* Modal customização */
        .modal-content {
            border: none;
            border-radius: 15px;
            overflow: hidden;
        }
        
        .modal-header.bg-success {
            background: linear-gradient(45deg, #28a745, #20c997) !important;
        }
        
        .list-unstyled li {
            padding: 0.3rem 0;
            font-size: 0.95rem;
        }
        
        .list-unstyled .bi-check-circle {
            margin-right: 0.5rem;
            font-size: 1.1rem;
        }
    </style>
    
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

<?php require_once '../includes/footer.php'; ?>
