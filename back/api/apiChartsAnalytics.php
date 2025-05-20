<?php
// API pour récupérer les visiteurs Google Analytics 4 et les renvoyer à Chart.js

// On déclare que la réponse sera au format JSON
header('Content-Type: application/json');

// Chargement automatique des dépendances, protection d'accès (Admin uniquement)
require_once '../composants/autoload.php';
checkAccess(['Admin']); // Seuls les administrateurs peuvent accéder à cette API

// Chargement de la configuration Analytics (ID propriété, chemin vers la clé, etc.)
require_once __DIR__ . '/../config/configAnalytics.php';

// On vérifie l'existence du fichier JSON de la clé de service
$serviceAccountFile = __DIR__ . '/../config/service-account.json';
if (!file_exists($serviceAccountFile)) {
    http_response_code(500);
    echo json_encode(['error' => 'Clé de service introuvable']);
    exit;
}

// Lecture du fichier JSON contenant les identifiants du service (compte Google Cloud)
$credentials = json_decode(file_get_contents($serviceAccountFile), true);
$privateKey = $credentials['private_key'];     // Clé privée pour signer le token
$clientEmail = $credentials['client_email'];   // Email du compte de service
$tokenUrl = $credentials['token_uri'];         // URL pour obtenir un token OAuth

// Lecture des paramètres GET transmis par le front (type + liste des dates)
$type = $_GET['type'] ?? null;
$datesJson = $_GET['dates'] ?? null;

// On vérifie que le type demandé est bien "visitors"
if ($type !== 'visitors') {
    http_response_code(400);
    echo json_encode(['error' => 'Type de statistique non supporté']);
    exit;
}

// On vérifie que les dates ont bien été transmises
if (!$datesJson) {
    http_response_code(400);
    echo json_encode(['error' => 'Aucune date transmise']);
    exit;
}

// Décodage des dates (liste de 7 jours de la semaine) depuis le JSON transmis
$datesArray = json_decode($datesJson, true);  // Exemple : ['2025-05-19', '2025-05-20', ..., null]
$validDates = array_filter($datesArray);      // On retire les valeurs null (jours futurs)

// Si aucune date valide n'est présente, on arrête (inutile d'interroger Google)
if (empty($validDates)) {
    http_response_code(204);
    echo json_encode(['labels' => [], 'data' => []]);
    exit;
}

// Calcul de la plage minimale et maximale pour la requête Analytics
$startDate = min($validDates);
$endDate = max($validDates);

// ----------------------
// Authentification avec Google (OAuth 2.0 via JWT signé)
// ----------------------

// Création de l'en-tête du token (JWT)
$header = base64_encode(json_encode([
    'alg' => 'RS256',
    'typ' => 'JWT'
]));

// Création de la charge utile du token (payload) avec les infos obligatoires
$now = time();
$payload = base64_encode(json_encode([
    'iss' => $clientEmail,                           // émetteur = notre compte de service
    'scope' => 'https://www.googleapis.com/auth/analytics.readonly', // portée des accès
    'aud' => $tokenUrl,                              // audience = serveur Google
    'exp' => $now + 3600,                            // expiration (1 heure)
    'iat' => $now                                     // moment de création
]));

// Fusion header.payload à signer
$toSign = "$header.$payload";

// Signature du token avec la clé privée (algorithme RSA)
openssl_sign($toSign, $signature, $privateKey, 'sha256WithRSAEncryption');

// Assemblage du token final : header.payload.signature
$jwt = "$toSign." . base64_encode($signature);

// ----------------------
// Envoi du token à Google pour obtenir un token OAuth valide (access_token)
// ----------------------

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $tokenUrl); // URL vers le serveur de token OAuth
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // On veut récupérer la réponse dans une variable
curl_setopt($ch, CURLOPT_POST, true); // Méthode POST
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer', // type de grant OAuth spécifique
    'assertion' => $jwt // notre jeton signé
]));

// Exécution de la requête pour obtenir le token d'accès
$tokenResponse = json_decode(curl_exec($ch), true);
curl_close($ch);

// Vérification que le token a bien été récupéré
if (!isset($tokenResponse['access_token'])) {
    http_response_code(500);
    echo json_encode(['error' => 'Échec récupération token']);
    exit;
}

$accessToken = $tokenResponse['access_token']; // Ce token permet d’accéder à l’API Google Analytics

// ----------------------
// Construction de la requête vers Google Analytics Data API v1beta
// ----------------------

$gaRequest = [
    'dateRanges' => [[
        'startDate' => $startDate,
        'endDate' => $endDate
    ]],
    'dimensions' => [[ 'name' => 'date' ]],
    'metrics' => [[ 'name' => 'activeUsers' ]]
];

// Appel à l'API Google Analytics avec les bons paramètres
$apiUrl = "https://analyticsdata.googleapis.com/v1beta/properties/{$PROPRIETY_ID}:runReport";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $accessToken,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($gaRequest));

$gaResponse = curl_exec($ch);
curl_close($ch);

$gaData = json_decode($gaResponse, true);

// ----------------------
// Traitement des données reçues
// ----------------------

// On construit une map (tableau associatif) date → nb de visiteurs
$dataMap = [];
if (isset($gaData['rows'])) {
    foreach ($gaData['rows'] as $row) {
        // Format brut reçu : "20240520"
        $rawDate = $row['dimensionValues'][0]['value'];
        $formatted = DateTime::createFromFormat('Ymd', $rawDate)->format('Y-m-d');

        $dataMap[$formatted] = intval($row['metricValues'][0]['value']);
    }
}

// On initialise deux tableaux pour Chart.js
$labels = [];
$data = [];

// Création d'un formateur de date française (ex: "Mar. 21 mai")
$formatter = new IntlDateFormatter(
    'fr_FR',                        // locale française
    IntlDateFormatter::FULL,       // style long (nom du jour)
    IntlDateFormatter::NONE,       // pas d'heure
    'Europe/Paris',                // fuseau horaire
    IntlDateFormatter::GREGORIAN,  // calendrier standard
    'EEE d MMM'                    // format court personnalisé
);

// On construit les 7 jours de la semaine, y compris ceux où il n’y a pas de données
foreach ($datesArray as $date) {
    if (!$date) {
        // Jour futur (ex: jeudi alors qu'on est mardi)
        $labels[] = '';
        $data[] = 0;
    } else {
        $dateObj = new DateTime($date);
        $labels[] = ucfirst($formatter->format($dateObj)); // Exemple : "Lun. 20 mai"
        $data[] = $dataMap[$date] ?? 0; // 0 si pas de données trouvées
    }
}

// Réponse finale au format JSON utilisable par Chart.js
echo json_encode([
    'labels' => $labels,
    'data' => $data
]);
