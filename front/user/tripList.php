<?php
require_once '../composants/autoload.php';
require_once '../../back/composants/db_connect.php';
require_once '../../back/composants/paginate.php';

// Marque la page active dans la navigation
$_SESSION['navSelected'] = 'search';

/**
 * Sécurisation et normalisation d'une ville
 */
function formatCity($city) {
    $city = htmlspecialchars(trim($city));
    return ucfirst(strtolower($city));
}

// Récupération des paramètres GET
$departure = isset($_GET['depart']) && $_GET['depart'] !== '' ? formatCity($_GET['depart']) : null;
$arrival   = isset($_GET['arrivee']) && $_GET['arrivee'] !== '' ? formatCity($_GET['arrivee']) : null;
$showAll   = isset($_GET['all']) && $_GET['all'] === '1';
$similar   = isset($_GET['similar']);

// Pagination
$itemsPerPage = 5;
$currentPage  = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset       = ($currentPage - 1) * $itemsPerPage;

// Construction dynamique de la clause WHERE
$where   = "t.status = 'planned' AND t.available_seats > 0 AND t.departure_date >= CURDATE()";
$params  = [];

// Si on n'affiche pas tous les trajets, on filtre
if (!$showAll) {
    if ($departure) {
        $where .= " AND LOWER(t.departure_city) = LOWER(:depart)";
        $params[':depart'] = $departure;
    }
    if ($arrival) {
        $where .= " AND LOWER(t.arrival_city) = LOWER(:arrivee)";
        $params[':arrivee'] = $arrival;
    }
}

// Comptage total pour pagination
$countSql = "SELECT COUNT(*) FROM trips t WHERE $where";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalItems = (int)$countStmt->fetchColumn();

// Requête principale avec jointures et moyenne des notes
$sql = <<<SQL
SELECT
    t.*,
    u.pseudo AS driver_name,
    v.brand,
    v.model,
    (
        SELECT ROUND(AVG(r.rating),1)
        FROM ratings r
        WHERE r.trip_id = t.id AND r.status = 'accepted'
    ) AS driver_rating
FROM trips t
JOIN users u     ON t.driver_id = u.id
JOIN vehicles v  ON t.vehicle_id = v.id
WHERE $where
ORDER BY t.departure_date ASC, t.departure_time ASC
LIMIT :limit OFFSET :offset;
SQL;

$stmt = $pdo->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit',  $itemsPerPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset,       PDO::PARAM_INT);
$stmt->execute();
$trips = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
      <button class="yellow" onclick="window.location.href='tripList.php?all=1'">🔁 Voir tous les trajets</button>
    </div>

    <!-- Formulaire de recherche rapide -->
    <form class="form-container" method="get" action="tripList.php">
      <input type="text" name="depart" id="depart" placeholder="Ville de départ" list="depart-list"
             value="<?= htmlspecialchars($departure ?? '') ?>">
      <datalist id="depart-list"></datalist>

      <input type="text" name="arrivee" id="arrivee" placeholder="Ville d’arrivée" list="arrivee-list"
             value="<?= htmlspecialchars($arrival ?? '') ?>">
      <datalist id="arrivee-list"></datalist>

      <button type="submit" class="green">🔍 Rechercher</button>
    </form>

    <!-- Résultats -->
    <div class="trip-cards">
      <?php if (empty($trips)): ?>
        <p>Aucun trajet trouvé.</p>

        <?php if (!$similar && ($departure || $arrival)): ?>
          <!-- Proposition de recherche similaire -->
          <form method="get" action="tripList.php">
            <?php if ($departure): ?>
              <input type="hidden" name="depart" value="<?= htmlspecialchars($departure) ?>">
            <?php endif; ?>
            <?php if ($arrival): ?>
              <input type="hidden" name="arrivee" value="<?= htmlspecialchars($arrival) ?>">
            <?php endif; ?>
            <input type="hidden" name="similar" value="1">
            <button class="blue">🔁 Rechercher d'autres trajets similaires</button>
          </form>
        <?php endif; ?>

      <?php else: ?>
        <?php foreach ($trips as $trip): ?>
          <div class="trip-card">
            <p><strong>Départ :</strong> <?= htmlspecialchars($trip['departure_city']) ?></p>
            <p><strong>Arrivée :</strong> <?= htmlspecialchars($trip['arrival_city']) ?></p>
            <p><strong>Date :</strong> <?= date('d/m/Y', strtotime($trip['departure_date'])) ?>
              à <?= substr($trip['departure_time'], 0, 5) ?></p>
            <p><strong>Conducteur :</strong> <?= htmlspecialchars($trip['driver_name']) ?>
              <span class="stars">
                <?= str_repeat("★", floor($trip['driver_rating'] ?? 0))
                   . str_repeat("☆", 5 - floor($trip['driver_rating'] ?? 0)) ?>
              </span>
              (<?= number_format($trip['driver_rating'] ?? 0, 1) ?>)
            </p>
            <p><strong>Véhicule :</strong> <?= htmlspecialchars($trip['brand']) ?>
              <?= htmlspecialchars($trip['model']) ?></p>
            <p><strong>Places :</strong> <?= (int)$trip['available_seats'] ?></p>
            <p><strong>Prix :</strong> <?= (float)$trip['price'] ?> crédits</p>
            <p><strong>Écologique :</strong> <?= $trip['is_ecological'] ? '✅' : '❌' ?></p>

            <?php if (isset($_SESSION['user']) &&
                      ($_SESSION['user'] instanceof SimpleUser || $_SESSION['user'] instanceof Driver)): ?>
              <form method="post" action="reserv.php">
                <input type="hidden" name="trip_id" value="<?= $trip['id'] ?>">
                <button class="blue">Réserver ce trajet</button>
              </form>
            <?php else: ?>
              <div class="reservation-warning">
                <p>🔒 Vous devez être connecté pour réserver</p>
                <form method="post" action="../../back/pendingTrip.php">
                  <input type="hidden" name="trip_id" value="<?= $trip['id'] ?>">
                  <input type="hidden" name="location" value="connect">
                  <button class="blue">Se connecter</button>
                </form>
                <form method="post" action="../../back/pendingTrip.php">
                  <input type="hidden" name="trip_id" value="<?= $trip['id'] ?>">
                  <input type="hidden" name="location" value="register">
                  <button class="blue">Créer un compte</button>
                </form>
              </div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php
      $paramsGET = $_GET;
      unset($paramsGET['page']);
      $queryStr = http_build_query($paramsGET);
      $baseUrl = 'tripList.php' . ($queryStr ? '?' . $queryStr : '');
      renderPagination($totalItems, $itemsPerPage, $currentPage, $baseUrl);
    ?>
  </div>
</main>

<?php include_once '../composants/footer.php'; ?>

<!-- GeoAPI pour autocompletion des villes -->
<script src="../js/geoApi.js"></script>

</body>
</html>
