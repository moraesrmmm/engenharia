<?php 
require_once '../config/config.php';
require_once '../includes/header.php'; 
?>

<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <h1 class="text-center mb-5">Entre em Contato</h1>
            
            <div class="row">
                <!-- Informações de Contato -->
                <div class="col-lg-4 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="bi bi-whatsapp text-success"></i> WhatsApp
                            </h5>
                            <p class="card-text">(11) 99999-9999</p>
                            <a href="https://wa.me/5511999999999" target="_blank" class="btn btn-success">
                                <i class="bi bi-whatsapp"></i> Conversar
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="bi bi-envelope text-primary"></i> Email
                            </h5>
                            <p class="card-text">contato@damonprojetos.com</p>
                            <a href="mailto:contato@damonprojetos.com" class="btn btn-primary">
                                <i class="bi bi-envelope"></i> Enviar Email
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="bi bi-telephone text-info"></i> Telefone
                            </h5>
                            <p class="card-text">(11) 3333-4444</p>
                            <a href="tel:+551133334444" class="btn btn-info">
                                <i class="bi bi-telephone"></i> Ligar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Formulário de Contato -->
            <div class="row mt-5">
                <div class="col-lg-8 mx-auto">
                    <div class="card">
                        <div class="card-header">
                            <h4><i class="bi bi-chat-dots"></i> Envie sua Mensagem</h4>
                        </div>
                        <div class="card-body">
                            <form id="contactForm" method="POST" action="">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="nome" class="form-label">Nome *</label>
                                        <input type="text" class="form-control" id="nome" name="nome" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">Email *</label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="telefone" class="form-label">Telefone *</label>
                                        <input type="tel" class="form-control" id="telefone" name="telefone" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="tipo_projeto" class="form-label">Tipo de Projeto</label>
                                        <select class="form-select" id="tipo_projeto" name="tipo_projeto">
                                            <option value="">Selecione...</option>
                                            <option value="casa_campo">Casa de Campo</option>
                                            <option value="sitio">Sítio</option>
                                            <option value="fazenda">Fazenda</option>
                                            <option value="chacara">Chácara</option>
                                            <option value="reforma">Reforma</option>
                                            <option value="outro">Outro</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="assunto" class="form-label">Assunto *</label>
                                    <input type="text" class="form-control" id="assunto" name="assunto" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="mensagem" class="form-label">Mensagem *</label>
                                    <textarea class="form-control" id="mensagem" name="mensagem" rows="5" required></textarea>
                                </div>
                                
                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="bi bi-send"></i> Enviar Mensagem
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Máscara para telefone
document.getElementById('telefone').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    value = value.replace(/(\d{2})(\d)/, '($1) $2');
    value = value.replace(/(\d{5})(\d)/, '$1-$2');
    e.target.value = value;
});

// Validação do formulário
document.getElementById('contactForm').addEventListener('submit', function(e) {
    e.preventDefault();
    alert('Mensagem enviada com sucesso! Entraremos em contato em breve.');
});
</script>

<?php require_once '../includes/footer.php'; ?>
