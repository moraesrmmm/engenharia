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
 * Formata dimensões para exibição
 * 
 * @param float $largura Largura em metros
 * @param float $comprimento Comprimento em metros
 * @return string Dimensões formatadas
 */
function formatarDimensoes($largura, $comprimento) {
    $larguraFormat = number_format($largura, 2, ',', '');
    $comprimentoFormat = number_format($comprimento, 2, ',', '');
    return "{$larguraFormat}m x {$comprimentoFormat}m";
}

/**
 * Calcula área a partir de largura e comprimento
 * 
 * @param float $largura Largura em metros
 * @param float $comprimento Comprimento em metros
 * @return float Área em metros quadrados
 */
function calcularArea($largura, $comprimento) {
    return $largura * $comprimento;
}

/**
 * Formata área para exibição
 * 
 * @param float $area Área em metros quadrados
 * @return string Área formatada
 */
function formatarArea($area) {
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
?>
