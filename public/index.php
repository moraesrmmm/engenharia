<?php
require_once './../config/config.php';
require_once './../includes/header.php';

$stmt = $pdo->query("SELECT * FROM projetos WHERE ativo = TRUE AND destaque = TRUE ORDER BY criado_em DESC LIMIT 6");
$projetos = $stmt->fetchAll();
?>

<!-- Hero Section com Imagem de Fundo -->
<section class="hero-section-bg">
  <div class="hero-overlay">
    <div class="container">
      <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-lg-10 col-xl-8 text-center text-white">
          <div class="hero-content">
            <p class="hero-subtitle mb-3">PLANTAS PRONTAS</p>
            <h1 class="hero-title display-2 fw-bold mb-4">
              Projetos para sua Casa de Campo
            </h1>
            <p class="hero-description mb-5">
              Sítio, Fazenda ou Chácara
            </p>
            
            <div class="hero-features mb-5">
              <div class="feature-item">
                <i class="bi bi-check-circle-fill text-success"></i>
                <span>Projetos de qualidade</span>
              </div>
              <div class="feature-item">
                <i class="bi bi-check-circle-fill text-success"></i>
                <span>Preço acessível</span>
              </div>
              <div class="feature-item">
                <i class="bi bi-check-circle-fill text-success"></i>
                <span>Compra fácil e segura</span>
              </div>
              <div class="feature-item">
                <i class="bi bi-check-circle-fill text-success"></i>
                <span>Envio imediato por e-mail</span>
              </div>
              <div class="feature-item">
                <i class="bi bi-check-circle-fill text-success"></i>
                <span>Suporte via WhatsApp</span>
              </div>
            </div>
            
            <div class="hero-cta">
              <a href="<?= url('projetos.php') ?>" class="btn btn-cta btn-lg">
                Adquira agora seu projeto e<br>
                construa com qualidade e economia!
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<div class="container">

<!-- Stats Section -->
<section class="stats-section py-4 mb-5">
  <div class="container">
    <div class="row text-center">
      <div class="col-md-3 mb-3">
        <div class="stat-card">
          <h3 class="text-primary fw-bold">50+</h3>
          <p class="text-muted">Projetos Realizados</p>
        </div>
      </div>
      <div class="col-md-3 mb-3">
        <div class="stat-card">
          <h3 class="text-primary fw-bold">100%</h3>
          <p class="text-muted">Clientes Satisfeitos</p>
        </div>
      </div>
      <div class="col-md-3 mb-3">
        <div class="stat-card">
          <h3 class="text-primary fw-bold">5 Anos</h3>
          <p class="text-muted">de Experiência</p>
        </div>
      </div>
      <div class="col-md-3 mb-3">
        <div class="stat-card">
          <h3 class="text-primary fw-bold">24/7</h3>
          <p class="text-muted">Suporte</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Featured Projects -->
<section class="projects-section">
  <div class="container">
    <div class="text-center mb-5">
      <h2 class="display-5 fw-bold text-dark">Projetos em Destaque</h2>
      <p class="lead text-muted">Conheça alguns dos nossos trabalhos mais recentes</p>
    </div>

    <div class="row g-4">
      <?php foreach ($projetos as $p): ?>
        <div class="col-lg-4 col-md-6 mb-4">
          <div class="project-card h-100">
            <div class="project-image">
              <img src="<?= asset('uploads/' . htmlspecialchars($p['capa_imagem'])) ?>" 
                   alt="<?= htmlspecialchars($p['titulo']) ?>" 
                   class="img-fluid">
              <div class="project-overlay">
                <a href="detalhes_projeto.php?id=<?= $p['id'] ?>" class="btn btn-light btn-sm">
                  <i class="bi bi-eye"></i> Ver Detalhes
                </a>
              </div>
            </div>
            <div class="project-content">
              <h5 class="project-title"><?= htmlspecialchars($p['titulo']) ?></h5>
              <div class="project-info">
                <?php if (!empty($p['tipo_projeto'])): ?>
                <div class="info-item">
                  <i class="bi bi-house-fill text-primary"></i>
                  <span><?= htmlspecialchars($p['tipo_projeto']) ?></span>
                </div>
                <?php endif; ?>
                <div class="info-item">
                  <i class="bi bi-rulers text-primary"></i>
                  <span>
                    <?php if ($p['largura_terreno'] && $p['comprimento_terreno']): ?>
                      <?= number_format($p['largura_terreno'], 1, ',', '.') ?>m × 
                      <?= number_format($p['comprimento_terreno'], 1, ',', '.') ?>m
                    <?php else: ?>
                      Dimensões não informadas
                    <?php endif; ?>
                  </span>
                </div>
                <div class="info-item">
                  <i class="bi bi-building text-primary"></i>
                  <span>
                    <?php if ($p['area_construida']): ?>
                      <?= number_format($p['area_construida'], 2, ',', '.') ?> m²
                    <?php else: ?>
                      Área não informada
                    <?php endif; ?>
                  </span>
                </div>
                <?php if ($p['valor_projeto']): ?>
                <div class="info-item">
                  <i class="bi bi-currency-dollar text-success"></i>
                  <span>R$ <?= number_format($p['valor_projeto'], 2, ',', '.') ?></span>
                </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="text-center mt-4 mb-4">
      <a href="<?= url('projetos.php') ?>" class="btn btn-primary btn-lg">
        <i class="bi bi-grid-3x3-gap"></i> Ver Todos os Projetos
      </a>
    </div>
  </div>
</section>

<!-- Credibility Section -->
<section class="credibility-section py-5">
  <div class="container">
    <div class="text-center mb-5">
      <h2 class="display-5 fw-bold text-white">São muitas vantagens:</h2>
    </div>
    
    <div class="row g-4">
      <div class="col-lg-4 mb-4">
        <div class="credibility-card">
          <div class="credibility-icon">
            <i class="bi bi-house-fill display-4 text-warning"></i>
          </div>
          <div class="credibility-content">
            <h4 class="credibility-title">Qualidade na Construção</h4>
            <p class="credibility-text">
              Tenha uma casa bem projetada, com ambientes que promovem integração, conforto e funcionalidade. 
              Adquirir na VGProjetos é investir em qualidade.
            </p>
          </div>
        </div>
      </div>
      
      <div class="col-lg-4 mb-4">
        <div class="credibility-card">
          <div class="credibility-icon">
            <i class="bi bi-currency-dollar display-4 text-success"></i>
          </div>
          <div class="credibility-content">
            <h4 class="credibility-title">Baixo Investimento</h4>
            <p class="credibility-text">
              Para economizar desde o início. Preços e formas de pagamento acessíveis para você garantir 
              um projeto com ótimo custo-benefício, sem abrir mão da qualidade.
            </p>
          </div>
        </div>
      </div>
      
      <div class="col-lg-4 mb-4">
        <div class="credibility-card">
          <div class="credibility-icon">
            <i class="bi bi-check-circle-fill display-4 text-info"></i>
          </div>
          <div class="credibility-content">
            <h4 class="credibility-title">Prático e Rápido</h4>
            <p class="credibility-text">
              Com o projeto pronto você pode começar o planejamento da sua construção de forma eficiente 
              e sem atrasos. Adquira agora e tenha acesso imediato ao projeto.
            </p>
          </div>
        </div>
      </div>
    </div>
    
    <div class="text-center mt-5">
      <a href="<?= url('projetos.php') ?>" class="btn btn-credibility btn-lg px-5">
        Ver todos os projetos <i class="bi bi-arrow-right"></i>
      </a>
    </div>
  </div>
</section>

<!-- Services Section -->
<section class="services-section py-5 mt-5 bg-light">
  <div class="container">
    <div class="text-center mb-5">
      <h2 class="display-5 fw-bold text-dark">Nossos Serviços</h2>
      <p class="lead text-muted">Oferecemos soluções completas em engenharia e arquitetura</p>
    </div>
    
    <div class="row g-4">
      <div class="col-lg-4 col-md-6">
        <div class="service-card text-center h-100">
          <div class="service-icon">
            <i class="bi bi-house-gear display-3 text-primary"></i>
          </div>
          <h4 class="service-title">Projetos Residenciais</h4>
          <p class="service-description">
            Casas personalizadas que refletem seu estilo de vida e necessidades específicas.
          </p>
        </div>
      </div>
      
      <div class="col-lg-4 col-md-6">
        <div class="service-card text-center h-100">
          <div class="service-icon">
            <i class="bi bi-building display-3 text-primary"></i>
          </div>
          <h4 class="service-title">Projetos Comerciais</h4>
          <p class="service-description">
            Espaços comerciais funcionais e atraentes que impulsionam seu negócio.
          </p>
        </div>
      </div>
      
      <div class="col-lg-4 col-md-6">
        <div class="service-card text-center h-100">
          <div class="service-icon">
            <i class="bi bi-tools display-3 text-primary"></i>
          </div>
          <h4 class="service-title">Reformas</h4>
          <p class="service-description">
            Renovações completas que transformam espaços existentes em ambientes modernos.
          </p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- WhatsApp Support Section -->
<section class="whatsapp-support-section py-5">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="whatsapp-support-card text-center">
          <div class="whatsapp-support-icon mb-4">
            <i class="bi bi-whatsapp display-2 text-success"></i>
          </div>
          <h3 class="whatsapp-support-title mb-3">Suporte via WhatsApp</h3>
          <p class="whatsapp-support-text mb-4">
            Tire suas dúvidas diretamente conosco! Nossa equipe está pronta para ajudar você 
            a escolher o projeto ideal para sua casa de campo.
          </p>
          <a href="https://wa.me/5511999999999?text=Olá! Gostaria de saber mais sobre os projetos de casa de campo." 
             target="_blank" 
             class="btn btn-whatsapp btn-lg">
            <i class="bi bi-whatsapp me-2"></i>
            Falar no WhatsApp
          </a>
        </div>
      </div>
    </div>
  </div>
</section>

</div> <!-- fim container -->

<!-- Botão Flutuante WhatsApp -->
<div class="whatsapp-float">
  <a href="https://wa.me/5511999999999?text=Olá! Gostaria de saber mais sobre os projetos de casa de campo." 
     target="_blank" 
     class="whatsapp-btn"
     title="Fale conosco no WhatsApp">
    <i class="bi bi-whatsapp"></i>
  </a>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
