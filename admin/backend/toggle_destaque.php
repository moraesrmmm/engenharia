<?php
session_start();
require_once '../../config/config.php';

// Verifica se o admin está logado
if (!isset($_SESSION['admin_logado']) || $_SESSION['admin_logado'] !== true) {
    header("Location: ../login.php");
    exit;
}

// Verifica se foi enviado via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../views/listar_projetos.php");
    exit;
}

try {
    $projeto_id = intval($_POST['projeto_id'] ?? 0);
    $destaque = intval($_POST['destaque'] ?? 0);
    
    if ($projeto_id <= 0) {
        throw new Exception('ID do projeto inválido!');
    }
    
    // Verifica se o projeto existe
    $stmt = $pdo->prepare("SELECT titulo FROM projetos WHERE id = ? AND ativo = TRUE");
    $stmt->execute([$projeto_id]);
    $projeto = $stmt->fetch();
    
    if (!$projeto) {
        throw new Exception('Projeto não encontrado!');
    }
    
    // Atualiza o status de destaque
    $stmt = $pdo->prepare("UPDATE projetos SET destaque = ? WHERE id = ?");
    $resultado = $stmt->execute([$destaque, $projeto_id]);
    
    if (!$resultado) {
        throw new Exception('Erro ao atualizar status de destaque!');
    }
    
    $acao = $destaque ? 'adicionado aos' : 'removido dos';
    $_SESSION['success_message'] = "Projeto '{$projeto['titulo']}' foi {$acao} destaques!";
    
} catch (Exception $e) {
    $_SESSION['error_message'] = $e->getMessage();
}

header("Location: ../views/listar_projetos.php");
exit;
?>
