-- TABELA DE ADMINISTRADORES
CREATE TABLE admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL
);

-- TABELA DE PROJETOS
CREATE TABLE projetos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(100) NOT NULL,
    descricao TEXT,

    largura DECIMAL(5,2),
    comprimento DECIMAL(5,2),
    largura_comprimento VARCHAR(50),
    area DECIMAL(6,2),

    preco_total DECIMAL(10,2),
    custo_mao_obra DECIMAL(10,2),
    custo_materiais DECIMAL(10,2),

    video_url VARCHAR(255),
    capa_imagem VARCHAR(255) NOT NULL,

    destaque BOOLEAN DEFAULT FALSE,
    ativo BOOLEAN DEFAULT TRUE,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- TABELA DE IMAGENS EXTRAS POR PROJETO
CREATE TABLE projeto_imagens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    projeto_id INT NOT NULL,
    imagem VARCHAR(255) NOT NULL,
    ativo BOOLEAN DEFAULT TRUE,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (projeto_id) REFERENCES projetos(id) ON DELETE CASCADE
);

-- TABELA DE COMODOS POR PROJETO
CREATE TABLE comodos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    projeto_id INT NOT NULL,
    tipo VARCHAR(50) NOT NULL,         -- Ex: 'Quarto', 'Sala', 'Garagem', 'Área Gourmet'
    nome VARCHAR(100),                 -- Ex: 'Quarto Casal', 'Banheiro Social'
    largura DECIMAL(5,2),
    comprimento DECIMAL(5,2),
    observacoes TEXT,
    ativo BOOLEAN DEFAULT TRUE,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (projeto_id) REFERENCES projetos(id) ON DELETE CASCADE
);

-- Adicionar campo destaque caso já exista a tabela
ALTER TABLE projetos ADD COLUMN IF NOT EXISTS destaque BOOLEAN DEFAULT FALSE;
