<?php
session_start();
require_once '../../back/composants/db_connect.php';
require_once '../../back/composants/paginate.php';

// Fonction de formatage pour sécuriser et capitaliser les villes
function formatCity($city) {
    $city = htmlspecialchars(trim($city));
    return ucfirst(strtolower($city));
}

// Récupération des paramètres GET
$departure = isset($_GET['depart']) ? formatCity($_GET['depart']) : '';
$arrival = isset($_GET['arrivee']) ? formatCity($_GET['arrivee']) : '';
$date = $_GET['date'] ?? '';
$heure = $_GET['heure'] ?? '';
$ecoOnly = isset($_GET['ecoOnly']);
$vehicleType = $_GET['vehicleType'] ?? '';
$prix_max = $_GET['prix_max'] ?? null;
$note_min = $_GET['note_min'] ?? null;
$sort = $_GET['sort'] ?? 'date';
$similar = isset($_GET['similar']); // affichage alternatif sans contrainte

// Pagination
$itemsPerPage = 5;
$currentPage = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($currentPage - 1) * $itemsPerPage;

// Construction dynamique de la clause WHERE
$where = "
    LOWER(t.departure_city) = LOWER(:depart)
    AND LOWER(t.arrival_city) = LOWER(:arrivee)
    AND t.departure_date >= CURDATE()
    AND t.status = 'planned'
    AND t.available_seats > 0
";
$params = [
    ':depart' => $departure,
    ':arrivee' => $arrival
];

if (!$similar) {
    if (!empty($date)) {
        $where .= " AND t.departure_date = :date";
        $params[':date'] = $date;
    }
    if (!empty($heure)) {
        $where .= " AND t.departure_time >= :heure";
        $params[':heure'] = $heure;
    }
    if ($ecoOnly) {
        $where .= " AND t.is_ecological = 1";
    }
    if (!empty($vehicleType)) {
        $where .= " AND v.fuel_type = :fuel";
        $params[':fuel'] = $vehicleType;
    }
    if (!empty($prix_max)) {
        $where .= " AND t.price <= :prix";
        $params[':prix'] = $prix_max;
    }
}

// Tri des résultats
$orderBy = "t.departure_date ASC, t.departure_time ASC";
if ($sort === 'price') $orderBy = "t.price ASC";
if ($sort === 'rating') $orderBy = "driver_rating DESC";

// Compter les résultats
$countSql = "
    SELECT COUNT(*) FROM (
        SELECT t.id
        FROM trips t
        JOIN vehicles v ON t.vehicle_id = v.id
        LEFT JOIN ratings r ON r.trip_id = t.id AND r.status = 'accepted'
        WHERE $where
        GROUP BY t.id
    ) AS sub
";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalItems = $countStmt->fetchColumn();

// Requête principale avec note moyenne par voyage
$query = $pdo->prepare("
    SELECT 
        t.*, 
        u.pseudo AS driver_name, 
        v.brand, 
        v.model,
        COALESCE(AVG(r.rating), 0) AS driver_rating
    FROM trips t
    JOIN users u ON t.driver_id = u.id
    JOIN vehicles v ON t.vehicle_id = v.id
    LEFT JOIN ratings r ON r.trip_id = t.id AND r.status = 'accepted'
    WHERE $where
    GROUP BY t.id
    ORDER BY $orderBy
    LIMIT :limit OFFSET :offset
");

foreach ($params as $key => $val) {
    $query->bindValue($key, $val);
}
$query->bindValue(':limit', $itemsPerPage, PDO::PARAM_INT);
$query->bindValue(':offset', $offset, PDO::PARAM_INT);
$query->execute();
$trips = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Résultats de recherche - EcoRide</title>
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/tripList.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Lemonada:wght@300..700&display=swap" rel="stylesheet">
</head>
<body>
<?php include_once '../composants/navbar.php'; ?>

<main>
  <div class="trips-container">
    <div class="top-buttons">
      <button class="blue" onclick="window.location.href='search.php'">⬅ Nouvelle recherche</button>
    </div>

    <!-- Tri -->
    <form class="form-container" method="get" action="tripList.php">
      <input type="hidden" name="depart" value="<?= htmlspecialchars($departure) ?>">
      <input type="hidden" name="arrivee" value="<?= htmlspecialchars($arrival) ?>">
      <?php if ($similar): ?>
        <input type="hidden" name="similar" value="1">
      <?php endif; ?>
      <label for="sort">Trier par :</label>
      <select name="sort" id="sort" onchange="this.form.submit()">
        <option value="date" <?= $sort === 'date' ? 'selected' : '' ?>>Date</option>
        <option value="price" <?= $sort === 'price' ? 'selected' : '' ?>>Prix</option>
        <option value="rating" <?= $sort === 'rating' ? 'selected' : '' ?>>Note</option>
      </select>
    </form>

    <div class="trip-cards">
      <?php if (empty($trips)): ?>
        <p>Aucun trajet trouvé pour ces critères.</p>
        <form method="get" action="tripList.php">
          <input type="hidden" name="depart" value="<?= htmlspecialchars($departure) ?>">
          <input type="hidden" name="arrivee" value="<?= htmlspecialchars($arrival) ?>">
          <input type="hidden" name="similar" value="1">
          <button type="submit" class="blue">🔍 Voir les trajets similaires</button>
        </form>
      <?php else: ?>
        <?php foreach ($trips as $trip): ?>
          <div class="trip-card">
            <p><strong>Départ :</strong> <?= htmlspecialchars($trip['departure_city']) ?></p>
            <p><strong>Arrivée :</strong> <?= htmlspecialchars($trip['arrival_city']) ?></p>
            <p><strong>Date :</strong> <?= date('d/m/Y', strtotime($trip['departure_date'])) ?> à <?= substr($trip['departure_time'], 0, 5) ?></p>
            <p><strong>Conducteur :</strong> <?= htmlspecialchars($trip['driver_name']) ?> -
              <span class="stars"><?= str_repeat("★", round($trip['driver_rating'])) . str_repeat("☆", 5 - round($trip['driver_rating'])) ?></span>
              (<?= number_format($trip['driver_rating'], 1) ?>)
            </p>
            <p><strong>Véhicule :</strong> <?= htmlspecialchars($trip['brand']) ?> <?= htmlspecialchars($trip['model']) ?></p>
            <p><strong>Places disponibles :</strong> <?= (int)$trip['available_seats'] ?></p>
            <p><strong>Prix :</strong> <?= (float)$trip['price'] ?> crédits</p>
            <p><strong>Écologique :</strong> <?= $trip['is_ecological'] ? '✅' : '❌' ?></p>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php
    $queryStr = http_build_query(array_merge($_GET, ['page' => '']));
    $baseUrl = 'tripList.php?' . $queryStr;
    renderPagination($totalItems, $itemsPerPage, $currentPage, $baseUrl);
    ?>
  </div>
</main>

<?php include_once '../composants/footer.html'; ?>
</body>
</html>
