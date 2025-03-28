<?php
// php/src/api_jogos.php

// Define o tipo de conteúdo como JSON
header('Content-Type: application/json');

// IDs e URLs (poderiam vir de um arquivo de configuração)
$campeonatoId = 10; // ID do Brasileirão
$nodeCacheUrlBase = getenv('NODEJS_CACHE_URL') ?: 'http://nodejs-cache:3000';

// Pega o número da rodada do parâmetro GET, com valor padrão 1
$rodadaNumero = isset($_GET['rodada']) ? (int)$_GET['rodada'] : 1;
if ($rodadaNumero < 1) {
    $rodadaNumero = 1; // Garante mínimo 1
}

// Monta a URL do serviço Node.js para a rodada específica
$urlJogosRodada = $nodeCacheUrlBase . "/api/cache/campeonatos/{$campeonatoId}/rodada/{$rodadaNumero}";

// Função para buscar JSON (similar à do index.php, mas retorna erro JSON)
function fetchJsonApi($url) {
    $context = stream_context_create(['http' => ['timeout' => 5]]);
    $json = @file_get_contents($url, false, $context);
    if ($json === false) {
        http_response_code(503); // Service Unavailable
        return json_encode(['error' => true, 'message' => "Não foi possível conectar ao serviço de cache ($url)."]);
    }
    // Verifica se o JSON decodificado é válido antes de retornar
    $data = json_decode($json, true);
     if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(502); // Bad Gateway
        error_log("Erro ao decodificar JSON da API de cache: " . $url . " - Erro: " . json_last_error_msg());
        return json_encode(['error' => true, 'message' => "Resposta inválida do serviço de cache."]);
    }
     // Verifica se a própria API de cache retornou um erro
     if (isset($data['error'])) {
         http_response_code(500); // Internal Server Error (ou outro código apropriado)
         error_log("Erro retornado pela API de cache ($url): " . $data['error']);
         // Retorna o erro encapsulado pela API de cache
         return json_encode($data);
     }

    return $json; // Retorna o JSON original se tudo estiver OK
}

// Busca os dados e retorna o JSON
echo fetchJsonApi($urlJogosRodada);

?>