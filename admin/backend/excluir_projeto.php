<?php
session_start();
require_once '../../config/config.php';

// Verifica se o admin está logado
if (!isset($_SESSION['admin_logado']) || $_SESSION['admin_logado'] !== true) {
    header("Location: ../login.php");
    exit;
}

// Verifica se foi enviado o ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = 'ID do projeto não informado!';
    header("Location: ../views/listar_projetos.php");
    exit;
}

try {
    $projeto_id = intval($_GET['id']);
    
    // Verifica se o projeto existe
    $stmt = $pdo->prepare("SELECT titulo, capa_imagem FROM projetos WHERE id = ? AND ativo = TRUE");
    $stmt->execute([$projeto_id]);
    $projeto = $stmt->fetch();
    
    if (!$projeto) {
        throw new Exception('Projeto não encontrado!');
    }
    
    // Inicia transação
    $pdo->beginTransaction();
    
    // Desativa o projeto (soft delete)
    $stmt = $pdo->prepare("UPDATE projetos SET ativo = FALSE WHERE id = ?");
    $resultado = $stmt->execute([$projeto_id]);
    
    if (!$resultado) {
        throw new Exception('Erro ao excluir projeto!');
    }
    
    // Desativa os cômodos também
    $stmt = $pdo->prepare("UPDATE comodos SET ativo = FALSE WHERE projeto_id = ?");
    $stmt->execute([$projeto_id]);
    
    // Confirma transação
    $pdo->commit();
    
    $_SESSION['success_message'] = "Projeto '{$projeto['titulo']}' foi excluído com sucesso!";
    
} catch (Exception $e) {
    // Desfaz transação em caso de erro
    $pdo->rollBack();
    $_SESSION['error_message'] = $e->getMessage();
}

header("Location: ../views/listar_projetos.php");
exit;
?>
