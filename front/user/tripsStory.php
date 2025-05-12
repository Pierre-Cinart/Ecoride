<?php
require_once '../composants/autoload.php';
require_once '../../back/composants/db_connect.php';
require_once '../../back/composants/checkAccess.php';
 require_once '../../back/composants/paginate.php';

checkAccess(['SimpleUser', 'Driver']);
$_SESSION['navSelected'] = 'account';

$user = $_SESSION['user'];
$userId = $user->getId();
$isDriver = $user instanceof Driver;

// === RÉCUPÉRATION DE TOUS LES TRAJETS TERMINÉS ===
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
";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
$stmt->execute();
$allTrips = $stmt->fetchAll(PDO::FETCH_ASSOC);

// === FILTRE ACTIF ===
$filter = $_GET['filter'] ?? 'all';

// === APPLICATION DU FILTRE ===
$filteredTrips = [];

foreach ($allTrips as $trip) {
    // Récupération de l'avis pour ce trajet
    $stmtRating = $pdo->prepare("SELECT id, rating, comment, status FROM ratings WHERE author_id = :uid AND trip_id = :tid LIMIT 1");
    $stmtRating->execute([':uid' => $userId, ':tid' => $trip['id']]);
    $rating = $stmtRating->fetch(PDO::FETCH_ASSOC);
    $status = $rating['status'] ?? 'waiting';

    // Filtrage selon le select
    $match = match ($filter) {
        'validated' => in_array($status, ['accepted', 'deleted']),
        'pending'   => $status === 'pending',
        'not_left'  => $status === 'waiting',
        default     => true,
    };

    if ($match) {
        $trip['rating'] = $rating;
        $trip['status'] = $status;
        $filteredTrips[] = $trip;
    }
}

// === PAGINATION (après filtrage) ===
$itemsPerPage = 2;
$totalFiltered = count($filteredTrips);
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$totalPages = max(1, ceil($totalFiltered / $itemsPerPage));
$offset = ($page - 1) * $itemsPerPage;
$currentPageTrips = array_slice($filteredTrips, $offset, $itemsPerPage);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Historique des trajets | EcoRide</title>
  <link rel="stylesheet" href="../css/style.css">
    <!-- google font -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Lemonada:wght@300..700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/tripsStory.css">
</head>
<body>

<?php include_once '../composants/navbar.php'; ?>

<main>
  <div class="form-container">
    <h2>Historique de vos trajets</h2>

    <!-- Formulaire de filtrage -->
    <form method="get" style="margin-bottom: 1rem;">
      <label for="filter">Filtrer par type d'avis :</label>
      <select name="filter" id="filter" onchange="this.form.submit()">
        <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>Tous les trajets</option>
        <option value="validated" <?= $filter === 'validated' ? 'selected' : '' ?>>Avis validés</option>
        <option value="pending" <?= $filter === 'pending' ? 'selected' : '' ?>>Avis en attente de validation</option>
        <option value="not_left" <?= $filter === 'not_left' ? 'selected' : '' ?>>Avis non laissés</option>
      </select>
    </form>

    <?php if (empty($currentPageTrips)): ?>
      <p>Aucun trajet à afficher pour ce filtre.</p>
    <?php else: ?>
      <?php foreach ($currentPageTrips as $trip): ?>
        <?php $rating = $trip['rating']; $status = $trip['status']; ?>
        <div class="trip-card">
          <span class="trip-role <?= $trip['role'] === 'passenger' ? 'passenger' : 'driver' ?>">
            <?= $trip['role'] === 'passenger' ? 'Passager' : 'Conducteur' ?>
          </span>

          <p><strong>Conducteur :</strong> <?= htmlspecialchars($trip['driver_name']) ?></p>
          <p><strong>De :</strong> <?= htmlspecialchars($trip['departure_city']) ?> →
             <strong>À :</strong> <?= htmlspecialchars($trip['arrival_city']) ?></p>
          <p><strong>Date :</strong> <?= htmlspecialchars($trip['departure_date']) ?> à <?= substr($trip['departure_time'], 0, 5) ?></p>

          <?php if ($trip['role'] === 'passenger'): ?>
            <?php if ($rating): ?>
              <p><strong>Note :</strong> <?= htmlspecialchars($rating['rating']) ?>/5</p>

              <?php if ($status === 'waiting'): ?>
                <form method="post" action="leaveReview.php">
                  <input type="hidden" name="trip_id" value="<?= $trip['id'] ?>">
                  <input type="hidden" name="driver" value="<?= htmlspecialchars($trip['driver_name']) ?>">
                  <button class="yellow" type="submit">⭐ Laisser un avis</button>
                </form>

              <?php elseif ($status === 'pending'): ?>
                <p><strong>Avis :</strong> <?= nl2br(htmlspecialchars($rating['comment'])) ?></p>
                <p class="text-muted"><em>🕒 En attente de validation</em></p>
                <button class="gray" disabled>🗑 Supprimer</button>
                <button class="gray" disabled>🛠 Demande de modification</button>

              <?php elseif ($status === 'accepted'): ?>
                <?php if ($rating['comment']): ?>
                  <p><strong>Avis :</strong> <?= nl2br(htmlspecialchars($rating['comment'])) ?></p>
                <?php else: ?>
                  <p><em>Avis sans commentaire</em></p>
                <?php endif; ?>

                <form method="post" action="../../back/deleteReview.php" onsubmit="return confirm('Supprimer votre avis ?');" style="display:inline-block;">
                  <input type="hidden" name="rating_id" value="<?= $rating['id'] ?>">
                  <button type="submit" class="red">🗑 Supprimer l'avis</button>
                </form>

                <form method="post" action="requestReviewChange.php" style="display:inline-block; margin-left: 1rem;">
                  <input type="hidden" name="rating_id" value="<?= $rating['id'] ?>">
                  <button type="submit" class="blue">🛠 Demander une modification</button>
                </form>

              <?php elseif ($status === 'deleted'): ?>
                <p><em>Avis supprimé</em></p>
                <form method="post" action="requestReviewChange.php">
                  <input type="hidden" name="rating_id" value="<?= $rating['id'] ?>">
                  <button type="submit" class="blue">🛠 Demander une modification</button>
                </form>
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

      <!-- Affichage pagination simple -->
      <?php
        renderPagination($totalFiltered, $itemsPerPage, $page, 'tripsStory.php?filter=' . urlencode($filter));
      ?>
    <?php endif; ?>
  </div>
</main>

<?php include_once '../composants/footer.html'; ?>
</body>
</html>
