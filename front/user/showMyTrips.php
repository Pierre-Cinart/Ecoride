<?php
  // chargement des classes
  include_once '../composants/autoload.php';
  //bouton select dans la navbar
  $_SESSION['navSelected'] = 'contact';


// Vérif connexion // a modif
if (!isset($_SESSION['typeOfUser'])) {
  header("Location: login.php");
  exit();
}

$type = $_SESSION['typeOfUser'];
$pseudo = $_SESSION['pseudo'] ?? 'Utilisateur';
$_SESSION['navSelected'] = 'account';

// Simule des trajets
$trajetsPassager = [
  ['date' => '2025-05-10', 'ville_depart' => 'Paris', 'ville_arrivee' => 'Lyon', 'conducteur' => 'Marie_69'],
  ['date' => '2025-05-14', 'ville_depart' => 'Marseille', 'ville_arrivee' => 'Nice', 'conducteur' => 'Zizou_13'],
];

$trajetsConducteur = ($type === 'driver') ? [
  ['date' => '2025-05-12', 'ville_depart' => 'Toulouse', 'ville_arrivee' => 'Bordeaux', 'places_restantes' => 2],
  ['date' => '2025-05-20', 'ville_depart' => 'Lille', 'ville_arrivee' => 'Bruxelles', 'places_restantes' => 3],
] : [];

?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Mes trajets réservés | EcoRide</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include_once '../composants/navbar.php'; ?>

<main>
  <?php include_once '../composants/inProgress.php'; ?>
  <div class="form-container">
    <h2>Mes trajets réservés</h2>

    <!-- SECTION PASSAGER -->
    <h3>En tant que passager</h3>
    <?php if (empty($trajetsPassager)): ?>
      <p>Vous n’avez pas encore réservé de trajet.</p>
    <?php else: ?>
      <?php foreach ($trajetsPassager as $trajet): ?>
        <div style="border:1px solid #ccc; padding:1rem; border-radius:8px; margin-bottom:1rem;">
          <p><strong>Départ :</strong> <?= $trajet['ville_depart'] ?></p>
          <p><strong>Arrivée :</strong> <?= $trajet['ville_arrivee'] ?></p>
          <p><strong>Date :</strong> <?= $trajet['date'] ?></p>
          <p><strong>Conducteur :</strong> <?= $trajet['conducteur'] ?></p>
          <form method="post" action="annuler_trajet.php">
            <input type="hidden" name="type" value="passager">
            <input type="hidden" name="trajet_id" value="...">
            <button class="red" type="submit">❌ Annuler</button>
          </form>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>

    <!-- SECTION CONDUCTEUR -->
    <?php if ($type === 'driver'): ?>
      <h3>En tant que conducteur</h3>
      <?php if (empty($trajetsConducteur)): ?>
        <p>Vous n’avez pas encore proposé de trajet.</p>
      <?php else: ?>
        <?php foreach ($trajetsConducteur as $trajet): ?>
          <div style="border:1px solid #ccc; padding:1rem; border-radius:8px; margin-bottom:1rem;">
            <p><strong>Départ :</strong> <?= $trajet['ville_depart'] ?></p>
            <p><strong>Arrivée :</strong> <?= $trajet['ville_arrivee'] ?></p>
            <p><strong>Date :</strong> <?= $trajet['date'] ?></p>
            <p><strong>Places restantes :</strong> <?= $trajet['places_restantes'] ?></p>
            <form method="post" action="annuler_trajet.php">
              <input type="hidden" name="type" value="conducteur">
              <input type="hidden" name="trajet_id" value="...">
              <button class="red" type="submit">❌ Annuler</button>
            </form>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    <?php endif; ?>

  </div>
</main>

<!-- footer -->
<?php include_once '../composants/footer.html'; ?>

</body>
</html>
