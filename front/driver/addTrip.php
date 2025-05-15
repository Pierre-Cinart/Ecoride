<?php
require_once '../../back/composants/autoload.php';
require_once '../../back/config/configORS'; // key = $OPEN_ROUTE_KEY
checkAccess(['Driver']);
$_SESSION['navSelected'] = 'offer';

$user=$_SESSION['user'];

?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Proposer un trajet - EcoRide</title>
  <link rel="stylesheet" href="../css/style.css">
   <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Lemonada:wght@300..700&display=swap" rel="stylesheet">
</head>
<body>

<header>
  <?php include_once '../composants/navbar.php'; ?>
</header>

<main>
  <div class="form-container">
    <h2>Proposer un trajet</h2>
    <p>Les trajets proposés vous coûteront <strong>2 crédits</strong>. Assurez-vous d’avoir un véhicule vérifié et les crédits nécessaires.</p>

    <form method="post" action="confirmAjoutTrajet.php" enctype="multipart/form-data">
      <label for="departure">Ville de départ :</label>
      <input type="text" id="departure" name="departure" required>

      <label for="arrival">Ville d’arrivée :</label>
      <input type="text" id="arrival" name="arrival" required>

      <label for="date">Date du trajet :</label>
      <input type="date" id="date" name="date" required>

      <label for="time">Heure de départ :</label>
      <input type="time" id="time" name="time" required>

      <label for="vehicle">Véhicule utilisé :</label>
      <select id="vehicle" name="vehicle" required>
        <option value="">-- Choisir un véhicule --</option>
        <option value="1">Renault Zoé</option>
        <option value="2">Tesla Model 3</option>
      </select>

      <label for="places">Nombre de places disponibles :</label>
      <input type="number" id="places" name="places" min="1" max="6" required>

      <label for="price">Prix du trajet (en crédits) :</label>
      <input type="number" id="price" name="price" min="0" step="1" required>

      <button type="submit">Valider le trajet</button>
    </form>
  </div>
</main>

<footer>
  <?php include_once '../composants/footer.html'; ?>
</footer>

</body>
</html>
