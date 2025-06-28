<?php
session_start();
require_once '../../config/config.php';

// Função para converter URL do YouTube para embed
function convertYouTubeUrl($url) {
    if (empty($url)) return '';
    
    // Padrões de URL do YouTube
    $patterns = [
        '/youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/',
        '/youtu\.be\/([a-zA-Z0-9_-]+)/',
        '/youtube\.com\/embed\/([a-zA-Z0-9_-]+)/'
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $url, $matches)) {
            return 'https://www.youtube.com/embed/' . $matches[1];
        }
    }
    
    return $url; // Retorna a URL original se não for do YouTube
}

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

// Verifica se foi passado o ID do projeto
if (!isset($_POST['projeto_id']) || !is_numeric($_POST['projeto_id'])) {
    $_SESSION['error_message'] = 'ID do projeto inválido!';
    header("Location: ../views/listar_projetos.php");
    exit;
}

$projeto_id = intval($_POST['projeto_id']);

try {
    // Inicia transação
    $pdo->beginTransaction();

    // Validações básicas
    $titulo = trim($_POST['titulo'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $largura = floatval($_POST['largura'] ?? 0);
    $comprimento = floatval($_POST['comprimento'] ?? 0);
    $area = floatval($_POST['area'] ?? 0);
    $preco_total = floatval($_POST['preco_total'] ?? 0);
    $custo_mao_obra = floatval($_POST['custo_mao_obra'] ?? 0);
    $custo_materiais = floatval($_POST['custo_materiais'] ?? 0);
    $video_url = trim($_POST['video_url'] ?? '');
    $destaque = isset($_POST['destaque']) ? 1 : 0;

    if (empty($titulo) || empty($descricao) || $largura <= 0 || $comprimento <= 0 || $area <= 0) {
        throw new Exception('Todos os campos obrigatórios devem ser preenchidos corretamente!');
    }

    if ($preco_total <= 0 || $custo_mao_obra < 0 || $custo_materiais < 0) {
        throw new Exception('Os valores financeiros devem ser válidos!');
    }

    // Verifica se o projeto existe
    $stmt = $pdo->prepare("SELECT capa_imagem FROM projetos WHERE id = ? AND ativo = TRUE");
    $stmt->execute([$projeto_id]);
    $projeto_atual = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$projeto_atual) {
        throw new Exception('Projeto não encontrado!');
    }

    // Processa upload de nova imagem (se enviada)
    $nova_imagem = '';
    if (isset($_FILES['capa_imagem']) && $_FILES['capa_imagem']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../../public/uploads/';
        
        // Cria diretório se não existir
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $arquivo_tmp = $_FILES['capa_imagem']['tmp_name'];
        $arquivo_nome = $_FILES['capa_imagem']['name'];
        $arquivo_ext = strtolower(pathinfo($arquivo_nome, PATHINFO_EXTENSION));

        // Validações do arquivo
        $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($arquivo_ext, $extensoes_permitidas)) {
            throw new Exception('Tipo de arquivo não permitido. Use: ' . implode(', ', $extensoes_permitidas));
        }

        if ($_FILES['capa_imagem']['size'] > 5 * 1024 * 1024) { // 5MB
            throw new Exception('Arquivo muito grande. Tamanho máximo: 5MB');
        }

        // Gera nome único para o arquivo
        $novo_nome = uniqid() . '_' . time() . '.' . $arquivo_ext;
        $caminho_completo = $upload_dir . $novo_nome;

        if (!move_uploaded_file($arquivo_tmp, $caminho_completo)) {
            throw new Exception('Erro ao fazer upload da imagem!');
        }

        $nova_imagem = $novo_nome;

        // Remove imagem antiga se uma nova foi enviada
        if (!empty($projeto_atual['capa_imagem']) && file_exists($upload_dir . $projeto_atual['capa_imagem'])) {
            unlink($upload_dir . $projeto_atual['capa_imagem']);
        }
    }

    // Converte URL do YouTube
    $video_url = convertYouTubeUrl($video_url);

    // Atualiza o projeto
    $sql_update = "UPDATE projetos SET 
                   titulo = ?, 
                   descricao = ?, 
                   largura = ?, 
                   comprimento = ?, 
                   area = ?, 
                   preco_total = ?, 
                   custo_mao_obra = ?, 
                   custo_materiais = ?, 
                   video_url = ?, 
                   destaque = ?";
    
    $params = [$titulo, $descricao, $largura, $comprimento, $area, $preco_total, 
               $custo_mao_obra, $custo_materiais, $video_url, $destaque];

    // Adiciona atualização da imagem se uma nova foi enviada
    if ($nova_imagem) {
        $sql_update .= ", capa_imagem = ?";
        $params[] = $nova_imagem;
    }

    $sql_update .= " WHERE id = ?";
    $params[] = $projeto_id;

    $stmt = $pdo->prepare($sql_update);
    $stmt->execute($params);

    // Processa cômodos
    if (isset($_POST['comodos']) && is_array($_POST['comodos'])) {
        // Primeiro, marca todos os cômodos existentes como inativos
        $stmt = $pdo->prepare("UPDATE comodos SET ativo = FALSE WHERE projeto_id = ?");
        $stmt->execute([$projeto_id]);

        // Processa cada cômodo
        foreach ($_POST['comodos'] as $comodo_data) {
            $tipo = trim($comodo_data['tipo'] ?? '');
            $nome = trim($comodo_data['nome'] ?? '');
            $comodo_largura = floatval($comodo_data['largura'] ?? 0);
            $comodo_comprimento = floatval($comodo_data['comprimento'] ?? 0);
            $observacoes = trim($comodo_data['observacoes'] ?? '');
            $comodo_id = isset($comodo_data['id']) ? intval($comodo_data['id']) : 0;

            if (empty($tipo)) continue; // Pula cômodos sem tipo

            if ($comodo_id > 0) {
                // Atualiza cômodo existente
                $stmt = $pdo->prepare("UPDATE comodos SET 
                                     tipo = ?, 
                                     nome = ?, 
                                     largura = ?, 
                                     comprimento = ?, 
                                     observacoes = ?, 
                                     ativo = TRUE 
                                     WHERE id = ? AND projeto_id = ?");
                $stmt->execute([$tipo, $nome, $comodo_largura, $comodo_comprimento, $observacoes, $comodo_id, $projeto_id]);
            } else {
                // Insere novo cômodo
                $stmt = $pdo->prepare("INSERT INTO comodos (projeto_id, tipo, nome, largura, comprimento, observacoes) 
                                     VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$projeto_id, $tipo, $nome, $comodo_largura, $comodo_comprimento, $observacoes]);
            }
        }
    }

    // Confirma transação
    $pdo->commit();
    
    $_SESSION['success_message'] = 'Projeto atualizado com sucesso!';
    header("Location: ../views/editar_projeto.php?id=" . $projeto_id);
    exit;

} catch (Exception $e) {
    // Desfaz transação em caso de erro
    $pdo->rollBack();
    
    // Remove arquivo de imagem se foi feito upload mas houve erro
    if (!empty($nova_imagem) && file_exists('../../public/uploads/' . $nova_imagem)) {
        unlink('../../public/uploads/' . $nova_imagem);
    }
    
    $_SESSION['error_message'] = $e->getMessage();
    header("Location: ../views/editar_projeto.php?id=" . $projeto_id);
    exit;
}
?>
