<?php
// chargement des class + demarage de sessions + update token
require_once '../../back/composants/autoload.php';


// Contrôle d'accès : uniquement SimpleUser ou Driver
require_once '../../back/composants/checkAccess.php';

checkAccess(['SimpleUser', 'Driver']);

// Pour la navbar
$_SESSION['navSelected'] = 'account';

// Récupération de l'utilisateur connecté
$user = $_SESSION['user'];
$userId = $user->getId();
$isDriver = $user instanceof Driver;

// Connexion BDD
require_once '../../back/composants/db_connect.php';

// Récupération des trajets réservés (en tant que passager)
$sql = "SELECT t.*, tp.confirmation_date
        FROM trip_participants tp
        JOIN trips t ON tp.trip_id = t.id
        WHERE tp.user_id = :user_id
          AND tp.confirmed = 1
        ORDER BY t.departure_date ASC, t.departure_time ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute([':user_id' => $userId]);
$trajetsPassager = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Mes trajets réservés | EcoRide</title>
  <!-- style -->
  <link rel="stylesheet" href="../css/style.css">
  <!-- google font -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Lemonada:wght@300..700&display=swap" rel="stylesheet">
</head>
<body>

<?php include_once '../composants/navbar.php'; ?>

<main>
  <?php include_once '../composants/inProgress.php'; ?>
  <div class="form-container">
    <h2>Mes trajets réservés</h2>

    <h4>En tant que passager</h4>
    <?php
      $oneTrip = false;
      foreach ($trajetsPassager as $trajet):
        // Combinaison date + heure pour comparaison
        $datetimeDepart = new DateTime($trajet['departure_date'] . ' ' . $trajet['departure_time']);
        $now = new DateTime();
        $diff = $now->diff($datetimeDepart);
        $heuresRestantes = ($datetimeDepart->getTimestamp() - $now->getTimestamp()) / 3600;

        if ($datetimeDepart > $now):
          $oneTrip = true;
    ?>
        <div style="border:1px solid #ccc; padding:1rem; border-radius:8px; margin-bottom:1rem;">
          <p><strong>Départ :</strong> <?= htmlspecialchars($trajet['departure_city']) ?></p>
          <p><strong>Arrivée :</strong> <?= htmlspecialchars($trajet['arrival_city']) ?></p>
          <p><strong>Date :</strong> <?= $trajet['departure_date'] ?> à <?= substr($trajet['departure_time'], 0, 5) ?></p>

          <?php if ($heuresRestantes >= 24): ?>
            <form method="post" action="../../back/cancelUserTrip.php" onsubmit="return confirm('Êtes-vous sûr de vouloir annuler ce voyage ?')">
              <input type="hidden" name="trip_id" value="<?= $trajet['id'] ?>">
              <button type="submit" class="red">❌ Annuler</button>
            </form>
          <?php else: ?>
            <p style="color:gray;"><em>Ce voyage est trop proche pour être annulé.</em></p>
          <?php endif; ?>
        </div>
    <?php
        endif;
      endforeach;

      if (!$oneTrip) echo "<p>Vous n’avez aucun trajet à venir.</p>";
    ?>

  </div>
</main>

<?php include_once '../composants/footer.html'; ?>

</body>
</html>
