<?php 
require_once '../config/config.php';
require_once '../includes/header.php'; ?>

<!-- Hero Section de Contato -->
<section class="contato-hero">
    <div class="container">
        <div class="row align-items-center min-vh-75">
            <div class="col-lg-6">
                <div class="hero-content">
                    <span class="hero-badge">
                        <i class="bi bi-chat-dots"></i> FALE CONOSCO
                    </span>
                    <h1 class="hero-title">
                        Vamos Realizar Seu
                        <span class="text-highlight">Projeto dos Sonhos</span>
                    </h1>
                    <p class="hero-description">
                        Entre em contato conosco e descubra como podemos transformar suas ideias em realidade. Nossa equipe está pronta para atendê-lo com excelência e dedicação.
                    </p>
                    <div class="hero-stats">
                        <div class="stat-item">
                            <div class="stat-number">500+</div>
                            <div class="stat-label">Projetos Entregues</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">24h</div>
                            <div class="stat-label">Resposta Garantida</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">100%</div>
                            <div class="stat-label">Satisfação</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="hero-image">
                    <div class="contact-illustration">
                        <i class="bi bi-house-heart"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Seção de Contatos -->
<section class="contact-section py-5">
    <div class="container">
        <div class="row">
            <!-- Informações de Contato -->
            <div class="col-lg-4 mb-5">
                <div class="contact-info">
                    <h3 class="section-title mb-4">
                        <i class="bi bi-geo-alt text-primary me-2"></i>
                        Como Nos Encontrar
                    </h3>
                    
                    <div class="contact-methods">
                        <!-- WhatsApp -->
                        <div class="contact-item whatsapp-contact">
                            <div class="contact-icon">
                                <i class="bi bi-whatsapp"></i>
                            </div>
                            <div class="contact-details">
                                <h5>WhatsApp</h5>
                                <p>(11) 99999-9999</p>
                                <a href="https://wa.me/5511999999999" target="_blank" class="contact-btn whatsapp-btn">
                                    <i class="bi bi-whatsapp me-2"></i>Conversar Agora
                                </a>
                            </div>
                        </div>

                        <!-- Telefone -->
                        <div class="contact-item phone-contact">
                            <div class="contact-icon">
                                <i class="bi bi-telephone"></i>
                            </div>
                            <div class="contact-details">
                                <h5>Telefone</h5>
                                <p>(11) 3333-4444</p>
                                <a href="tel:+551133334444" class="contact-btn phone-btn">
                                    <i class="bi bi-telephone me-2"></i>Ligar Agora
                                </a>
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="contact-item email-contact">
                            <div class="contact-icon">
                                <i class="bi bi-envelope"></i>
                            </div>
                            <div class="contact-details">
                                <h5>Email</h5>
                                <p>contato@damonprojetos.com</p>
                                <a href="mailto:contato@damonprojetos.com" class="contact-btn email-btn">
                                    <i class="bi bi-envelope me-2"></i>Enviar Email
                                </a>
                            </div>
                        </div>

                        <!-- Horário -->
                        <div class="contact-item schedule-contact">
                            <div class="contact-icon">
                                <i class="bi bi-clock"></i>
                            </div>
                            <div class="contact-details">
                                <h5>Horário de Atendimento</h5>
                                <div class="schedule-list">
                                    <div class="schedule-item">
                                        <span>Segunda à Sexta:</span>
                                        <span>8:00 - 18:00</span>
                                    </div>
                                    <div class="schedule-item">
                                        <span>Sábado:</span>
                                        <span>8:00 - 14:00</span>
                                    </div>
                                    <div class="schedule-item">
                                        <span>Domingo:</span>
                                        <span>Fechado</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formulário de Contato -->
            <div class="col-lg-8">
                <div class="contact-form-container">
                    <div class="form-header">
                        <h3 class="form-title">
                            <i class="bi bi-send text-primary me-2"></i>
                            Envie Sua Mensagem
                        </h3>
                        <p class="form-subtitle">
                            Preencha o formulário abaixo e entraremos em contato em até 24 horas
                        </p>
                    </div>

                    <form class="professional-form" id="contactForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nome" class="form-label">
                                        <i class="bi bi-person me-2"></i>Nome Completo
                                    </label>
                                    <input type="text" class="form-control" id="nome" name="nome" required>
                                    <div class="form-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email" class="form-label">
                                        <i class="bi bi-envelope me-2"></i>Email
                                    </label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                    <div class="form-feedback"></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="telefone" class="form-label">
                                        <i class="bi bi-telephone me-2"></i>Telefone/WhatsApp
                                    </label>
                                    <input type="tel" class="form-control" id="telefone" name="telefone" required>
                                    <div class="form-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tipo_projeto" class="form-label">
                                        <i class="bi bi-house me-2"></i>Tipo de Projeto
                                    </label>
                                    <select class="form-control" id="tipo_projeto" name="tipo_projeto" required>
                                        <option value="">Selecione o tipo de projeto</option>
                                        <option value="casa_campo">Casa de Campo</option>
                                        <option value="sitio">Sítio</option>
                                        <option value="fazenda">Fazenda</option>
                                        <option value="chacara">Chácara</option>
                                        <option value="reforma">Reforma</option>
                                        <option value="outro">Outro</option>
                                    </select>
                                    <div class="form-feedback"></div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="assunto" class="form-label">
                                <i class="bi bi-chat-square-text me-2"></i>Assunto
                            </label>
                            <input type="text" class="form-control" id="assunto" name="assunto" required>
                            <div class="form-feedback"></div>
                        </div>

                        <div class="form-group">
                            <label for="mensagem" class="form-label">
                                <i class="bi bi-pencil-square me-2"></i>Mensagem
                            </label>
                            <textarea class="form-control" id="mensagem" name="mensagem" rows="6" required 
                                      placeholder="Conte-nos sobre seu projeto, suas necessidades e expectativas..."></textarea>
                            <div class="form-feedback"></div>
                        </div>

                        <div class="form-group">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="aceito_termos" name="aceito_termos" required>
                                <label class="form-check-label" for="aceito_termos">
                                    Concordo em receber comunicações por email e WhatsApp sobre meus projetos
                                </label>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary btn-submit">
                                <i class="bi bi-send me-2"></i>
                                <span class="btn-text">Enviar Mensagem</span>
                                <div class="btn-loader">
                                    <div class="spinner-border spinner-border-sm" role="status"></div>
                                </div>
                            </button>
                            <div class="form-success-message">
                                <i class="bi bi-check-circle me-2"></i>
                                Mensagem enviada com sucesso! Entraremos em contato em breve.
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Seção de Vantagens -->
<section class="advantages-section py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h3 class="section-title">Por Que Escolher a Damon Projetos?</h3>
            <p class="section-subtitle">Mais de 10 anos transformando sonhos em realidade</p>
        </div>
        
        <div class="row">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="advantage-card">
                    <div class="advantage-icon">
                        <i class="bi bi-award"></i>
                    </div>
                    <h5>Qualidade Garantida</h5>
                    <p>Projetos desenvolvidos seguindo as melhores práticas e normas técnicas</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="advantage-card">
                    <div class="advantage-icon">
                        <i class="bi bi-lightning"></i>
                    </div>
                    <h5>Entrega Rápida</h5>
                    <p>Envio imediato por email após confirmação do pagamento</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="advantage-card">
                    <div class="advantage-icon">
                        <i class="bi bi-headset"></i>
                    </div>
                    <h5>Suporte Completo</h5>
                    <p>Atendimento via WhatsApp para tirar todas suas dúvidas</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="advantage-card">
                    <div class="advantage-icon">
                        <i class="bi bi-wallet"></i>
                    </div>
                    <h5>Preço Justo</h5>
                    <p>Projetos com excelente custo-benefício para todos os orçamentos</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CSS específico para a página de contato -->
<style>
/* Hero Section */
.contato-hero {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    color: white;
    padding: 120px 0 80px;
    position: relative;
    overflow: hidden;
}

.contato-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="%23ffffff" opacity="0.05"/><circle cx="75" cy="75" r="1" fill="%23ffffff" opacity="0.05"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
}

.hero-badge {
    display: inline-block;
    background: rgba(255, 255, 255, 0.2);
    color: white;
    padding: 8px 20px;
    border-radius: 25px;
    font-size: 0.9rem;
    font-weight: 600;
    margin-bottom: 20px;
    backdrop-filter: blur(10px);
}

.hero-title {
    font-size: 3.5rem;
    font-weight: 700;
    line-height: 1.2;
    margin-bottom: 25px;
}

.text-highlight {
    color: #e67e22;
    font-weight: 800;
    text-shadow: 2px 2px 4px rgba(230, 126, 34, 0.3);
    position: relative;
    display: inline-block;
}

.text-highlight::after {
    content: '';
    position: absolute;
    bottom: -5px;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #e67e22, #f39c12, #e67e22);
    border-radius: 2px;
    animation: shimmer 2s ease-in-out infinite;
}

@keyframes shimmer {
    0%, 100% { opacity: 0.7; }
    50% { opacity: 1; }
}

.hero-description {
    font-size: 1.3rem;
    margin-bottom: 40px;
    opacity: 0.9;
    line-height: 1.6;
}

.hero-stats {
    display: flex;
    gap: 40px;
    margin-top: 40px;
}

.stat-item {
    text-align: center;
}

.stat-number {
    font-size: 2.5rem;
    font-weight: 700;
    color: #e67e22;
    margin-bottom: 5px;
}

.stat-label {
    font-size: 0.9rem;
    opacity: 0.8;
}

.contact-illustration {
    text-align: center;
    position: relative;
}

.contact-illustration i {
    font-size: 15rem;
    color: rgba(255, 255, 255, 0.1);
    animation: float 3s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-20px); }
}

/* Contact Section */
.contact-section {
    background: white;
    position: relative;
    z-index: 2;
}

.contact-info {
    background: white;
    border-radius: 25px;
    padding: 40px;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
    height: fit-content;
    position: sticky;
    top: 100px;
}

.section-title {
    font-size: 1.8rem;
    font-weight: 700;
    color: #2c3e50;
}

.contact-methods {
    display: flex;
    flex-direction: column;
    gap: 30px;
}

.contact-item {
    display: flex;
    align-items: flex-start;
    gap: 20px;
    padding: 25px;
    border-radius: 20px;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.contact-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.whatsapp-contact {
    background: linear-gradient(135deg, #25d366 0%, #128c7e 100%);
    color: white;
}

.whatsapp-contact:hover {
    border-color: #25d366;
}

.phone-contact {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    color: white;
}

.email-contact {
    background: linear-gradient(135deg, #e67e22 0%, #d35400 100%);
    color: white;
}

.schedule-contact {
    background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
    color: white;
}

.contact-icon {
    width: 60px;
    height: 60px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    flex-shrink: 0;
}

.contact-details h5 {
    margin-bottom: 10px;
    font-weight: 600;
}

.contact-details p {
    margin-bottom: 15px;
    opacity: 0.9;
}

.contact-btn {
    display: inline-flex;
    align-items: center;
    padding: 10px 20px;
    background: rgba(255, 255, 255, 0.2);
    color: white;
    text-decoration: none;
    border-radius: 25px;
    font-weight: 600;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
}

.contact-btn:hover {
    background: rgba(255, 255, 255, 0.3);
    color: white;
    transform: translateX(5px);
    text-decoration: none;
}

.schedule-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.schedule-item {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
}

/* Formulário */
.contact-form-container {
    background: white;
    border-radius: 25px;
    padding: 50px;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
    border: 1px solid #f1f1f1;
}

.form-header {
    text-align: center;
    margin-bottom: 40px;
}

.form-title {
    font-size: 2.2rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 15px;
}

.form-subtitle {
    color: #6c757d;
    font-size: 1.1rem;
}

.professional-form .form-group {
    margin-bottom: 30px;
}

.professional-form .form-label {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
}

.professional-form .form-control {
    border: 2px solid #e9ecef;
    border-radius: 15px;
    padding: 15px 20px;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: #fafafa;
}

.professional-form .form-control:focus {
    border-color: #3498db;
    box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
    background: white;
}

.professional-form .form-control:valid {
    border-color: #27ae60;
    background: white;
}

.form-actions {
    text-align: center;
    margin-top: 40px;
}

.btn-submit {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    border: none;
    padding: 18px 50px;
    border-radius: 50px;
    font-size: 1.1rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
    box-shadow: 0 10px 30px rgba(52, 152, 219, 0.3);
}

.btn-submit:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 40px rgba(52, 152, 219, 0.4);
}

.btn-loader {
    display: none;
}

.btn-submit.loading .btn-text {
    display: none;
}

.btn-submit.loading .btn-loader {
    display: inline-block;
}

.form-success-message {
    display: none;
    background: #27ae60;
    color: white;
    padding: 15px 30px;
    border-radius: 50px;
    margin-top: 20px;
    font-weight: 600;
}

/* Advantages Section */
.advantages-section {
    background: #f8f9fa;
}

.advantage-card {
    background: white;
    padding: 40px 30px;
    border-radius: 20px;
    text-align: center;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    height: 100%;
}

.advantage-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
}

.advantage-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 25px;
    font-size: 2rem;
    color: white;
}

.advantage-card h5 {
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 15px;
}

.advantage-card p {
    color: #6c757d;
    line-height: 1.6;
}

/* Responsive */
@media (max-width: 768px) {
    .contato-hero {
        padding: 80px 0 60px;
    }
    
    .hero-title {
        font-size: 2.5rem;
    }
    
    .hero-stats {
        flex-direction: column;
        gap: 20px;
        margin-top: 30px;
    }
    
    .contact-illustration i {
        font-size: 8rem;
    }
    
    .contact-info,
    .contact-form-container {
        padding: 30px;
        margin-bottom: 30px;
    }
    
    .contact-item {
        flex-direction: column;
        text-align: center;
        gap: 15px;
    }
    
    .form-title {
        font-size: 1.8rem;
    }
    
    .advantage-card {
        margin-bottom: 30px;
    }
}

@media (max-width: 480px) {
    .hero-title {
        font-size: 2rem;
    }
    
    .contact-info,
    .contact-form-container {
        padding: 20px;
    }
    
    .btn-submit {
        padding: 15px 30px;
        font-size: 1rem;
    }
}
</style>

<!-- JavaScript para o formulário -->
<script>
document.getElementById('contactForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const submitBtn = this.querySelector('.btn-submit');
    const successMessage = this.querySelector('.form-success-message');
    
    // Simular envio
    submitBtn.classList.add('loading');
    
    setTimeout(() => {
        submitBtn.classList.remove('loading');
        successMessage.style.display = 'block';
        this.reset();
        
        setTimeout(() => {
            successMessage.style.display = 'none';
        }, 5000);
    }, 2000);
});

// Máscara para telefone
document.getElementById('telefone').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    value = value.replace(/(\d{2})(\d)/, '($1) $2');
    value = value.replace(/(\d{5})(\d)/, '$1-$2');
    e.target.value = value;
});
</script>

<?php require_once '../includes/footer.php'; ?>