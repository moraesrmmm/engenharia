<?php
session_start();
require_once './../config/config.php';

// Se já está logado, redireciona para o dashboard
if (isset($_SESSION['admin_logado'])) {
    header("Location: dashboard.php");
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'] ?? '';
    $senha = $_POST['senha'] ?? '';
    
    if (!empty($usuario) && !empty($senha)) {
        $stmt = $pdo->prepare("SELECT * FROM admin WHERE usuario = ?");
        $stmt->execute([$usuario]);
        $admin = $stmt->fetch();
        
        if ($admin && password_verify($senha, $admin['senha'])) {
            $_SESSION['admin_logado'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_usuario'] = $admin['usuario'];
            header("Location: dashboard.php");
            exit;
        } else {
            $erro = 'Usuário ou senha incorretos!';
        }
    } else {
        $erro = 'Preencha todos os campos!';
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Damon Projetos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', sans-serif;
        }
        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 400px;
            margin: auto;
        }
        .login-header {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
        .login-body {
            padding: 40px;
        }
        .form-control {
            border-radius: 15px;
            border: 2px solid #e9ecef;
            padding: 15px 20px;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }
        .btn-login {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            border: none;
            border-radius: 15px;
            padding: 15px;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s ease;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(52, 152, 219, 0.3);
        }
        .alert {
            border-radius: 15px;
            border: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-card">
            <div class="login-header">
                <i class="bi bi-shield-lock display-3 mb-3"></i>
                <h3 class="mb-0">Admin Login</h3>
                <p class="mb-0 opacity-75">Damon Projetos</p>
            </div>
            <div class="login-body">
                <?php if ($erro): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> <?= $erro ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="mb-4">
                        <label class="form-label fw-bold">
                            <i class="bi bi-person"></i> Usuário
                        </label>
                        <input type="text" name="usuario" class="form-control" required 
                               placeholder="Digite seu usuário" value="<?= htmlspecialchars($_POST['usuario'] ?? '') ?>">
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold">
                            <i class="bi bi-lock"></i> Senha
                        </label>
                        <input type="password" name="senha" class="form-control" required 
                               placeholder="Digite sua senha">
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-login">
                        <i class="bi bi-box-arrow-in-right"></i> Entrar
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
