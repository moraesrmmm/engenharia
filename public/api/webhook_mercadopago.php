<?php
header('Content-Type: application/json');
require_once '../../config/config.php';

// Função para registrar logs
function registrarLog($venda_id, $evento, $descricao, $dados_json = null) {
    global $pdo;
    
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    $stmt = $pdo->prepare("
        INSERT INTO vendas_logs (venda_id, evento, descricao, dados_json, ip_cliente, user_agent) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $venda_id, $evento, $descricao, 
        $dados_json ? json_encode($dados_json) : null,
        $ip, $user_agent
    ]);
}

// Função para obter configurações do Mercado Pago
function obterConfiguracaoMP($chave) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT valor FROM configuracoes_mp WHERE chave = ? AND ativo = TRUE");
    $stmt->execute([$chave]);
    $result = $stmt->fetch();
    
    return $result ? $result['valor'] : '';
}

// Função para enviar email com arquivo
function enviarArquivoPorEmail($venda_id) {
    global $pdo;
    
    try {
        // Buscar dados da venda
        $stmt = $pdo->prepare("
            SELECT v.*, p.titulo, p.arquivo_projeto, p.capa_imagem
            FROM vendas v 
            INNER JOIN projetos p ON v.projeto_id = p.id 
            WHERE v.id = ? AND v.status_pagamento = 'approved' AND v.status_envio = 'pendente'
        ");
        $stmt->execute([$venda_id]);
        $venda = $stmt->fetch();
        
        if (!$venda || empty($venda['arquivo_projeto'])) {
            throw new Exception('Venda não encontrada ou sem arquivo');
        }
        
        $arquivo_path = '../../public/uploads/projetos/' . $venda['arquivo_projeto'];
        
        if (!file_exists($arquivo_path)) {
            throw new Exception('Arquivo do projeto não encontrado');
        }
        
        // Configurações do email
        $para = $venda['email_cliente'];
        $nome_cliente = $venda['nome_cliente'];
        $titulo_projeto = $venda['titulo'];
        
        $assunto = "Seu projeto: {$titulo_projeto} - Arquivos para Download";
        
        $mensagem = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .header { background: #007bff; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; }
                .footer { background: #f8f9fa; padding: 15px; text-align: center; font-size: 12px; color: #666; }
                .btn { background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; }
                .project-info { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h2>🏠 Projeto Entregue com Sucesso!</h2>
            </div>
            
            <div class='content'>
                <p>Olá <strong>{$nome_cliente}</strong>,</p>
                
                <p>Seu pagamento foi confirmado e o projeto <strong>{$titulo_projeto}</strong> está pronto para download!</p>
                
                <div class='project-info'>
                    <h3>📋 O que você está recebendo:</h3>
                    <ul>
                        <li>✅ Plantas baixas completas em formato PDF e DWG</li>
                        <li>✅ Cortes e fachadas detalhados</li>
                        <li>✅ Memorial descritivo técnico</li>
                        <li>✅ Lista de materiais especificados</li>
                        <li>✅ Detalhes construtivos importantes</li>
                    </ul>
                </div>
                
                <p><strong>📁 Arquivo anexado:</strong> Todos os documentos estão compactados no arquivo ZIP anexo a este email.</p>
                
                <h3>🔧 Suporte Técnico:</h3>
                <p>Oferecemos suporte técnico por <strong>30 dias</strong> para esclarecer dúvidas sobre os projetos.</p>
                
                <h3>⚖️ Direitos e Licença:</h3>
                <p>Este projeto é licenciado para uso pessoal. É proibida a revenda ou distribuição dos arquivos.</p>
                
                <p>Em caso de dúvidas, entre em contato conosco:</p>
                <ul>
                    <li>📧 Email: contato@engenharia.com</li>
                    <li>📱 WhatsApp: (11) 99999-9999</li>
                </ul>
                
                <p>Obrigado pela confiança e bom projeto!</p>
                
                <p><strong>Equipe Projetos de Engenharia</strong></p>
            </div>
            
            <div class='footer'>
                <p>Este é um email automático. Não responda diretamente a esta mensagem.</p>
                <p>© " . date('Y') . " Projetos de Engenharia - Todos os direitos reservados</p>
            </div>
        </body>
        </html>
        ";
        
        // Headers do email
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: Projetos de Engenharia <projetos@engenharia.com>" . "\r\n";
        
        // Preparar anexo
        $boundary = md5(time());
        $headers .= "Content-Type: multipart/mixed; boundary=\"{$boundary}\"" . "\r\n";
        
        $message_body = "--{$boundary}\r\n";
        $message_body .= "Content-Type: text/html; charset=UTF-8\r\n";
        $message_body .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
        $message_body .= $mensagem . "\r\n";
        
        // Anexar arquivo
        $file_content = file_get_contents($arquivo_path);
        $file_content = chunk_split(base64_encode($file_content));
        
        $message_body .= "--{$boundary}\r\n";
        $message_body .= "Content-Type: application/zip; name=\"{$titulo_projeto}.zip\"\r\n";
        $message_body .= "Content-Disposition: attachment; filename=\"{$titulo_projeto}.zip\"\r\n";
        $message_body .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $message_body .= $file_content . "\r\n";
        $message_body .= "--{$boundary}--";
        
        // Enviar email
        if (mail($para, $assunto, $message_body, $headers)) {
            // Atualizar status de envio
            $stmt = $pdo->prepare("
                UPDATE vendas 
                SET status_envio = 'enviado', data_envio_email = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$venda_id]);
            
            registrarLog($venda_id, 'EMAIL_ENVIADO', 'Arquivo enviado por email com sucesso');
            return true;
        } else {
            throw new Exception('Falha no envio do email');
        }
        
    } catch (Exception $e) {
        // Atualizar status de erro
        $stmt = $pdo->prepare("UPDATE vendas SET status_envio = 'erro' WHERE id = ?");
        $stmt->execute([$venda_id]);
        
        registrarLog($venda_id, 'ERRO_EMAIL', 'Erro ao enviar email: ' . $e->getMessage());
        return false;
    }
}

try {
    // Log da requisição
    $input = file_get_contents('php://input');
    file_put_contents('../../logs/webhook_mp_' . date('Y-m-d') . '.log', 
        date('Y-m-d H:i:s') . " - " . $input . "\n", FILE_APPEND);
    
    $data = json_decode($input, true);
    
    if (!$data || !isset($data['data']['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid data']);
        exit;
    }
    
    $payment_id = $data['data']['id'];
    $access_token = obterConfiguracaoMP('mp_access_token');
    
    if (empty($access_token)) {
        http_response_code(500);
        echo json_encode(['error' => 'Configuration error']);
        exit;
    }
    
    // Buscar detalhes do pagamento no Mercado Pago
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.mercadopago.com/v1/payments/{$payment_id}");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $access_token
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code !== 200) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch payment']);
        exit;
    }
    
    $payment_data = json_decode($response, true);
    
    if (!$payment_data) {
        http_response_code(500);
        echo json_encode(['error' => 'Invalid payment data']);
        exit;
    }
    
    // Buscar venda pela external_reference
    $external_reference = $payment_data['external_reference'] ?? '';
    
    if (strpos($external_reference, 'VENDA_') === 0) {
        $venda_id = intval(str_replace('VENDA_', '', $external_reference));
        
        $stmt = $pdo->prepare("SELECT * FROM vendas WHERE id = ?");
        $stmt->execute([$venda_id]);
        $venda = $stmt->fetch();
        
        if ($venda) {
            $status_antigo = $venda['status_pagamento'];
            $status_novo = $payment_data['status'];
            
            // Atualizar status da venda
            $stmt = $pdo->prepare("
                UPDATE vendas 
                SET status_pagamento = ?, data_aprovacao = ? 
                WHERE id = ?
            ");
            
            $data_aprovacao = ($status_novo === 'approved') ? date('Y-m-d H:i:s') : null;
            $stmt->execute([$status_novo, $data_aprovacao, $venda_id]);
            
            registrarLog($venda_id, 'WEBHOOK_RECEBIDO', 
                "Status alterado de {$status_antigo} para {$status_novo}", $payment_data);
            
            // Se foi aprovado, enviar arquivo por email
            if ($status_novo === 'approved' && $status_antigo !== 'approved') {
                enviarArquivoPorEmail($venda_id);
            }
        }
    }
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
