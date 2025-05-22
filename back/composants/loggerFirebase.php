<?php

require_once __DIR__ . '/../config/configAnalytics.php';

/**
 * Enregistre une action dans Firestore (logger)
 */
function logActionToFirebase(string $name, string $action, ?string $timestamp = null): bool
{
    $timestamp = $timestamp ?? date('c');

    $payload = [
        "fields" => [
            "Name" => ["stringValue" => $name],
            "Action" => ["stringValue" => $action],
            "Timestamp" => ["timestampValue" => $timestamp]
        ]
    ];

    // ✅ URL CORRECTE avec bon Project ID
    $url = "https://firestore.googleapis.com/v1/projects/ecoride-ecf/databases/(default)/documents/logs?key=AIzaSyBuSSGf_iG9Rzyilo7TG8qr-4dFEN_YWI4";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode >= 400) {
        error_log("Erreur Firebase ($httpCode) : $response");
        return false;
    }

    return true;
}

/**
 * Lit les logs depuis Firestore
 */
function readLogsFromFirebase(int $offset = 0, int $limit = 10): array
{
    // URL api
    $url = "https://firestore.googleapis.com/v1/projects/ecoride-ecf/databases/(default)/documents/logs?key=AIzaSyBuSSGf_iG9Rzyilo7TG8qr-4dFEN_YWI4";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json"
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode >= 400 || !$response) {
        error_log("Erreur récupération logs Firebase : $response");
        return [];
    }

    $json = json_decode($response, true);

    if (!isset($json['documents'])) return [];

    $documents = $json['documents'];

    usort($documents, function ($a, $b) {
        return strcmp(
            $b['fields']['Timestamp']['timestampValue'] ?? '',
            $a['fields']['Timestamp']['timestampValue'] ?? ''
        );
    });

    $documents = array_slice($documents, $offset, $limit);

    $logs = [];
    foreach ($documents as $doc) {
        $fields = $doc['fields'];
        $logs[] = [
            'Name' => $fields['Name']['stringValue'] ?? 'Inconnu',
            'Action' => $fields['Action']['stringValue'] ?? 'Non spécifiée',
            'Timestamp' => $fields['Timestamp']['timestampValue'] ?? '???'
        ];
    }

    return $logs;
}
