
CREATE TABLE admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Inserir usuário admin padrão (senha: damoneng3@@1546831!!)
INSERT INTO admin (usuario, senha) VALUES 
('admin', '$2y$10$nqpl/Ht1xa0Rh7w0OKEsneb816pcBUruXm9iCZtsjLH6adY5egOc.');

-- =================================================================
-- TABELA DE PROJETOS (Estrutura Atualizada)
-- =================================================================
CREATE TABLE projetos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    descricao TEXT NOT NULL,
    
    -- Dimensões e áreas do terreno
    largura_terreno DECIMAL(8,2) NOT NULL,
    comprimento_terreno DECIMAL(8,2) NOT NULL,
    area_terreno DECIMAL(10,2) NOT NULL,
    area_construida DECIMAL(10,2) DEFAULT 0,
    
    -- Valores atualizados
    valor_projeto DECIMAL(12,2) NULL,
    custo_mao_obra DECIMAL(12,2) NULL,
    custo_materiais DECIMAL(12,2) NULL,
    
    -- Mídia
    video_url VARCHAR(500) NULL,
    capa_imagem VARCHAR(255) NOT NULL,
    arquivo_projeto VARCHAR(255) DEFAULT NULL COMMENT 'Arquivo ZIP com documentos do projeto',
    
    -- Status e controle
    destaque BOOLEAN DEFAULT FALSE,
    ativo BOOLEAN DEFAULT TRUE,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Índices para performance
    INDEX idx_destaque (destaque),
    INDEX idx_ativo (ativo),
    INDEX idx_criado_em (criado_em)
);

-- =================================================================
-- TABELA DE ANDARES (Nova Estrutura Hierárquica)
-- =================================================================
CREATE TABLE andares (
    id INT AUTO_INCREMENT PRIMARY KEY,
    projeto_id INT NOT NULL,
    nome VARCHAR(100) NOT NULL,
    area DECIMAL(8,2) NOT NULL,
    ordem INT DEFAULT 1,
    observacoes TEXT NULL,
    ativo BOOLEAN DEFAULT TRUE,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (projeto_id) REFERENCES projetos(id) ON DELETE CASCADE,
    INDEX idx_projeto_ordem (projeto_id, ordem),
    INDEX idx_ativo (ativo)
);

-- =================================================================
-- TABELA DE CÔMODOS (Atualizada para Andares)
-- =================================================================
CREATE TABLE comodos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    projeto_id INT NOT NULL,
    andar_id INT NOT NULL,
    tipo VARCHAR(50) NOT NULL,
    nome VARCHAR(100) NULL,
    observacoes TEXT NULL,
    ativo BOOLEAN DEFAULT TRUE,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (projeto_id) REFERENCES projetos(id) ON DELETE CASCADE,
    FOREIGN KEY (andar_id) REFERENCES andares(id) ON DELETE CASCADE,
    INDEX idx_projeto (projeto_id),
    INDEX idx_andar (andar_id),
    INDEX idx_tipo (tipo),
    INDEX idx_ativo (ativo)
);

-- =================================================================
-- TABELA DE VENDAS
-- =================================================================
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

-- =================================================================
-- TABELA DE LOGS DE VENDAS
-- =================================================================
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

-- =================================================================
-- TABELA DE CONFIGURAÇÕES DO MERCADO PAGO
-- =================================================================
CREATE TABLE configuracoes_mp (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chave VARCHAR(100) NOT NULL UNIQUE,
    valor TEXT NOT NULL,
    descricao VARCHAR(255),
    ativo BOOLEAN DEFAULT TRUE,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =================================================================
-- CONFIGURAÇÕES PADRÃO DO MERCADO PAGO
-- =================================================================
INSERT INTO configuracoes_mp (chave, valor, descricao) VALUES
('mp_public_key', 'APP_USR-9784e097-6b3c-4f1a-8f88-29857b922799', 'Chave pública do Mercado Pago'),
('mp_access_token', 'APP_USR-6712424600827825-062814-fe3172b5cf2055e635f47317b941d810-1350257138', 'Token de acesso do Mercado Pago'),
('mp_webhook_url', 'https://damonengenharia.free.nf/public/api/webhook_mercadopago.php', 'URL do webhook para notificações'),
('mp_success_url', 'https://damonengenharia.free.nf/public/api/pagamento_sucesso.php', 'URL de retorno para pagamento aprovado'),
('mp_failure_url', 'https://damonengenharia.free.nf/public/api/pagamento_falha.php', 'URL de retorno para pagamento rejeitado'),
('mp_pending_url', 'https://damonengenharia.free.nf/public/api/pagamento_pendente.php', 'URL de retorno para pagamento pendente'),
('email_remetente', 'damon_engenharia@hotmail.com', 'Email remetente para envio dos arquivos'),
('email_nome_remetente', 'Projetos de Engenharia', 'Nome do remetente dos emails');

-- =================================================================
-- TABELA DE IMAGENS EXTRAS POR PROJETO
-- =================================================================
CREATE TABLE projeto_imagens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    projeto_id INT NOT NULL,
    imagem VARCHAR(255) NOT NULL,
    legenda VARCHAR(200) NULL,
    ordem INT DEFAULT 1,
    ativo BOOLEAN DEFAULT TRUE,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (projeto_id) REFERENCES projetos(id) ON DELETE CASCADE,
    INDEX idx_projeto_ordem (projeto_id, ordem),
    INDEX idx_ativo (ativo)
);

