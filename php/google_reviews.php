<?php
/**
 * Google Places API — Busca de avaliações reais
 * 
 * Este arquivo busca avaliações do Google via Places API (New)
 * e salva/atualiza no banco de dados automaticamente.
 *
 * COMO USAR:
 * 1. Obtenha uma chave de API do Google Cloud Console:
 *    → https://console.cloud.google.com/apis/credentials
 * 2. Ative a "Places API (New)" no console.
 * 3. Preenha as constantes abaixo (GOOGLE_API_KEY e GOOGLE_PLACE_ID).
 * 4. Execute manualmente via navegador ou configure um cron job:
 *    Ex: curl https://seusite.com/php/google_reviews.php?run=1
 *
 * ATENÇÃO: A API do Google Places possui custos por requisição.
 * O plano gratuito inclui US$200/mês de crédito (suficiente para ~40.000 chamadas).
 * Recomendamos limitar a execução a 1x por dia via cron.
 *
 * ALTERNATIVA GRATUITA:
 * Se preferir não usar a API, cadastre as avaliações manualmente
 * pelo Painel Administrativo → seção "Adicionar nova avaliação".
 */

declare(strict_types=1);

// ============================================================================
// CONFIGURAÇÃO — Preencha com seus dados
// ============================================================================

/** Sua chave de API do Google Cloud (Places API New) */
define('GOOGLE_API_KEY', ''); // Ex: 'AIzaSyB...'

/** Place ID da Barbearia Vlad no Google Maps */
define('GOOGLE_PLACE_ID', 'ChIJX3ycVqtJJ5URnt9vEtItqiw'); // Barbearia Vlad

// ============================================================================

if (GOOGLE_API_KEY === '') {
    http_response_code(500);
    exit('Erro: GOOGLE_API_KEY não configurada. Edite php/google_reviews.php.');
}

// Proteção: só executa se chamado com ?run=1 ou via CLI
if (php_sapi_name() !== 'cli' && ($_GET['run'] ?? '') !== '1') {
    http_response_code(403);
    exit('Acesso negado. Use ?run=1 para executar.');
}

require_once __DIR__ . '/helpers.php';
$conn = require __DIR__ . '/db.php';

if (!$conn instanceof mysqli) {
    http_response_code(500);
    exit('Erro de conexão com o banco de dados.');
}

// ============================================================================
// BUSCA NA API DO GOOGLE PLACES (New)
// ============================================================================

/**
 * Busca avaliações do Google Places API (New).
 * Documentação: https://developers.google.com/maps/documentation/places/web-service/place-details
 */
function fetchGoogleReviews(string $placeId, string $apiKey): array
{
    $url = 'https://places.googleapis.com/v1/places/' . urlencode($placeId);

    $headers = [
        'Content-Type: application/json',
        'X-Goog-Api-Key: ' . $apiKey,
        'X-Goog-FieldMask: reviews',
    ];

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);

    $response = curl_exec($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($response === false || $curlError !== '') {
        return ['ok' => false, 'error' => 'Erro cURL: ' . $curlError, 'reviews' => []];
    }

    if ($httpCode !== 200) {
        return ['ok' => false, 'error' => "HTTP {$httpCode}: {$response}", 'reviews' => []];
    }

    $data = json_decode((string) $response, true);

    if (!is_array($data) || !isset($data['reviews'])) {
        return ['ok' => false, 'error' => 'Resposta sem avaliações.', 'reviews' => []];
    }

    $reviews = [];
    foreach ($data['reviews'] as $review) {
        $authorName = $review['authorAttribution']['displayName'] ?? 'Cliente';
        $rating = (int) ($review['rating'] ?? 5);
        $text = $review['originalText']['text'] ?? ($review['text']['text'] ?? '');
        $photoUri = $review['authorAttribution']['photoUri'] ?? '';

        if (trim($text) === '') {
            continue; // Ignora avaliações sem texto
        }

        $reviews[] = [
            'client_name' => $authorName,
            'quote' => $text,
            'rating' => $rating,
            'photo_url' => $photoUri,
        ];
    }

    return ['ok' => true, 'error' => '', 'reviews' => $reviews];
}

/**
 * Salva as avaliações do Google no banco, marcadas com source='google'.
 * Avaliações existentes do Google são substituídas; avaliações manuais são preservadas.
 */
function saveGoogleReviews(mysqli $conn, array $reviews): int
{
    // Remove avaliações anteriores do Google (mantém as manuais)
    $conn->query("DELETE FROM reviews WHERE source = 'google'");

    $saved = 0;

    // Descobre o próximo sort_order
    $maxOrder = 0;
    $result = $conn->query('SELECT COALESCE(MAX(sort_order), 0) AS max_order FROM reviews');
    if ($result instanceof mysqli_result) {
        $maxOrder = (int) ($result->fetch_assoc()['max_order'] ?? 0);
        $result->free();
    }

    // Descobre o próximo ID
    $nextId = 1;
    $idResult = $conn->query('SELECT COALESCE(MAX(id), 0) + 1 AS next_id FROM reviews');
    if ($idResult instanceof mysqli_result) {
        $nextId = (int) ($idResult->fetch_assoc()['next_id'] ?? 1);
        $idResult->free();
    }

    $stmt = $conn->prepare(
        'INSERT INTO reviews (id, client_name, quote, rating, photo_path, sort_order, is_active, source)
         VALUES (?, ?, ?, ?, ?, ?, 1, \'google\')'
    );

    if (!$stmt) {
        return 0;
    }

    foreach ($reviews as $review) {
        $order = ++$maxOrder;
        $clientName = (string) $review['client_name'];
        $quote = (string) $review['quote'];
        $rating = (int) $review['rating'];
        $photoPath = !empty($review['photo_url']) ? (string) $review['photo_url'] : 'img/pessoa1.jpg';

        $stmt->bind_param('issisi', $nextId, $clientName, $quote, $rating, $photoPath, $order);

        if ($stmt->execute()) {
            $saved++;
            $nextId++;
        }
    }

    $stmt->close();
    return $saved;
}

// ============================================================================
// EXECUÇÃO
// ============================================================================

echo "Buscando avaliações do Google Places...\n";

$result = fetchGoogleReviews(GOOGLE_PLACE_ID, GOOGLE_API_KEY);

if (!$result['ok']) {
    echo "ERRO: " . $result['error'] . "\n";
    exit(1);
}

$count = count($result['reviews']);
echo "Encontradas {$count} avaliação(ões) com texto.\n";

if ($count === 0) {
    echo "Nenhuma avaliação para salvar.\n";
    exit(0);
}

$saved = saveGoogleReviews($conn, $result['reviews']);
echo "Salvas {$saved} avaliação(ões) no banco de dados.\n";
echo "Concluído!\n";
