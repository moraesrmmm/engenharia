-- =================================================================
-- BANCO DE DADOS: Sistema de Projetos de Engenharia/Arquitetura
-- Versão: 2.0 - Estrutura Hierárquica com Andares
-- Data: 2025-06-28
-- =================================================================

-- Criar banco de dados
CREATE DATABASE IF NOT EXISTS engenharia CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE engenharia;

-- =================================================================
-- TABELA DE ADMINISTRADORES
-- =================================================================
CREATE TABLE admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Inserir usuário admin padrão (senha: admin123)
INSERT INTO admin (usuario, senha) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

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
-- TRIGGERS PARA AUTOMATIZAR ÁREA CONSTRUÍDA
-- =================================================================

-- Trigger para atualizar área construída quando andar é inserido
DELIMITER $$
CREATE TRIGGER trigger_andar_insert_area 
AFTER INSERT ON andares 
FOR EACH ROW 
BEGIN
    UPDATE projetos 
    SET area_construida = (
        SELECT COALESCE(SUM(area), 0) 
        FROM andares 
        WHERE projeto_id = NEW.projeto_id AND ativo = TRUE
    )
    WHERE id = NEW.projeto_id;
END$$

-- Trigger para atualizar área construída quando andar é atualizado
CREATE TRIGGER trigger_andar_update_area 
AFTER UPDATE ON andares 
FOR EACH ROW 
BEGIN
    UPDATE projetos 
    SET area_construida = (
        SELECT COALESCE(SUM(area), 0) 
        FROM andares 
        WHERE projeto_id = NEW.projeto_id AND ativo = TRUE
    )
    WHERE id = NEW.projeto_id;
END$$

-- Trigger para atualizar área construída quando andar é excluído
CREATE TRIGGER trigger_andar_delete_area 
AFTER DELETE ON andares 
FOR EACH ROW 
BEGIN
    UPDATE projetos 
    SET area_construida = (
        SELECT COALESCE(SUM(area), 0) 
        FROM andares 
        WHERE projeto_id = OLD.projeto_id AND ativo = TRUE
    )
    WHERE id = OLD.projeto_id;
END$$

DELIMITER ;

-- =================================================================
-- VIEWS ÚTEIS PARA CONSULTAS
-- =================================================================

-- View para estatísticas de projetos
CREATE VIEW vw_projetos_stats AS
SELECT 
    p.id,
    p.titulo,
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

-- =================================================================
-- DADOS DE EXEMPLO PARA TESTE
-- =================================================================

-- Projeto de exemplo 1
INSERT INTO projetos (titulo, descricao, largura_terreno, comprimento_terreno, area_terreno, valor_projeto, custo_mao_obra, custo_materiais, capa_imagem, destaque) VALUES
('Casa Moderna Térrea', 'Projeto de casa térrea moderna com 3 quartos, área gourmet e piscina. Design contemporâneo com linhas clean e integração com área externa.', 20.00, 30.00, 600.00, 450000.00, 180000.00, 270000.00, 'casa_moderna_1.jpg', TRUE);

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

-- Projeto de exemplo 2
INSERT INTO projetos (titulo, descricao, largura_terreno, comprimento_terreno, area_terreno, valor_projeto, custo_mao_obra, custo_materiais, capa_imagem, destaque) VALUES
('Sobrado Contemporâneo', 'Sobrado de 2 pavimentos com arquitetura contemporânea. 4 suítes, home office, área de lazer completa e acabamento de alto padrão.', 25.00, 32.00, 800.00, 750000.00, 300000.00, 450000.00, 'sobrado_contemporaneo_1.jpg', FALSE);

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

-- =================================================================
-- CONFIGURAÇÕES FINAIS
-- =================================================================

-- Otimizar tabelas
OPTIMIZE TABLE projetos, andares, comodos, projeto_imagens;

-- Exibir resumo da estrutura criada
SELECT 
    'Estrutura do banco criada com sucesso!' as status,
    (SELECT COUNT(*) FROM projetos) as total_projetos,
    (SELECT COUNT(*) FROM andares) as total_andares,
    (SELECT COUNT(*) FROM comodos) as total_comodos;

-- =================================================================
-- FIM DO SCRIPT
-- =================================================================
