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
    tipo_projeto ENUM('residencial', 'comercial', 'industrial', 'misto') DEFAULT 'residencial' COMMENT 'Tipo do projeto',
    
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

-- =================================================================
-- VIEWS ÚTEIS PARA CONSULTAS
-- =================================================================

-- View para estatísticas de projetos
CREATE VIEW vw_projetos_stats AS
SELECT 
    p.id,
    p.titulo,
    p.tipo_projeto,
    p.area_terreno,
    p.area_construida,
    p.valor_projeto,
    COUNT(DISTINCT a.id) as total_andares,
    COUNT(DISTINCT c.id) as total_comodos,
    COUNT(DISTINCT CASE WHEN c.tipo IN ('Quarto', 'Suíte') THEN c.id END) as quartos,
    COUNT(DISTINCT CASE WHEN c.tipo = 'Banheiro' THEN c.id END) as banheiros,
    p.destaque,
    p.ativo,
    p.criado_em
FROM projetos p
LEFT JOIN andares a ON p.id = a.projeto_id AND a.ativo = TRUE
LEFT JOIN comodos c ON a.id = c.andar_id AND c.ativo = TRUE
WHERE p.ativo = TRUE
GROUP BY p.id;

-- View para detalhes completos de projetos
CREATE VIEW vw_projetos_detalhes AS
SELECT 
    p.*,
    COUNT(DISTINCT a.id) as total_andares,
    COUNT(DISTINCT c.id) as total_comodos,
    COUNT(DISTINCT CASE WHEN c.tipo IN ('Quarto', 'Suíte') THEN c.id END) as quartos,
    COUNT(DISTINCT CASE WHEN c.tipo = 'Banheiro' THEN c.id END) as banheiros,
    GROUP_CONCAT(DISTINCT a.nome ORDER BY a.ordem SEPARATOR ', ') as lista_andares
FROM projetos p
LEFT JOIN andares a ON p.id = a.projeto_id AND a.ativo = TRUE
LEFT JOIN comodos c ON a.id = c.andar_id AND c.ativo = TRUE
WHERE p.ativo = TRUE
GROUP BY p.id;

-- View para estatísticas de vendas
CREATE VIEW vw_vendas_stats AS
SELECT 
    v.id,
    v.projeto_id,
    p.titulo as projeto_titulo,
    p.tipo_projeto,
    v.email_cliente,
    v.nome_cliente,
    v.valor_pago,
    v.status_pagamento,
    v.status_envio,
    v.data_compra,
    v.data_aprovacao,
    v.data_envio_email,
    DATEDIFF(v.data_aprovacao, v.data_compra) as dias_para_aprovacao,
    CASE 
        WHEN v.data_envio_email IS NOT NULL THEN DATEDIFF(v.data_envio_email, v.data_aprovacao)
        ELSE NULL 
    END as dias_para_envio
FROM vendas v
INNER JOIN projetos p ON v.projeto_id = p.id;

-- =================================================================
-- DADOS DE EXEMPLO PARA TESTE
-- =================================================================

-- Projeto de exemplo 1 - Residencial
INSERT INTO projetos (titulo, descricao, tipo_projeto, largura_terreno, comprimento_terreno, area_terreno, valor_projeto, custo_mao_obra, custo_materiais, capa_imagem, destaque) VALUES
('Casa Moderna Térrea', 'Projeto de casa térrea moderna com 3 quartos, área gourmet e piscina. Design contemporâneo com linhas clean e integração com área externa.', 'residencial', 20.00, 30.00, 600.00, 450000.00, 180000.00, 270000.00, 'casa_moderna_1.jpg', TRUE);

SET @projeto1_id = LAST_INSERT_ID();

-- Andares do projeto 1
INSERT INTO andares (projeto_id, nome, area, ordem) VALUES
(@projeto1_id, 'Térreo', 180.50, 1);

SET @andar1_id = LAST_INSERT_ID();

-- Cômodos do projeto 1
INSERT INTO comodos (projeto_id, andar_id, tipo, nome, observacoes) VALUES
(@projeto1_id, @andar1_id, 'Sala de Estar', 'Sala Principal', 'Sala ampla com pé direito duplo'),
(@projeto1_id, @andar1_id, 'Sala de Jantar', 'Sala de Jantar', 'Integrada com a cozinha'),
(@projeto1_id, @andar1_id, 'Cozinha', 'Cozinha Gourmet', 'Ilha central e bancada em granito'),
(@projeto1_id, @andar1_id, 'Quarto', 'Quarto Casal', 'Suíte principal com closet'),
(@projeto1_id, @andar1_id, 'Banheiro', 'Banheiro Suíte', 'Banheiro da suíte principal'),
(@projeto1_id, @andar1_id, 'Quarto', 'Quarto 1', 'Quarto com banheiro compartilhado'),
(@projeto1_id, @andar1_id, 'Quarto', 'Quarto 2', 'Quarto com banheiro compartilhado'),
(@projeto1_id, @andar1_id, 'Banheiro', 'Banheiro Social', 'Banheiro para quartos 1 e 2'),
(@projeto1_id, @andar1_id, 'Área Gourmet', 'Área Gourmet', 'Churrasqueira e pia'),
(@projeto1_id, @andar1_id, 'Garagem', 'Garagem', 'Para 2 carros');

-- Projeto de exemplo 2 - Residencial
INSERT INTO projetos (titulo, descricao, tipo_projeto, largura_terreno, comprimento_terreno, area_terreno, valor_projeto, custo_mao_obra, custo_materiais, capa_imagem, destaque) VALUES
('Sobrado Contemporâneo', 'Sobrado de 2 pavimentos com arquitetura contemporânea. 4 suítes, home office, área de lazer completa e acabamento de alto padrão.', 'residencial', 25.00, 32.00, 800.00, 750000.00, 300000.00, 450000.00, 'sobrado_contemporaneo_1.jpg', FALSE);

SET @projeto2_id = LAST_INSERT_ID();

-- Andares do projeto 2
INSERT INTO andares (projeto_id, nome, area, ordem) VALUES
(@projeto2_id, 'Térreo', 220.00, 1),
(@projeto2_id, 'Primeiro Andar', 180.00, 2);

SET @andar2_terreo = (SELECT id FROM andares WHERE projeto_id = @projeto2_id AND ordem = 1);
SET @andar2_primeiro = (SELECT id FROM andares WHERE projeto_id = @projeto2_id AND ordem = 2);

-- Cômodos do térreo - projeto 2
INSERT INTO comodos (projeto_id, andar_id, tipo, nome, observacoes) VALUES
(@projeto2_id, @andar2_terreo, 'Hall', 'Hall de Entrada', 'Entrada principal com pé direito duplo'),
(@projeto2_id, @andar2_terreo, 'Sala de Estar', 'Living', 'Ambiente integrado'),
(@projeto2_id, @andar2_terreo, 'Sala de Jantar', 'Sala de Jantar', 'Mesa para 8 pessoas'),
(@projeto2_id, @andar2_terreo, 'Cozinha', 'Cozinha', 'Cozinha planejada'),
(@projeto2_id, @andar2_terreo, 'Despensa', 'Despensa', 'Armazenamento'),
(@projeto2_id, @andar2_terreo, 'Lavanderia', 'Lavanderia', 'Área de serviço'),
(@projeto2_id, @andar2_terreo, 'Banheiro', 'Lavabo', 'Banheiro social'),
(@projeto2_id, @andar2_terreo, 'Escritório', 'Home Office', 'Ambiente de trabalho'),
(@projeto2_id, @andar2_terreo, 'Garagem', 'Garagem', 'Para 3 carros');

-- Cômodos do primeiro andar - projeto 2
INSERT INTO comodos (projeto_id, andar_id, tipo, nome, observacoes) VALUES
(@projeto2_id, @andar2_primeiro, 'Suíte', 'Suíte Master', 'Suíte principal com closet'),
(@projeto2_id, @andar2_primeiro, 'Suíte', 'Suíte 1', 'Suíte com varanda'),
(@projeto2_id, @andar2_primeiro, 'Suíte', 'Suíte 2', 'Suíte com banheira'),
(@projeto2_id, @andar2_primeiro, 'Suíte', 'Suíte 3', 'Suíte de hóspedes'),
(@projeto2_id, @andar2_primeiro, 'Closet', 'Closet Master', 'Closet da suíte principal'),
(@projeto2_id, @andar2_primeiro, 'Varanda', 'Varanda', 'Varanda com vista para jardim');

-- Projeto de exemplo 3 - Comercial
INSERT INTO projetos (titulo, descricao, tipo_projeto, largura_terreno, comprimento_terreno, area_terreno, valor_projeto, custo_mao_obra, custo_materiais, capa_imagem, destaque) VALUES
('Escritório Corporativo', 'Projeto de escritório corporativo moderno com salas de reunião, open space e área de convivência. Ideal para empresas de tecnologia.', 'comercial', 30.00, 40.00, 1200.00, 850000.00, 340000.00, 510000.00, 'escritorio_corporativo_1.jpg', FALSE);

SET @projeto3_id = LAST_INSERT_ID();

-- Andares do projeto 3
INSERT INTO andares (projeto_id, nome, area, ordem) VALUES
(@projeto3_id, 'Térreo', 400.00, 1),
(@projeto3_id, 'Primeiro Andar', 380.00, 2);

SET @andar3_terreo = (SELECT id FROM andares WHERE projeto_id = @projeto3_id AND ordem = 1);
SET @andar3_primeiro = (SELECT id FROM andares WHERE projeto_id = @projeto3_id AND ordem = 2);

-- Cômodos do térreo - projeto 3
INSERT INTO comodos (projeto_id, andar_id, tipo, nome, observacoes) VALUES
(@projeto3_id, @andar3_terreo, 'Recepção', 'Hall de Entrada', 'Recepção com balcão de atendimento'),
(@projeto3_id, @andar3_terreo, 'Sala de Reunião', 'Sala de Reunião 1', 'Para 8 pessoas'),
(@projeto3_id, @andar3_terreo, 'Sala de Reunião', 'Sala de Reunião 2', 'Para 12 pessoas'),
(@projeto3_id, @andar3_terreo, 'Open Space', 'Área de Trabalho', 'Espaço colaborativo'),
(@projeto3_id, @andar3_terreo, 'Copa', 'Copa', 'Área de descanso'),
(@projeto3_id, @andar3_terreo, 'Banheiro', 'Banheiro Masculino', 'Banheiro funcionários'),
(@projeto3_id, @andar3_terreo, 'Banheiro', 'Banheiro Feminino', 'Banheiro funcionárias'),
(@projeto3_id, @andar3_terreo, 'Depósito', 'Depósito', 'Armazenamento geral');

-- Cômodos do primeiro andar - projeto 3
INSERT INTO comodos (projeto_id, andar_id, tipo, nome, observacoes) VALUES
(@projeto3_id, @andar3_primeiro, 'Diretoria', 'Sala da Diretoria', 'Sala executiva'),
(@projeto3_id, @andar3_primeiro, 'Sala de Reunião', 'Sala de Reunião Executiva', 'Para diretoria'),
(@projeto3_id, @andar3_primeiro, 'Open Space', 'Área de Desenvolvimento', 'Equipe de desenvolvimento'),
(@projeto3_id, @andar3_primeiro, 'Sala de Treinamento', 'Sala de Treinamento', 'Para capacitações'),
(@projeto3_id, @andar3_primeiro, 'Copa', 'Copa Executiva', 'Área de descanso diretoria'),
(@projeto3_id, @andar3_primeiro, 'Banheiro', 'Banheiro Executivo', 'Banheiro da diretoria');

-- =================================================================
-- CONFIGURAÇÕES FINAIS
-- =================================================================

