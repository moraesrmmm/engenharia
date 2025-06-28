<?php 
require_once __DIR__ . '/../auth.php';

// Determinar caminho relativo baseado na localização atual
$currentDir = dirname($_SERVER['SCRIPT_NAME']);
$adminPath = '/engenharia/admin/';
$assetsPath = $adminPath . 'assets/';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Damon Projetos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= $assetsPath ?>css/admin.css" rel="stylesheet">
</head>
<body>
    <header class="admin-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col">
                    <h1 class="h3 mb-0">
                        <i class="bi bi-gear-fill"></i> Painel Administrativo
                    </h1>
                    <small class="opacity-75">Damon Projetos</small>
                </div>
                <div class="col-auto">
                    <div class="dropdown">
                        <button class="btn btn-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?= $_SESSION['admin_usuario'] ?>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?= $adminPath ?>dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                            <li><a class="dropdown-item" href="<?= $adminPath ?>views/cadastrar_projeto.php"><i class="bi bi-plus-circle"></i> Novo Projeto</a></li>
                            <li><a class="dropdown-item" href="<?= $adminPath ?>views/listar_projetos.php"><i class="bi bi-list"></i> Listar Projetos</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?= $adminPath ?>dashboard.php?logout=1"><i class="bi bi-box-arrow-right"></i> Sair</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </header>
    
    <main class="container">
