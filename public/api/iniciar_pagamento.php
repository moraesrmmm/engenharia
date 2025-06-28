<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

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

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método não permitido');
    }
    
    $json = file_get_contents('php://input');
    $dados = json_decode($json, true);
    
    if (!$dados) {
        throw new Exception('Dados inválidos');
    }
    
    // Validar dados obrigatórios
    $projeto_id = intval($dados['projeto_id'] ?? 0);
    $nome_cliente = trim($dados['nome_cliente'] ?? '');
    $email_cliente = trim($dados['email_cliente'] ?? '');
    $telefone_cliente = trim($dados['telefone_cliente'] ?? '');
    
    if (!$projeto_id || !$nome_cliente || !$email_cliente) {
        throw new Exception('Dados obrigatórios não informados');
    }
    
    // Validar email
    if (!filter_var($email_cliente, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Email inválido');
    }
    
    // Buscar projeto
    $stmt = $pdo->prepare("
        SELECT * FROM projetos 
        WHERE id = ? AND ativo = TRUE AND arquivo_projeto IS NOT NULL AND valor_projeto > 0
    ");
    $stmt->execute([$projeto_id]);
    $projeto = $stmt->fetch();
    
    if (!$projeto) {
        throw new Exception('Projeto não encontrado ou não disponível para compra');
    }
    
    // Verificar se o cliente já comprou este projeto
    $stmt_verifica = $pdo->prepare("
        SELECT id FROM vendas 
        WHERE projeto_id = ? AND email_cliente = ? AND status_pagamento = 'approved'
    ");
    $stmt_verifica->execute([$projeto_id, $email_cliente]);
    
    if ($stmt_verifica->fetch()) {
        throw new Exception('Você já possui este projeto. Verifique seu email.');
    }
    
    // Obter configurações do Mercado Pago
    $access_token = obterConfiguracaoMP('mp_access_token');
    
    if (empty($access_token)) {
        throw new Exception('Sistema de pagamento temporariamente indisponível');
    }
    
    // Criar venda no banco
    $stmt = $pdo->prepare("
        INSERT INTO vendas (projeto_id, email_cliente, nome_cliente, telefone_cliente, valor_pago, payment_id, status_pagamento) 
        VALUES (?, ?, ?, ?, ?, ?, 'pending')
    ");
    
    $payment_id_temp = 'TEMP_' . uniqid();
    $stmt->execute([
        $projeto_id, $email_cliente, $nome_cliente, 
        $telefone_cliente, $projeto['valor_projeto'], $payment_id_temp
    ]);
    
    $venda_id = $pdo->lastInsertId();
    
    // Registrar log
    registrarLog($venda_id, 'VENDA_CRIADA', 'Venda criada no sistema', $dados);
    
    // Preparar dados para o Mercado Pago
    $preference_data = [
        'items' => [
            [
                'id' => 'projeto_' . $projeto_id,
                'title' => 'Projeto: ' . $projeto['titulo'],
                'description' => 'Plantas e documentos técnicos do projeto ' . $projeto['titulo'],
                'picture_url' => url('uploads/' . $projeto['capa_imagem']),
                'category_id' => 'others',
                'quantity' => 1,
                'currency_id' => 'BRL',
                'unit_price' => floatval($projeto['valor_projeto'])
            ]
        ],
        'payer' => [
            'name' => $nome_cliente,
            'email' => $email_cliente,
            'phone' => [
                'number' => $telefone_cliente
            ]
        ],
        'back_urls' => [
            'success' => url('api/pagamento_sucesso.php?venda_id=' . $venda_id),
            'failure' => url('api/pagamento_falha.php?venda_id=' . $venda_id),
            'pending' => url('api/pagamento_pendente.php?venda_id=' . $venda_id)
        ],
        'auto_return' => 'approved',
        'external_reference' => 'VENDA_' . $venda_id,
        'notification_url' => url('api/webhook_mercadopago.php'),
        'expires' => true,
        'expiration_date_from' => date('c'),
        'expiration_date_to' => date('c', strtotime('+1 day'))
    ];
    
    // Fazer requisição para o Mercado Pago
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.mercadopago.com/checkout/preferences');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($preference_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $access_token
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code !== 201) {
        registrarLog($venda_id, 'ERRO_MP', 'Erro ao criar preferência no MP', [
            'http_code' => $http_code,
            'response' => $response
        ]);
        throw new Exception('Erro ao criar pagamento. Tente novamente.');
    }
    
    $mp_response = json_decode($response, true);
    
    if (!$mp_response || !isset($mp_response['init_point'])) {
        registrarLog($venda_id, 'ERRO_MP', 'Resposta inválida do MP', $mp_response);
        throw new Exception('Erro ao criar pagamento. Tente novamente.');
    }
    
    // Atualizar venda com ID da preferência
    $stmt = $pdo->prepare("UPDATE vendas SET payment_id = ? WHERE id = ?");
    $stmt->execute([$mp_response['id'], $venda_id]);
    
    registrarLog($venda_id, 'PREFERENCIA_CRIADA', 'Preferência criada no MP', $mp_response);
    
    echo json_encode([
        'success' => true,
        'checkout_url' => $mp_response['init_point'],
        'venda_id' => $venda_id
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
