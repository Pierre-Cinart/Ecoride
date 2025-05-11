<?php
// === Chargement des composants nécessaires ===
require_once '../composants/autoload.php';                         // Chargement des classes utilisateurs + session
require_once '../../back/composants/db_connect.php';              // Connexion à la base de données
require_once '../../back/composants/checkAccess.php';             // Contrôle d'accès
require_once '../../back/composants/paginate.php';                // Fonction de pagination

checkAccess(['SimpleUser', 'Driver']);                            // Protection d'accès

$_SESSION['navSelected'] = 'account';

$user = $_SESSION['user'];
$userId = $user->getId();
$isDriver = $user instanceof Driver;

// === PAGINATION CONFIGURATION ===
$itemsPerPage = 2;
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $itemsPerPage;

// === RÉCUPÉRATION DE TOUS LES TRAJETS TERMINÉS (passager OU conducteur) ===
$sqlCount = "
    SELECT COUNT(*) FROM (
        SELECT t.id FROM trips t
        JOIN trip_participants tp ON tp.trip_id = t.id
        WHERE tp.user_id = :uid AND tp.confirmed = 1 AND t.status = 'completed'
        UNION
        SELECT t2.id FROM trips t2
        WHERE t2.driver_id = :uid AND t2.status = 'completed'
    ) AS all_trips
";
$stmtCount = $pdo->prepare($sqlCount);
$stmtCount->execute([':uid' => $userId]);
$totalTrips = $stmtCount->fetchColumn();

// === RÉCUPÉRATION DES TRAJETS PAGINÉS ===
$sql = "
    SELECT t.*, u.pseudo AS driver_name, 'passenger' AS role
    FROM trips t
    JOIN trip_participants tp ON tp.trip_id = t.id
    JOIN users u ON t.driver_id = u.id
    WHERE tp.user_id = :uid AND tp.confirmed = 1 AND t.status = 'completed'
    
    UNION ALL
    
    SELECT t2.*, u2.pseudo AS driver_name, 'driver' AS role
    FROM trips t2
    JOIN users u2 ON t2.driver_id = u2.id
    WHERE t2.driver_id = :uid AND t2.status = 'completed'
    
    ORDER BY departure_date DESC
    LIMIT :limit OFFSET :offset
";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
$stmt->bindValue(':limit', $itemsPerPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$trips = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Historique des trajets | EcoRide</title>
  <link rel="stylesheet" href="../css/style.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Lemonada:wght@300..700&display=swap" rel="stylesheet">
  <style>
    .trip-card {
      border: 1px solid #ccc;
      background: white;
      padding: 1rem;
      border-radius: 10px;
      margin-bottom: 1.5rem;
    }
    .trip-role {
      font-weight: bold;
      font-size: 0.9em;
      padding: 2px 6px;
      border-radius: 5px;
      display: inline-block;
      margin-bottom: 0.5rem;
    }
    .passenger { background: #d1ecf1; color: #0c5460; }
    .driver { background: #e2e3e5; color: #383d41; }
    .pagination {
      text-align: center;
      margin-top: 1rem;
      margin-bottom: 2rem;
    }
    .pagination a {
      margin: 0 5px;
      padding: 0.5rem;
      border: 1px solid #aaa;
      border-radius: 5px;
      text-decoration: none;
    }
    .pagination span.active {
      background: #eee;
      border: 1px solid #000;
      padding: 0.5rem;
      border-radius: 5px;
      font-weight: bold;
    }
  </style>
</head>
<body>

<?php include_once '../composants/navbar.php'; ?>

<main>
  <div class="form-container">
    <h2>Historique de vos trajets</h2>

    <?php if (empty($trips)): ?>
      <p>Aucun voyage terminé à afficher.</p>
    <?php else: ?>
      <?php foreach ($trips as $trip): ?>
        <div class="trip-card">
          <span class="trip-role <?= $trip['role'] === 'passenger' ? 'passenger' : 'driver' ?>">
            <?= $trip['role'] === 'passenger' ? 'Passager' : 'Conducteur' ?>
          </span>

          <p><strong>Conducteur :</strong> <?= htmlspecialchars($trip['driver_name']) ?></p>
          <p><strong>De :</strong> <?= htmlspecialchars($trip['departure_city']) ?> → 
             <strong>À :</strong> <?= htmlspecialchars($trip['arrival_city']) ?></p>
          <p><strong>Date :</strong> <?= htmlspecialchars($trip['departure_date']) ?> à <?= substr($trip['departure_time'], 0, 5) ?></p>

          <?php if ($trip['role'] === 'passenger'): ?>
            <?php
              $stmtRating = $pdo->prepare("SELECT id, rating, comment FROM ratings WHERE author_id = :uid AND trip_id = :tid AND status = 'accepted'");
              $stmtRating->execute([':uid' => $userId, ':tid' => $trip['id']]);
              $rating = $stmtRating->fetch(PDO::FETCH_ASSOC);
            ?>

            <?php if ($rating): ?>
              <p><strong>Note :</strong> <?= htmlspecialchars($rating['rating']) ?>/5</p>
              <?php if ($rating['comment']): ?>
                <p><strong>Commentaire :</strong> <?= nl2br(htmlspecialchars($rating['comment'])) ?></p>
                <form method="post" action="../../back/deleteReview.php" onsubmit="return confirm('Supprimer votre commentaire ?');">
                  <input type="hidden" name="rating_id" value="<?= $rating['id'] ?>">
                  <button type="submit" class="red">🗑 Supprimer le commentaire</button>
                </form>
              <?php else: ?>
                <p><em>Commentaire supprimé</em></p>
              <?php endif; ?>
            <?php else: ?>
              <form method="post" action="leaveReview.php">
                <input type="hidden" name="trip_id" value="<?= $trip['id'] ?>">
                <input type="hidden" name="driver" value="<?= htmlspecialchars($trip['driver_name']) ?>">
                <button class="yellow" type="submit">⭐ Laisser un avis</button>
              </form>
            <?php endif; ?>
          <?php else: ?>
            <form method="get" action="../driver/myTripsReviews.php">
              <input type="hidden" name="trip_id" value="<?= $trip['id'] ?>">
              <button class="green" type="submit">📝 Voir les avis reçus</button>
            </form>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>

      <?php
        // Affiche la pagination avec ?page=
        renderPagination($totalTrips, $itemsPerPage, $page, 'tripsStory.php');
      ?>
    <?php endif; ?>
  </div>
</main>

<?php include_once '../composants/footer.html'; ?>
</body>
</html>
