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
    header("Location: ../views/cadastrar_projeto.php");
    exit;
}

try {
    // Inicia transação
    $pdo->beginTransaction();

    // Validações básicas
    $titulo = trim($_POST['titulo'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $largura = floatval($_POST['largura'] ?? 0);
    $comprimento = floatval($_POST['comprimento'] ?? 0);
    $area = floatval($_POST['area'] ?? 0);

    if (empty($titulo) || empty($descricao) || $largura <= 0 || $comprimento <= 0 || $area <= 0) {
        throw new Exception('Todos os campos obrigatórios devem ser preenchidos corretamente!');
    }

    // Upload da capa
    $nomeImagem = '';
    if (!empty($_FILES['capa_imagem']['name'])) {
        $arquivo = $_FILES['capa_imagem'];
        
        // Validações do arquivo
        $tiposPermitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!in_array($arquivo['type'], $tiposPermitidos)) {
            throw new Exception('Apenas imagens JPEG, PNG e GIF são permitidas!');
        }
        
        if ($arquivo['size'] > 5 * 1024 * 1024) { // 5MB
            throw new Exception('A imagem deve ter no máximo 5MB!');
        }
        
        // Cria diretório se não existir
        $uploadDir = '../../public/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $ext = pathinfo($arquivo['name'], PATHINFO_EXTENSION);
        $nomeImagem = uniqid('projeto_') . '.' . $ext;
        $caminhoCompleto = $uploadDir . $nomeImagem;
        
        if (!move_uploaded_file($arquivo['tmp_name'], $caminhoCompleto)) {
            throw new Exception('Erro ao fazer upload da imagem!');
        }
    } else {
        throw new Exception('A imagem de capa é obrigatória!');
    }

    // Dados do projeto
    $preco_total = !empty($_POST['preco_total']) ? floatval($_POST['preco_total']) : null;
    $custo_mao_obra = !empty($_POST['custo_mao_obra']) ? floatval($_POST['custo_mao_obra']) : null;
    $custo_materiais = !empty($_POST['custo_materiais']) ? floatval($_POST['custo_materiais']) : null;
    $video_url = convertYouTubeUrl(trim($_POST['video_url'] ?? ''));
    $destaque = isset($_POST['destaque']) ? 1 : 0;
    $largura_comprimento = number_format($largura, 2, ',', '') . 'm x ' . number_format($comprimento, 2, ',', '') . 'm';

    // Salva projeto
    $stmt = $pdo->prepare("
        INSERT INTO projetos 
        (titulo, descricao, largura, comprimento, largura_comprimento, area, preco_total, custo_mao_obra, custo_materiais, video_url, capa_imagem, destaque) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $resultado = $stmt->execute([
        $titulo, $descricao, $largura, $comprimento, $largura_comprimento, $area,
        $preco_total, $custo_mao_obra, $custo_materiais, $video_url, $nomeImagem, $destaque
    ]);

    if (!$resultado) {
        throw new Exception('Erro ao salvar projeto no banco de dados!');
    }

    $projeto_id = $pdo->lastInsertId();

    // Cadastrar cômodos
    $tipos = $_POST['tipo'] ?? [];
    $nomes = $_POST['nome'] ?? [];
    $larguras = $_POST['largura_comodo'] ?? [];
    $comprimentos = $_POST['comprimento_comodo'] ?? [];
    $observacoes = $_POST['observacoes'] ?? [];

    $comodosInseridos = 0;
    for ($i = 0; $i < count($tipos); $i++) {
        if (empty($tipos[$i])) continue;

        $stmt = $pdo->prepare("
            INSERT INTO comodos (projeto_id, tipo, nome, largura, comprimento, observacoes) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $resultado = $stmt->execute([
            $projeto_id,
            $tipos[$i],
            $nomes[$i] ?? '',
            !empty($larguras[$i]) ? floatval($larguras[$i]) : null,
            !empty($comprimentos[$i]) ? floatval($comprimentos[$i]) : null,
            $observacoes[$i] ?? ''
        ]);

        if ($resultado) {
            $comodosInseridos++;
        }
    }

    // Confirma transação
    $pdo->commit();

    // Redireciona com sucesso
    $_SESSION['success_message'] = "Projeto '{$titulo}' cadastrado com sucesso! {$comodosInseridos} cômodos adicionados.";
    header("Location: ../dashboard.php?success=1");
    exit;

} catch (Exception $e) {
    // Desfaz transação em caso de erro
    $pdo->rollBack();
    
    // Remove imagem se foi feito upload
    if (!empty($nomeImagem) && file_exists($uploadDir . $nomeImagem)) {
        unlink($uploadDir . $nomeImagem);
    }
    
    $_SESSION['error_message'] = $e->getMessage();
    header("Location: ../views/cadastrar_projeto.php?error=1");
    exit;
}
