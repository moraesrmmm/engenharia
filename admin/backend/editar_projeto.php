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
    $tipo_projeto = trim($_POST['tipo_projeto'] ?? 'Casa Térrea');
    $largura_terreno = floatval($_POST['largura_terreno'] ?? 0);
    $comprimento_terreno = floatval($_POST['comprimento_terreno'] ?? 0);
    $area_terreno = floatval($_POST['area_terreno'] ?? 0);
    $valor_projeto = floatval($_POST['valor_projeto'] ?? 0);
    $custo_mao_obra = floatval($_POST['custo_mao_obra'] ?? 0);
    $custo_materiais = floatval($_POST['custo_materiais'] ?? 0);
    $video_url = trim($_POST['video_url'] ?? '');
    $destaque = isset($_POST['destaque']) ? 1 : 0;
    $area_construida_frontend = floatval($_POST['area_construida_calculated'] ?? 0);

    if (empty($titulo) || empty($descricao) || empty($tipo_projeto) || $largura_terreno <= 0 || $comprimento_terreno <= 0 || $area_terreno <= 0) {
        throw new Exception('Todos os campos obrigatórios devem ser preenchidos corretamente!');
    }

    if ($valor_projeto < 0 || $custo_mao_obra < 0 || $custo_materiais < 0) {
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

    // Upload do arquivo ZIP (opcional)
    $novo_arquivo = null;
    if (!empty($_FILES['arquivo_projeto']['name'])) {
        $upload_dir = '../../public/uploads/projetos/';
        
        // Cria diretório se não existir
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $arquivo_tmp = $_FILES['arquivo_projeto']['tmp_name'];
        $arquivo_nome = $_FILES['arquivo_projeto']['name'];
        $arquivo_ext = strtolower(pathinfo($arquivo_nome, PATHINFO_EXTENSION));

        // Validações do arquivo ZIP
        if ($arquivo_ext !== 'zip') {
            throw new Exception('Apenas arquivos ZIP são permitidos!');
        }

        if ($_FILES['arquivo_projeto']['size'] > 50 * 1024 * 1024) { // 50MB
            throw new Exception('Arquivo muito grande. Tamanho máximo: 50MB');
        }

        // Gera nome único para o arquivo
        $novo_nome = uniqid('projeto_') . '_' . time() . '.zip';
        $caminho_completo = $upload_dir . $novo_nome;

        if (!move_uploaded_file($arquivo_tmp, $caminho_completo)) {
            throw new Exception('Erro ao fazer upload do arquivo do projeto!');
        }

        $novo_arquivo = $novo_nome;

        // Remove arquivo antigo se um novo foi enviado
        if (!empty($projeto_atual['arquivo_projeto']) && file_exists($upload_dir . $projeto_atual['arquivo_projeto'])) {
            unlink($upload_dir . $projeto_atual['arquivo_projeto']);
        }
    }

    // Converte URL do YouTube
    $video_url = convertYouTubeUrl($video_url);

    // Atualiza o projeto (com tipo, largura, comprimento e área do terreno)
    $sql_update = "UPDATE projetos SET 
                   titulo = ?, 
                   descricao = ?, 
                   tipo_projeto = ?, 
                   largura_terreno = ?, 
                   comprimento_terreno = ?, 
                   area_terreno = ?, 
                   valor_projeto = ?, 
                   custo_mao_obra = ?, 
                   custo_materiais = ?, 
                   video_url = ?, 
                   destaque = ?";
    
    $params = [$titulo, $descricao, $tipo_projeto, $largura_terreno, $comprimento_terreno, $area_terreno, 
               $valor_projeto, $custo_mao_obra, $custo_materiais, $video_url, $destaque];

    // Adiciona atualização da imagem se uma nova foi enviada
    if ($nova_imagem) {
        $sql_update .= ", capa_imagem = ?";
        $params[] = $nova_imagem;
    }

    // Adiciona atualização do arquivo se um novo foi enviado
    if ($novo_arquivo) {
        $sql_update .= ", arquivo_projeto = ?";
        $params[] = $novo_arquivo;
    }

    $sql_update .= " WHERE id = ?";
    $params[] = $projeto_id;

    $stmt = $pdo->prepare($sql_update);
    $stmt->execute($params);

    // Processa andares e cômodos
    if (isset($_POST['andares']) && is_array($_POST['andares'])) {
        // Primeiro, marca todos os andares existentes como inativos
        $stmt = $pdo->prepare("UPDATE andares SET ativo = FALSE WHERE projeto_id = ?");
        $stmt->execute([$projeto_id]);

        // Marca todos os cômodos como inativos
        $stmt = $pdo->prepare("UPDATE comodos SET ativo = FALSE WHERE projeto_id = ?");
        $stmt->execute([$projeto_id]);

        // Processa cada andar
        foreach ($_POST['andares'] as $andar_data) {
            $nome_andar = trim($andar_data['nome'] ?? '');
            $area_andar = floatval($andar_data['area'] ?? 0);
            $ordem_andar = intval($andar_data['ordem'] ?? 1);
            $observacoes_andar = trim($andar_data['observacoes'] ?? '');
            $andar_id = isset($andar_data['id']) ? intval($andar_data['id']) : 0;

            if (empty($nome_andar) || $area_andar <= 0) continue; // Pula andares inválidos

            if ($andar_id > 0) {
                // Atualiza andar existente
                $stmt = $pdo->prepare("UPDATE andares SET 
                                     nome = ?, 
                                     area = ?, 
                                     ordem = ?,
                                     observacoes = ?, 
                                     ativo = TRUE 
                                     WHERE id = ? AND projeto_id = ?");
                $stmt->execute([$nome_andar, $area_andar, $ordem_andar, $observacoes_andar, $andar_id, $projeto_id]);
            } else {
                // Insere novo andar
                $stmt = $pdo->prepare("INSERT INTO andares (projeto_id, nome, area, ordem, observacoes) 
                                     VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$projeto_id, $nome_andar, $area_andar, $ordem_andar, $observacoes_andar]);
                $andar_id = $pdo->lastInsertId();
            }

            // Processa cômodos deste andar
            if (isset($andar_data['comodos']) && is_array($andar_data['comodos'])) {
                foreach ($andar_data['comodos'] as $comodo_data) {
                    $tipo_comodo = trim($comodo_data['tipo'] ?? '');
                    $nome_comodo = trim($comodo_data['nome'] ?? '');
                    $observacoes_comodo = trim($comodo_data['observacoes'] ?? '');
                    $comodo_id = isset($comodo_data['id']) ? intval($comodo_data['id']) : 0;

                    if (empty($tipo_comodo)) continue; // Pula cômodos sem tipo

                    if ($comodo_id > 0) {
                        // Atualiza cômodo existente (sem largura/comprimento)
                        $stmt = $pdo->prepare("UPDATE comodos SET 
                                             andar_id = ?,
                                             tipo = ?, 
                                             nome = ?, 
                                             observacoes = ?, 
                                             ativo = TRUE 
                                             WHERE id = ? AND projeto_id = ?");
                        $stmt->execute([$andar_id, $tipo_comodo, $nome_comodo, $observacoes_comodo, $comodo_id, $projeto_id]);
                    } else {
                        // Insere novo cômodo (sem largura/comprimento)
                        $stmt = $pdo->prepare("INSERT INTO comodos (projeto_id, andar_id, tipo, nome, observacoes) 
                                             VALUES (?, ?, ?, ?, ?)");
                        $stmt->execute([$projeto_id, $andar_id, $tipo_comodo, $nome_comodo, $observacoes_comodo]);
                    }
                }
            }
        }
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
    
    // Remove arquivo ZIP se foi feito upload mas houve erro
    if (!empty($novo_arquivo) && file_exists('../../public/uploads/projetos/' . $novo_arquivo)) {
        unlink('../../public/uploads/projetos/' . $novo_arquivo);
    }
    
    // Salvar dados do formulário para repreenchimento
    $_SESSION['form_data'] = $_POST;
    $_SESSION['error_message'] = $e->getMessage();
    header("Location: ../views/editar_projeto.php?id=" . $projeto_id);
    exit;
}
?>
