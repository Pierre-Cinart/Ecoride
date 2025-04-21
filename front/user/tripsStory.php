<?php
session_start();
$_SESSION['navSelected'] = 'account';

$type = $_SESSION['typeOfUser'] ?? null;
$pseudo = $_SESSION['pseudo'] ?? 'Utilisateur';

// Redirection si non connecté
if (!$type) {
  header("Location: login.php");
  exit();
}

// Simulations
$voyagesPassager = [
  ['conducteur' => 'KevinDriver', 'ville_depart' => 'Paris', 'ville_arrivee' => 'Lyon', 'date' => '2025-04-10', 'note_donnee' => false],
  ['conducteur' => 'JulieRider', 'ville_depart' => 'Nice', 'ville_arrivee' => 'Toulon', 'date' => '2025-04-14', 'note_donnee' => true],
];

$voyagesConducteur = ($type === 'driver') ? [
  ['ville_depart' => 'Toulouse', 'ville_arrivee' => 'Bordeaux', 'date' => '2025-04-05'],
  ['ville_depart' => 'Lille', 'ville_arrivee' => 'Bruxelles', 'date' => '2025-03-29'],
] : [];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Historique des voyages | EcoRide</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<header>
  <?php include_once '../composants/navbar.php'; ?>
</header>

<main>
  <div class="form-container">
    <h2>Historique de vos voyages</h2>

    <!-- SECTION PASSAGER -->
    <h3>En tant que passager</h3>
    <?php if (empty($voyagesPassager)): ?>
      <p>Vous n’avez encore effectué aucun trajet.</p>
    <?php else: ?>
      <?php foreach ($voyagesPassager as $voyage): ?>
        <div style="border:1px solid #ccc; padding:1rem; border-radius:8px; margin-bottom:1rem;">
          <p><strong>Conducteur :</strong> <?= $voyage['conducteur'] ?></p>
          <p><strong>Départ :</strong> <?= $voyage['ville_depart'] ?> → <strong>Arrivée :</strong> <?= $voyage['ville_arrivee'] ?></p>
          <p><strong>Date :</strong> <?= $voyage['date'] ?></p>

          <?php if (!$voyage['note_donnee']): ?>
            <form method="post" action="leaveReview.php">
              <input type="hidden" name="trip_id" value="...">
              <input type="hidden" name="conducteur" value="<?= $voyage['conducteur'] ?>">
              <button class="yellow" type="submit">⭐ Laisser un avis</button>
            </form>
          <?php else: ?>
            <p style="color: green;">✅ Avis déjà laissé</p>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>

    <!-- SECTION CONDUCTEUR -->
    <?php if ($type === 'driver'): ?>
      <h3>En tant que conducteur</h3>
      <?php if (empty($voyagesConducteur)): ?>
        <p>Aucun trajet conduit n’est encore enregistré.</p>
      <?php else: ?>
        <?php foreach ($voyagesConducteur as $trajet): ?>
          <div style="border:1px solid #ccc; padding:1rem; border-radius:8px; margin-bottom:1rem;">
            <p><strong>Départ :</strong> <?= $trajet['ville_depart'] ?> → <strong>Arrivée :</strong> <?= $trajet['ville_arrivee'] ?></p>
            <p><strong>Date :</strong> <?= $trajet['date'] ?></p>
            <!-- bouton pour voir les avis -->
            <form method="get" action="avisRecus.php" style="margin-top: 0.5rem;">
                <input type="hidden" name="trip_id" value="...">
                <button class="green" type="submit">📝 Voir les avis reçus</button>
            </form>

          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    <?php endif; ?>

    <!-- Pagination fictive -->
    <div class="pagination">← Précédent | Page 1 sur 3 | Suivant →</div>

  </div>
</main>

<footer>
  <?php include_once '../composants/footer.html'; ?>
</footer>

</body>
</html>
