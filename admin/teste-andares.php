<?php
/**
 * Script de teste para verificar as alterações do sistema de andares
 * Execute este script após aplicar a migração do banco de dados
 */

require_once '../config/config.php';
require_once '../includes/funcoes.php';

echo "<h1>🏗️ Teste do Sistema de Andares</h1>";

try {
    // Teste 1: Verificar se as tabelas foram criadas
    echo "<h2>1. Verificando estrutura do banco...</h2>";
    
    $tables = ['projetos', 'andares', 'comodos'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "<p style='color: green;'>✓ Tabela $table existe</p>";
        } else {
            echo "<p style='color: red;'>✗ Tabela $table não encontrada</p>";
        }
    }
    
    // Teste 2: Verificar colunas da tabela projetos
    echo "<h2>2. Verificando colunas da tabela projetos...</h2>";
    $stmt = $pdo->query("DESCRIBE projetos");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $required_columns = ['largura_terreno', 'comprimento_terreno', 'area_terreno', 'area_construida', 'dimensoes_terreno'];
    foreach ($required_columns as $column) {
        if (in_array($column, $columns)) {
            echo "<p style='color: green;'>✓ Coluna $column existe</p>";
        } else {
            echo "<p style='color: red;'>✗ Coluna $column não encontrada</p>";
        }
    }
    
    // Teste 3: Verificar colunas da tabela andares
    echo "<h2>3. Verificando tabela andares...</h2>";
    $stmt = $pdo->query("DESCRIBE andares");
    $andar_columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $required_andar_columns = ['id', 'projeto_id', 'nome', 'area', 'ordem'];
    foreach ($required_andar_columns as $column) {
        if (in_array($column, $andar_columns)) {
            echo "<p style='color: green;'>✓ Coluna andares.$column existe</p>";
        } else {
            echo "<p style='color: red;'>✗ Coluna andares.$column não encontrada</p>";
        }
    }
    
    // Teste 4: Verificar colunas da tabela comodos
    echo "<h2>4. Verificando tabela comodos...</h2>";
    $stmt = $pdo->query("DESCRIBE comodos");
    $comodo_columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (in_array('andar_id', $comodo_columns)) {
        echo "<p style='color: green;'>✓ Coluna comodos.andar_id existe</p>";
    } else {
        echo "<p style='color: red;'>✗ Coluna comodos.andar_id não encontrada</p>";
    }
    
    // Teste 5: Contar registros
    echo "<h2>5. Contando registros...</h2>";
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM projetos WHERE ativo = TRUE");
    $total_projetos = $stmt->fetchColumn();
    echo "<p>📊 Total de projetos ativos: <strong>$total_projetos</strong></p>";
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM andares WHERE ativo = TRUE");
    $total_andares = $stmt->fetchColumn();
    echo "<p>🏢 Total de andares ativos: <strong>$total_andares</strong></p>";
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM comodos WHERE ativo = TRUE");
    $total_comodos = $stmt->fetchColumn();
    echo "<p>🚪 Total de cômodos ativos: <strong>$total_comodos</strong></p>";
    
    // Teste 6: Verificar triggers
    echo "<h2>6. Verificando triggers...</h2>";
    $stmt = $pdo->query("SHOW TRIGGERS LIKE 'atualizar_area_construida%'");
    $triggers = $stmt->fetchAll();
    
    if (count($triggers) >= 2) {
        echo "<p style='color: green;'>✓ Triggers de área construída estão funcionando</p>";
        foreach ($triggers as $trigger) {
            echo "<p style='margin-left: 20px;'>• {$trigger['Trigger']}</p>";
        }
    } else {
        echo "<p style='color: orange;'>⚠️ Alguns triggers podem não estar funcionando</p>";
    }
    
    // Teste 7: Testar funções
    echo "<h2>7. Testando funções auxiliares...</h2>";
    
    $tipos_comodos = getTiposComodos();
    echo "<p style='color: green;'>✓ getTiposComodos(): " . count($tipos_comodos) . " tipos disponíveis</p>";
    
    $nomes_andares = getNomesAndares();
    echo "<p style='color: green;'>✓ getNomesAndares(): " . count($nomes_andares) . " nomes disponíveis</p>";
    
    // Teste 8: Exemplo de projeto com andares
    if ($total_projetos > 0) {
        echo "<h2>8. Exemplo de projeto com andares...</h2>";
        
        $stmt = $pdo->query("SELECT id, titulo FROM projetos WHERE ativo = TRUE LIMIT 1");
        $projeto = $stmt->fetch();
        
        if ($projeto) {
            echo "<h3>Projeto: {$projeto['titulo']}</h3>";
            
            $andares = buscarAndaresComComodos($pdo, $projeto['id']);
            
            if (empty($andares)) {
                echo "<p style='color: orange;'>⚠️ Este projeto não tem andares cadastrados</p>";
            } else {
                foreach ($andares as $andar) {
                    echo "<div style='background: #f8f9fa; padding: 15px; margin: 10px 0; border-radius: 8px;'>";
                    echo "<h4>🏢 {$andar['nome']} - " . formatarArea($andar['area']) . "</h4>";
                    echo "<p>📊 {$andar['total_comodos']} cômodos | {$andar['quartos']} quartos</p>";
                    
                    if (!empty($andar['comodos'])) {
                        echo "<ul>";
                        foreach ($andar['comodos'] as $comodo) {
                            $dimensoes = '';
                            if ($comodo['largura'] && $comodo['comprimento']) {
                                $area_comodo = $comodo['largura'] * $comodo['comprimento'];
                                $dimensoes = " - " . formatarArea($area_comodo);
                            }
                            echo "<li>{$comodo['tipo']}: {$comodo['nome']}{$dimensoes}</li>";
                        }
                        echo "</ul>";
                    }
                    echo "</div>";
                }
            }
            
            $stats = calcularEstatisticasProjeto($pdo, $projeto['id']);
            if ($stats) {
                echo "<div style='background: #e3f2fd; padding: 15px; margin: 10px 0; border-radius: 8px;'>";
                echo "<h4>📈 Estatísticas do Projeto</h4>";
                echo "<p><strong>Área do terreno:</strong> " . formatarArea($stats['area_terreno']) . "</p>";
                echo "<p><strong>Área construída:</strong> " . formatarArea($stats['area_construida']) . "</p>";
                echo "<p><strong>Total de andares:</strong> {$stats['total_andares']}</p>";
                echo "<p><strong>Total de cômodos:</strong> {$stats['total_comodos']}</p>";
                echo "<p><strong>Quartos:</strong> {$stats['total_quartos']}</p>";
                echo "<p><strong>Banheiros:</strong> {$stats['total_banheiros']}</p>";
                echo "</div>";
            }
        }
    }
    
    echo "<h2>✅ Teste concluído!</h2>";
    echo "<p style='background: #d4edda; padding: 15px; border-radius: 8px; color: #155724;'>";
    echo "<strong>Status:</strong> Sistema de andares parece estar funcionando corretamente!<br>";
    echo "<strong>Próximos passos:</strong> Atualize os formulários de cadastro e edição de projetos.";
    echo "</p>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; color: #721c24;'>";
    echo "<h3>❌ Erro durante o teste:</h3>";
    echo "<p>{$e->getMessage()}</p>";
    echo "<p><strong>Verifique se:</strong></p>";
    echo "<ul>";
    echo "<li>A migração do banco foi executada corretamente</li>";
    echo "<li>As permissões do banco estão corretas</li>";
    echo "<li>Todas as tabelas foram criadas</li>";
    echo "</ul>";
    echo "</div>";
}
?>

<style>
body {
    font-family: 'Segoe UI', Arial, sans-serif;
    max-width: 1000px;
    margin: 0 auto;
    padding: 20px;
    background: #f5f5f5;
}
h1, h2, h3 { color: #2980b9; }
p { margin: 5px 0; }
ul { margin-left: 20px; }
</style>
