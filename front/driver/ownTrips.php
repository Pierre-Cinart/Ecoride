
<?php
// ==========================================================
// PAGE : ownTrips.php (conducteur) - Affiche ses trajets à venir avec pagination
// ==========================================================

// Chargement des composants essentiels (classes, session, vérif accès, DB, pagination)
require_once '../../back/composants/autoload.php';
require_once '../../back/composants/paginate.php';
checkAccess(['Driver']);
$_SESSION['navSelected'] = 'account';
$user = $_SESSION['user'];
$userId = $user->getId();

// Configuration pagination
$itemsPerPage = 5;
$currentPage = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$offset = ($currentPage - 1) * $itemsPerPage;

// 1. Compter le nombre total de trajets à venir du conducteur
$countQuery = $pdo->prepare("SELECT COUNT(*) FROM trips WHERE driver_id = :driver_id AND (departure_date > CURDATE() OR (departure_date = CURDATE() AND departure_time > CURTIME()))");
$countQuery->execute([':driver_id' => $userId]);
$totalTrips = (int) $countQuery->fetchColumn();

// 2. Récupération des trajets pour la page actuelle
$query = $pdo->prepare("
  SELECT *
  FROM trips
  WHERE driver_id = :driver_id
    AND (departure_date > CURDATE() OR (departure_date = CURDATE() AND departure_time > CURTIME()))
  ORDER BY departure_date ASC, departure_time ASC
  LIMIT :offset, :limit
");
$query->bindValue(':driver_id', $userId, PDO::PARAM_INT);
$query->bindValue(':offset', $offset, PDO::PARAM_INT);
$query->bindValue(':limit', $itemsPerPage, PDO::PARAM_INT);
$query->execute();
$trips = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Mes trajets à venir | EcoRide</title>
  <link rel="stylesheet" href="../css/style.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Lemonada:wght@300..700&display=swap" rel="stylesheet">
</head>
<body>

<?php include_once '../composants/navbar.php'; ?>

<main>
  <?php include_once '../composants/inProgress.php'; ?>
  <div class="form-container">
    <h2>Mes trajets à venir</h2>
    <h4>En tant que conducteur</h4>

    <?php
      $now = new DateTime();
      $hasTrip = false;

      foreach ($trips as $trip):
        $departureDT = new DateTime($trip['departure_date'] . ' ' . $trip['departure_time']);
        if ($departureDT > $now):
          $hasTrip = true;
    ?>
      <div style="border:1px solid #ccc; padding:1rem; border-radius:8px; margin-bottom:1rem;">
        <p><strong>Départ :</strong> <?= htmlspecialchars($trip['departure_city']) ?></p>
        <p><strong>Arrivée :</strong> <?= htmlspecialchars($trip['arrival_city']) ?></p>
        <p><strong>Date :</strong> <?= $trip['departure_date'] ?> à <?= substr($trip['departure_time'], 0, 5) ?></p>

        <!-- Formulaire d'annulation -->
        <form method="post" action="../../back/cancelOwnTrip.php"
              onsubmit="return confirm('⚠️ Vous êtes sur le point d\'annuler ce trajet prévu le <?= $trip['departure_date'] ?> à <?= substr($trip['departure_time'], 0, 5) ?>. S\'il y a des passagers, une pénalité de 2 crédits sera appliquée. Continuer ?')">
          <input type="hidden" name="trip_id" value="<?= $trip['id'] ?>">
          <button type="submit" class="red">❌ Annuler</button>
        </form>
      </div>
    <?php
        endif;
      endforeach;

      if (!$hasTrip) {
        echo "<p>Vous n’avez aucun trajet à venir.</p>";
      }

      // Affichage de la pagination
      $baseUrl = 'ownTrips.php';
      renderPagination($totalTrips, $itemsPerPage, $currentPage, $baseUrl);
    ?>
  </div>
</main>

<?php include_once '../composants/footer.php'; ?>
</body>
</html>
