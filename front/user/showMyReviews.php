<?php
  // chargement des classes
  include_once '../composants/autoload.php';
  //bouton select dans la navbar
  $_SESSION['navSelected'] = 'account';



$pseudo = $_SESSION['pseudo'] ?? 'Moi';
$type = $_SESSION['typeOfUser'] ?? null;

// Sécurité : accès réservé aux utilisateurs connectés
if (!$type) {
  header('Location: login.php');
  exit();
}

// Simulation d'avis laissés
$commentairesLaisses = [
  ['destinataire' => 'KevinDriver', 'note' => 5, 'commentaire' => 'Très ponctuel, agréable et voiture propre.'],
  ['destinataire' => 'JulieRider', 'note' => 4, 'commentaire' => 'Tout s’est bien passé, je recommande !'],
  ['destinataire' => 'EcoRider69', 'note' => 3, 'commentaire' => 'Le trajet était correct mais un peu de retard à l’arrivée.'],
];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Mes commentaires - EcoRide</title>
  <link rel="stylesheet" href="../css/style.css" />
  <link rel="stylesheet" href="../css/reviews.css" />
</head>
<body>

  <header>
    <?php include_once '../composants/navbar.php';?>
  </header>

  <main>
    <?php include_once '../composants/inProgress.php'; ?>
    <section class="reviews-section">
      <h2>Mes commentaires</h2>
      <p>Voici les avis que vous avez laissés après vos trajets.</p>

      <?php foreach ($commentairesLaisses as $avis): ?>
        <div class="review-card">
          <p><strong><?= htmlspecialchars($pseudo) ?></strong> sur <em><?= htmlspecialchars($avis['destinataire']) ?></em>
            <span class="stars">
              <?= str_repeat('★', $avis['note']) . str_repeat('☆', 5 - $avis['note']) ?>
            </span>
          </p>
          <p><?= htmlspecialchars($avis['commentaire']) ?></p>

          <form method="post" action="deleteReview.php" style="text-align: right;">
            <input type="hidden" name="review_id" value="...">
            <button class="red" type="submit">🗑 Supprimer</button>
          </form>
        </div>
      <?php endforeach; ?>

      <!-- Pagination simulée -->
      <div class="pagination">← Précédent | Page 1 sur 3 | Suivant →</div>
    </section>
  </main>

  <!-- footer -->
  <?php include_once '../composants/footer.html'; ?>

</body>
</html>
