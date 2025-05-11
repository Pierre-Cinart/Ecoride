<?php
// Fichier : front/user/reserv.php
require_once '../composants/autoload.php';
require_once '../../back/composants/db_connect.php';

$_SESSION['navSelected'] = 'account';

// ANNULATION manuelle du résumé => supprime la session et retourne à la recherche
if (isset($_GET['action']) && $_GET['action'] === 'cancel') {
    unset($_SESSION['tripPending']);
    header('Location: search.php');
    exit;
}

// Vérifie que l'utilisateur est connecté
if (!isset($_SESSION['user']) || !($_SESSION['user'] instanceof SimpleUser || $_SESSION['user'] instanceof Driver)) {
    $_SESSION['error'] = "Vous devez être connecté pour réserver.";
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user'];

// Récupération du trip_id (fail-safe session ou post)
$tripId = $_POST['trip_id'] ?? ($_SESSION['tripPending'] ?? null);

if (!$tripId || !is_numeric($tripId)) {
    $_SESSION['error'] = "Aucun trajet sélectionné.";
    header('Location: search.php');
    exit;
}

$_SESSION['tripPending'] = $tripId;

// Requête pour les infos du trajet
$stmt = $pdo->prepare("
    SELECT t.*, u.pseudo AS driver_name, v.brand, v.model 
    FROM trips t
    JOIN users u ON t.driver_id = u.id
    JOIN vehicles v ON t.vehicle_id = v.id
    WHERE t.id = :id
");
$stmt->execute([':id' => $tripId]);
$trip = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$trip) {
    $_SESSION['error'] = "Trajet introuvable.";
    header('Location: search.php');
    exit;
}

$hasEnoughCredits = $user->getCredits() >= $trip['price'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Confirmer la réservation - EcoRide</title>
  <link rel="stylesheet" href="../css/style.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Lemonada:wght@300..700&display=swap" rel="stylesheet">
  <style>
    .trip-summary {
      background: white;
      padding: 1.5rem;
      border-radius: 12px;
      margin: auto;
      max-width: 500px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .trip-summary h2 { margin-top: 0; }
    .danger { color: red; font-weight: bold; }
    .success { color: green; font-weight: bold; }
  </style>
</head>
<body>

<?php include_once '../composants/navbar.php'; ?>

<main>
  <div class="trip-summary">
    <h2>Résumé du trajet</h2>
    <p><strong>Départ :</strong> <?= htmlspecialchars($trip['departure_city']) ?></p>
    <p><strong>Arrivée :</strong> <?= htmlspecialchars($trip['arrival_city']) ?></p>
    <p><strong>Date :</strong> <?= htmlspecialchars($trip['departure_date']) ?> à <?= substr($trip['departure_time'], 0, 5) ?></p>
    <p><strong>Conducteur :</strong> <?= htmlspecialchars($trip['driver_name']) ?></p>
    <p><strong>Véhicule :</strong> <?= htmlspecialchars($trip['brand']) ?> <?= htmlspecialchars($trip['model']) ?></p>
    <p><strong>Places disponibles :</strong> <?= (int)$trip['available_seats'] ?></p>
    <p><strong>Prix :</strong> <?= (float)$trip['price'] ?> crédits</p>

    <?php if (!$hasEnoughCredits): ?>
      <p class="danger">❌ Vous n'avez pas assez de crédits pour réserver ce trajet.</p>
      <p><a class="danger" href="addCredits.php">👉 Cliquez ici pour recharger vos crédits</a></p>
    <?php else: ?>
      <form method="post" action="../../back/reserv.php">
        <input type="hidden" name="trip_id" value="<?= $trip['id'] ?>">
        <button type="submit" class="blue">✅ Confirmer la réservation</button>
      </form>
    <?php endif; ?>

    <!-- Bouton annuler -->
    <form method="get" action="">
      <input type="hidden" name="action" value="cancel">
      <button type="submit" class="red">❌ Annuler</button>
    </form>
  </div>
</main>

<?php include_once '../composants/footer.html'; ?>

<!-- JS pour cacher les popups -->
<script>
  const popup = document.querySelector('.pending-alert'); // remplace par ton sélecteur exact
  if (popup) popup.classList.add('hidden'); // ajoute la classe pour masquer
</script>

</body>
</html>
