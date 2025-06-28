<?php require_once './../includes/header.php'; ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="text-center mb-5">
                <h1 class="display-4 mb-3">Entre em Contato</h1>
                <p class="lead">Interessado em nossos projetos? Vamos conversar!</p>
            </div>

            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card h-100 border-0 shadow">
                        <div class="card-body text-center p-4">
                            <div class="mb-3">
                                <i class="bi bi-telephone-fill text-primary fs-1"></i>
                            </div>
                            <h5 class="card-title">Telefone</h5>
                            <p class="card-text">(11) 99999-9999</p>
                            <a href="tel:+5511999999999" class="btn btn-primary">Ligar Agora</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-4">
                    <div class="card h-100 border-0 shadow">
                        <div class="card-body text-center p-4">
                            <div class="mb-3">
                                <i class="bi bi-envelope-fill text-primary fs-1"></i>
                            </div>
                            <h5 class="card-title">Email</h5>
                            <p class="card-text">contato@engenharia.com</p>
                            <a href="mailto:contato@engenharia.com" class="btn btn-primary">Enviar Email</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow mt-4">
                <div class="card-body p-4">
                    <h5 class="card-title mb-4">Envie uma Mensagem</h5>
                    <form>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nome" class="form-label">Nome</label>
                                <input type="text" class="form-control" id="nome" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="assunto" class="form-label">Assunto</label>
                            <input type="text" class="form-control" id="assunto" required>
                        </div>
                        <div class="mb-3">
                            <label for="mensagem" class="form-label">Mensagem</label>
                            <textarea class="form-control" id="mensagem" rows="5" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg">Enviar Mensagem</button>
                    </form>
                </div>
            </div>

            <div class="text-center mt-4">
                <a href="index.php" class="btn btn-outline-primary">
                    <i class="bi bi-arrow-left"></i> Voltar aos Projetos
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once './../includes/footer.php'; ?>