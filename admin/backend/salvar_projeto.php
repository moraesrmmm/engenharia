<?php
require_once '../auth.php';
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
    $tipo_projeto = trim($_POST['tipo_projeto'] ?? 'Casa Térrea');
    $largura_terreno = floatval($_POST['largura_terreno'] ?? 0);
    $comprimento_terreno = floatval($_POST['comprimento_terreno'] ?? 0);
    $area_terreno = floatval($_POST['area_terreno'] ?? 0);

    if (empty($titulo) || empty($descricao) || empty($tipo_projeto) || $largura_terreno <= 0 || $comprimento_terreno <= 0 || $area_terreno <= 0) {
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

    // Upload do arquivo ZIP (opcional)
    $nomeArquivo = null;
    if (!empty($_FILES['arquivo_projeto']['name'])) {
        $arquivo = $_FILES['arquivo_projeto'];
        
        // Validações do arquivo ZIP
        if ($arquivo['type'] !== 'application/zip' && $arquivo['type'] !== 'application/x-zip-compressed') {
            throw new Exception('Apenas arquivos ZIP são permitidos!');
        }
        
        if ($arquivo['size'] > 50 * 1024 * 1024) { // 50MB
            throw new Exception('O arquivo ZIP deve ter no máximo 50MB!');
        }
        
        // Cria diretório se não existir
        $uploadDir = '../../public/uploads/projetos/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $nomeArquivo = uniqid('projeto_') . '.zip';
        $caminhoCompleto = $uploadDir . $nomeArquivo;
        
        if (!move_uploaded_file($arquivo['tmp_name'], $caminhoCompleto)) {
            throw new Exception('Erro ao fazer upload do arquivo do projeto!');
        }
    }

    // Dados do projeto
    $valor_projeto = !empty($_POST['valor_projeto']) ? floatval($_POST['valor_projeto']) : null;
    $custo_mao_obra = !empty($_POST['custo_mao_obra']) ? floatval($_POST['custo_mao_obra']) : null;
    $custo_materiais = !empty($_POST['custo_materiais']) ? floatval($_POST['custo_materiais']) : null;
    $video_url = convertYouTubeUrl(trim($_POST['video_url'] ?? ''));
    $destaque = isset($_POST['destaque']) ? 1 : 0;
    $area_construida_frontend = floatval($_POST['area_construida_calculated'] ?? 0);

    // Salva projeto com novos campos (tipo, largura, comprimento e área do terreno + arquivo)
    $stmt = $pdo->prepare("
        INSERT INTO projetos 
        (titulo, descricao, tipo_projeto, largura_terreno, comprimento_terreno, area_terreno, valor_projeto, custo_mao_obra, custo_materiais, video_url, capa_imagem, arquivo_projeto, destaque) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $resultado = $stmt->execute([
        $titulo, $descricao, $tipo_projeto, $largura_terreno, $comprimento_terreno, $area_terreno,
        $valor_projeto, $custo_mao_obra, $custo_materiais, $video_url, $nomeImagem, $nomeArquivo, $destaque
    ]);

    if (!$resultado) {
        throw new Exception('Erro ao salvar projeto no banco de dados!');
    }

    $projeto_id = $pdo->lastInsertId();

    // Processar andares e cômodos
    $andares = $_POST['andares'] ?? [];
    $totalAndares = 0;
    $totalComodos = 0;

    foreach ($andares as $andar_data) {
        $nome_andar = trim($andar_data['nome'] ?? '');
        $area_andar = floatval($andar_data['area'] ?? 0);
        $ordem_andar = intval($andar_data['ordem'] ?? 1);
        $observacoes_andar = trim($andar_data['observacoes'] ?? '');

        if (empty($nome_andar) || $area_andar <= 0) {
            continue; // Pula andares inválidos
        }

        // Insere andar
        $stmt = $pdo->prepare("
            INSERT INTO andares (projeto_id, nome, area, ordem, observacoes) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $resultado = $stmt->execute([
            $projeto_id, $nome_andar, $area_andar, $ordem_andar, $observacoes_andar
        ]);

        if ($resultado) {
            $andar_id = $pdo->lastInsertId();
            $totalAndares++;

            // Processar cômodos deste andar
            $comodos = $andar_data['comodos'] ?? [];
            
            foreach ($comodos as $comodo_data) {
                $tipo_comodo = trim($comodo_data['tipo'] ?? '');
                $nome_comodo = trim($comodo_data['nome'] ?? '');
                $observacoes_comodo = trim($comodo_data['observacoes'] ?? '');

                if (empty($tipo_comodo)) continue; // Pula cômodos sem tipo

                // Insere cômodo (sem largura e comprimento)
                $stmt = $pdo->prepare("
                    INSERT INTO comodos (projeto_id, andar_id, tipo, nome, observacoes) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                
                $resultado = $stmt->execute([
                    $projeto_id, $andar_id, $tipo_comodo, $nome_comodo, $observacoes_comodo
                ]);

                if ($resultado) {
                    $totalComodos++;
                }
            }
        }
    }

    // Se nenhum andar foi criado, cria um térreo padrão
    if ($totalAndares === 0) {
        $stmt = $pdo->prepare("
            INSERT INTO andares (projeto_id, nome, area, ordem, observacoes) 
            VALUES (?, 'Térreo', ?, 1, 'Andar criado automaticamente')
        ");
        $stmt->execute([$projeto_id, $area_terreno]);
        $totalAndares = 1;
    }

    // Calcular área construída (usa valor do frontend se disponível, senão calcula baseado nos andares)
    $area_construida = $area_construida_frontend;
    
    if ($area_construida <= 0) {
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(area), 0) as area_total FROM andares WHERE projeto_id = ? AND ativo = TRUE");
        $stmt->execute([$projeto_id]);
        $area_construida = $stmt->fetchColumn();
    }

    // Atualizar área construída no projeto
    $stmt = $pdo->prepare("UPDATE projetos SET area_construida = ? WHERE id = ?");
    $stmt->execute([$area_construida, $projeto_id]);

    // Confirma transação
    $pdo->commit();

    // Limpar dados do formulário em caso de sucesso
    if (isset($_SESSION['form_data'])) {
        unset($_SESSION['form_data']);
    }

    // Redireciona com sucesso
    $_SESSION['success_message'] = "Projeto '{$titulo}' cadastrado com sucesso! {$totalAndares} andar(es) e {$totalComodos} cômodos adicionados.";
    header("Location: ../dashboard.php?success=1");
    exit;

} catch (Exception $e) {
    // Desfaz transação em caso de erro
    $pdo->rollBack();
    
    // Remove imagem se foi feito upload
    if (!empty($nomeImagem) && file_exists($uploadDir . $nomeImagem)) {
        unlink($uploadDir . $nomeImagem);
    }
    
    // Salvar dados do formulário para repreenchimento
    $_SESSION['form_data'] = $_POST;
    $_SESSION['error_message'] = $e->getMessage();
    header("Location: ../views/cadastrar_projeto.php?error=1");
    exit;
}
