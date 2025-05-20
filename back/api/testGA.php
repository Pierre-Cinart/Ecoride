<?php
require_once '../composants/autoload.php';
// Chemin vers fichier JSON
$jsonKeyFile = '../config/service-account.json'; // mets le bon chemin vers ton fichier JSON



// 1. Charger les infos du JSON
$jwt = json_decode(file_get_contents($jsonKeyFile), true);

$privateKey = $jwt['private_key'];
$clientEmail = $jwt['client_email'];
$tokenUrl = $jwt['token_uri'];

// 2. Générer le JWT
$header = base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));

$now = time();
$payload = base64_encode(json_encode([
    'iss' => $clientEmail,
    'scope' => 'https://www.googleapis.com/auth/analytics.readonly',
    'aud' => $tokenUrl,
    'exp' => $now + 3600,
    'iat' => $now
]));

$signatureInput = "$header.$payload";

// Signature RSA
openssl_sign($signatureInput, $signature, $privateKey, 'sha256WithRSAEncryption');
$jwtAssertion = "$signatureInput." . base64_encode($signature);

// 3. Requête POST vers token_uri
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $tokenUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
    'assertion' => $jwtAssertion
]));
$response = json_decode(curl_exec($ch), true);
curl_close($ch);

if (!isset($response['access_token'])) {
    die("❌ Erreur récupération token : " . json_encode($response));
}

$accessToken = $response['access_token'];

// 4. Requête API Google Analytics Data (ex: sessions par jour)
$apiUrl = "https://analyticsdata.googleapis.com/v1beta/properties/$PROPRIETY_ID:runReport";

$reportRequest = [
    'dateRanges' => [['startDate' => '7daysAgo', 'endDate' => 'today']],
    'dimensions' => [['name' => 'date']],
    'metrics' => [['name' => 'activeUsers']]
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $accessToken,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($reportRequest));

$analyticsResponse = curl_exec($ch);
curl_close($ch);

// 5. Affichage
$data = json_decode($analyticsResponse, true);

if (isset($data['rows'])) {
    echo "<h2>Utilisateurs actifs par jour (7 derniers jours)</h2><ul>";
    foreach ($data['rows'] as $row) {
        echo "<li>{$row['dimensionValues'][0]['value']} : {$row['metricValues'][0]['value']}</li>";
    }
    echo "</ul>";
} else {
    echo "<pre>";
    print_r($data);
    echo "</pre>";
}
?>
