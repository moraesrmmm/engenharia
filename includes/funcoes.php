<?php
/**
 * Funções utilitárias para o sistema de projetos
 */

/**
 * Converte URL do YouTube para URL de incorporação (embed)
 * 
 * @param string $url URL do YouTube
 * @return string URL de incorporação ou URL original se não for do YouTube
 */
function convertYouTubeUrl($url) {
    if (empty($url)) return '';
    
    // Padrões de URL do YouTube
    $patterns = [
        '/youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/',  // youtube.com/watch?v=ID
        '/youtu\.be\/([a-zA-Z0-9_-]+)/',              // youtu.be/ID
        '/youtube\.com\/embed\/([a-zA-Z0-9_-]+)/'     // youtube.com/embed/ID (já convertida)
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $url, $matches)) {
            return 'https://www.youtube.com/embed/' . $matches[1];
        }
    }
    
    return $url; // Retorna a URL original se não for do YouTube
}

/**
 * Formata valor monetário para exibição
 * 
 * @param float $valor Valor em reais
 * @param bool $simbolo Se deve incluir o símbolo R$
 * @return string Valor formatado
 */
function formatarMoeda($valor, $simbolo = true) {
    if (is_null($valor) || $valor === '') return '';
    
    $valorFormatado = number_format($valor, 2, ',', '.');
    return $simbolo ? "R$ {$valorFormatado}" : $valorFormatado;
}

/**
 * Formata área para exibição
 * 
 * @param float $area Área em metros quadrados
 * @return string Área formatada
 */
function formatarArea($area) {
    if (is_null($area) || $area === '') return 'Não informado';
    return number_format($area, 2, ',', '.') . ' m²';
}

/**
 * Gera slug a partir de um texto
 * 
 * @param string $texto Texto para converter
 * @return string Slug gerado
 */
function gerarSlug($texto) {
    // Remove acentos
    $texto = iconv('UTF-8', 'ASCII//TRANSLIT', $texto);
    // Converte para minúsculas
    $texto = strtolower($texto);
    // Remove caracteres especiais
    $texto = preg_replace('/[^a-z0-9\s-]/', '', $texto);
    // Substitui espaços e múltiplos hífens por um hífen
    $texto = preg_replace('/[\s-]+/', '-', $texto);
    // Remove hífens do início e fim
    $texto = trim($texto, '-');
    
    return $texto;
}

/**
 * Trunca texto mantendo palavras inteiras
 * 
 * @param string $texto Texto para truncar
 * @param int $limite Limite de caracteres
 * @param string $sufixo Sufixo a adicionar (ex: "...")
 * @return string Texto truncado
 */
function truncarTexto($texto, $limite = 150, $sufixo = '...') {
    if (strlen($texto) <= $limite) {
        return $texto;
    }
    
    $texto = substr($texto, 0, $limite);
    $ultimoEspaco = strrpos($texto, ' ');
    
    if ($ultimoEspaco !== false) {
        $texto = substr($texto, 0, $ultimoEspaco);
    }
    
    return $texto . $sufixo;
}

/**
 * Buscar andares de um projeto com seus cômodos
 */
function buscarAndaresComComodos($pdo, $projeto_id) {
    $stmt = $pdo->prepare("
        SELECT a.*, 
               COUNT(c.id) as total_comodos,
               COUNT(CASE WHEN c.tipo IN ('Quarto', 'Suíte') THEN 1 END) as quartos
        FROM andares a 
        LEFT JOIN comodos c ON a.id = c.andar_id AND c.ativo = TRUE
        WHERE a.projeto_id = ? AND a.ativo = TRUE 
        GROUP BY a.id 
        ORDER BY a.ordem, a.id
    ");
    $stmt->execute([$projeto_id]);
    $andares = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Buscar cômodos de cada andar
    foreach ($andares as &$andar) {
        $stmt = $pdo->prepare("
            SELECT * FROM comodos 
            WHERE andar_id = ? AND ativo = TRUE 
            ORDER BY tipo, nome
        ");
        $stmt->execute([$andar['id']]);
        $andar['comodos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    return $andares;
}

/**
 * Calcular estatísticas de um projeto
 */
function calcularEstatisticasProjeto($pdo, $projeto_id) {
    $stmt = $pdo->prepare("
        SELECT 
            p.area_terreno,
            p.area_construida,
            COUNT(DISTINCT a.id) as total_andares,
            COUNT(DISTINCT c.id) as total_comodos,
            COUNT(DISTINCT CASE WHEN c.tipo IN ('Quarto', 'Suíte') THEN c.id END) as total_quartos,
            COUNT(DISTINCT CASE WHEN c.tipo = 'Banheiro' THEN c.id END) as total_banheiros
        FROM projetos p
        LEFT JOIN andares a ON p.id = a.projeto_id AND a.ativo = TRUE
        LEFT JOIN comodos c ON a.id = c.andar_id AND c.ativo = TRUE
        WHERE p.id = ? AND p.ativo = TRUE
        GROUP BY p.id
    ");
    $stmt->execute([$projeto_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Tipos de cômodos disponíveis
 */
function getTiposComodos() {
    return [
        'Quarto' => 'Quarto',
        'Suíte' => 'Suíte',
        'Banheiro' => 'Banheiro',
        'Sala' => 'Sala',
        'Cozinha' => 'Cozinha',
        'Copa' => 'Copa',
        'Área de Serviço' => 'Área de Serviço',
        'Varanda' => 'Varanda',
        'Garagem' => 'Garagem',
        'Escritório' => 'Escritório',
        'Biblioteca' => 'Biblioteca',
        'Closet' => 'Closet',
        'Despensa' => 'Despensa',
        'Lavabo' => 'Lavabo',
        'Hall' => 'Hall',
        'Corredor' => 'Corredor',
        'Escada' => 'Escada',
        'Porão' => 'Porão',
        'Sótão' => 'Sótão',
        'Terraço' => 'Terraço',
        'Área Gourmet' => 'Área Gourmet',
        'Piscina' => 'Piscina',
        'Outro' => 'Outro'
    ];
}

/**
 * Nomes de andares comuns
 */
function getNomesAndares() {
    return [
        'Subsolo' => 'Subsolo',
        'Térreo' => 'Térreo',
        '1º Andar' => '1º Andar',
        '2º Andar' => '2º Andar',
        '3º Andar' => '3º Andar',
        'Cobertura' => 'Cobertura',
        'Ático' => 'Ático'
    ];
}
?>
