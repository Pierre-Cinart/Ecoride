<?php
//chargement des classes
require_once '../composants/autoload.php';
// accés bdd
require_once '../../back/composants/db_connect.php';
//control d accés
require_once '../../back/composants/checkAccess.php';
checkAccess(['SimpleUser', 'Driver']);

$_SESSION['navSelected'] = 'reviews';

$tripId = $_POST['trip_id'] ?? null;
$driver = $_POST['driver'] ?? null;

if (!$tripId || !$driver) {
    $_SESSION['error'] = "Données manquantes pour laisser un avis.";
    header("Location: tripsStory.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Laisser un avis - EcoRide</title>
  <link rel="stylesheet" href="../css/style.css">
  <!-- google font -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Lemonada:wght@300..700&display=swap" rel="stylesheet">
</head>
<body>

<?php include_once '../composants/navbar.php'; ?>

<main>
  <div class="form-container">
    <h2>Laisser un avis sur le trajet</h2>
    <p><strong>Conducteur :</strong> <?= htmlspecialchars($driver) ?></p>

    <form method="post" action="../../back/saveReview.php" onsubmit="return confirm('Confirmer l\'envoi de votre avis ?')">
      <input type="hidden" name="trip_id" value="<?= htmlspecialchars($tripId) ?>">
      <label for="rating">Note (1 à 5) :</label>
      <input type="number" name="rating" id="rating" min="1" max="5" step="0.5" required>

      <label for="comment">Commentaire (facultatif) :</label>
      <textarea name="comment" id="comment" rows="4" placeholder="Partagez votre expérience..."></textarea>

      <button type="submit" class="green">✅ Envoyer l'avis</button>
    </form>
  </div>
</main>

<?php include_once '../composants/footer.html'; ?>
</body>
</html>
