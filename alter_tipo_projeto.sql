-- Adicionar campo tipo_projeto na tabela projetos
ALTER TABLE projetos 
ADD COLUMN tipo_projeto ENUM(
    'Casa Térrea',
    'Sobrado',
    'Casa com Piscina',
    'Casa de Campo',
    'Casa de Praia',
    'Casa Geminada',
    'Casa Duplex',
    'Casa Triplex',
    'Chalé',
    'Residência Moderna',
    'Residência Clássica',
    'Residência Minimalista',
    'Casa Container',
    'Casa Sustentável',
    'Casa Pré-Fabricada',
    'Mansão',
    'Casa com Edícula',
    'Casa com Mezanino',
    'Casa com Loft',
    'Casa Comercial',
    'Kitnet',
    'Studio',
    'Apartamento',
    'Cobertura',
    'Prédio Residencial',
    'Prédio Comercial',
    'Galpão',
    'Barracão',
    'Escritório',
    'Loja',
    'Consultório',
    'Clínica',
    'Restaurante',
    'Cafeteria',
    'Academia',
    'Salão de Beleza',
    'Oficina',
    'Depósito',
    'Armazém',
    'Outro'
) DEFAULT 'Casa Térrea' AFTER descricao;

-- Comentário da alteração
-- Este campo permite categorizar os projetos por tipo de construção
-- facilitando a busca e organização dos projetos no sistema
