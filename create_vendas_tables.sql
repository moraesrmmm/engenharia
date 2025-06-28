-- Adicionar campo de arquivo ZIP na tabela projetos
ALTER TABLE projetos ADD COLUMN arquivo_projeto VARCHAR(255) DEFAULT NULL COMMENT 'Arquivo ZIP com documentos do projeto';

-- Criar tabela de vendas
CREATE TABLE vendas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    projeto_id INT NOT NULL,
    email_cliente VARCHAR(255) NOT NULL,
    nome_cliente VARCHAR(255) NOT NULL,
    telefone_cliente VARCHAR(20),
    valor_pago DECIMAL(10,2) NOT NULL,
    payment_id VARCHAR(255) NOT NULL COMMENT 'ID do pagamento no Mercado Pago',
    status_pagamento ENUM('pending', 'approved', 'rejected', 'cancelled', 'in_process') DEFAULT 'pending',
    status_envio ENUM('pendente', 'enviado', 'erro') DEFAULT 'pendente',
    data_compra TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_aprovacao TIMESTAMP NULL,
    data_envio_email TIMESTAMP NULL,
    observacoes TEXT,
    INDEX idx_projeto_id (projeto_id),
    INDEX idx_email_cliente (email_cliente),
    INDEX idx_payment_id (payment_id),
    INDEX idx_status_pagamento (status_pagamento),
    FOREIGN KEY (projeto_id) REFERENCES projetos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Criar tabela de logs de vendas
CREATE TABLE vendas_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    venda_id INT NOT NULL,
    evento VARCHAR(100) NOT NULL,
    descricao TEXT,
    dados_json TEXT COMMENT 'Dados adicionais em formato JSON',
    ip_cliente VARCHAR(45),
    user_agent TEXT,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_venda_id (venda_id),
    INDEX idx_evento (evento),
    INDEX idx_criado_em (criado_em),
    FOREIGN KEY (venda_id) REFERENCES vendas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Criar configurações do Mercado Pago
CREATE TABLE configuracoes_mp (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chave VARCHAR(100) NOT NULL UNIQUE,
    valor TEXT NOT NULL,
    descricao VARCHAR(255),
    ativo BOOLEAN DEFAULT TRUE,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir configurações padrão do Mercado Pago
INSERT INTO configuracoes_mp (chave, valor, descricao) VALUES
('mp_public_key', 'APP_USR-9784e097-6b3c-4f1a-8f88-29857b922799', 'Chave pública do Mercado Pago'),
('mp_access_token', 'APP_USR-6712424600827825-062814-fe3172b5cf2055e635f47317b941d810-1350257138', 'Token de acesso do Mercado Pago'),
('mp_webhook_url', 'https://damonengenharia.free.nf/public/api/webhook_mercadopago.php', 'URL do webhook para notificações'),
('mp_success_url', 'https://damonengenharia.free.nf/public/api/pagamento_sucesso.php', 'URL de retorno para pagamento aprovado'),
('mp_failure_url', 'https://damonengenharia.free.nf/public/api/pagamento_falha.php', 'URL de retorno para pagamento rejeitado'),
('mp_pending_url', 'https://damonengenharia.free.nf/public/api/pagamento_pendente.php', 'URL de retorno para pagamento pendente'),
('email_remetente', 'romulo_moraes2018@hotmail.com', 'Email remetente para envio dos arquivos'),
('email_nome_remetente', 'Projetos de Engenharia', 'Nome do remetente dos emails');
