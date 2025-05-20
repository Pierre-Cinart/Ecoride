<?php
// API qui renvoie le nombre de trajets par jour pour une semaine donnée

header('Content-Type: application/json');

// Chargement des composants (inclut $pdo)
require_once '../composants/autoload.php';
checkAccess(['Admin']);

// Lecture et validation du paramètre GET : dates (JSON encodé)
$datesJson = $_GET['dates'] ?? null;

$datesArray = null;
if ($datesJson) {
    $decoded = json_decode($datesJson, true);
    if (is_array($decoded) && count($decoded) === 7) {
        $datesArray = $decoded;
    }
}

// Si le paramètre est mal formé ou absent, renvoyer 0 partout
if (!is_array($datesArray)) {
    echo json_encode([
        'labels' => ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
        'data' => array_fill(0, 7, 0)
    ]);
    exit;
}

// On ne garde que les jours passés ou aujourd’hui (on ignore les jours futurs = null)
$validDates = array_filter($datesArray);

// Si tous les jours sont dans le futur, on renvoie 0 partout
if (empty($validDates)) {
    echo json_encode([
        'labels' => ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
        'data' => array_fill(0, 7, 0)
    ]);
    exit;
}

try {
    // Préparation de la requête SQL : nombre de trajets par date
    $placeholders = implode(',', array_fill(0, count($validDates), '?'));
    $sql = "SELECT departure_date, COUNT(*) AS total
            FROM trips
            WHERE departure_date IN ($placeholders)
            GROUP BY departure_date";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($validDates);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // On transforme les résultats en tableau associatif date => total
    $map = [];
    foreach ($results as $row) {
        $map[$row['departure_date']] = (int) $row['total'];
    }

    // Construction du tableau de données dans l’ordre
    $data = [];
    foreach ($datesArray as $date) {
        $data[] = $date && isset($map[$date]) ? $map[$date] : 0;
    }

    // Labels fixes (pas besoin de date française, tu veux comme dans l’exemple)
    $labels = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];

    // Réponse JSON finale
    echo json_encode([
        'labels' => $labels,
        'data' => $data
    ]);
} catch (Exception $e) {
    // En cas d'erreur SQL ou autre
    http_response_code(500);
    echo json_encode(['labels' => ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'], 'data' => array_fill(0, 7, 0)]);
}
