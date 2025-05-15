<?php
require_once '../../back/composants/autoload.php';
require_once '../../back/config/configORS.php'; // Clé ORS : $OPEN_ROUTE_KEY
checkAccess(['Driver']);
$_SESSION['navSelected'] = 'offer';

$user = $_SESSION['user'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Proposer un trajet - EcoRide</title>
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/search.css"> <!-- Pour la map et layout -->

  <!-- Leaflet -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />

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
  <div class="main-container">
    <!-- Formulaire de proposition de trajet -->
    <div class="form-container">
      <h2>Proposer un trajet</h2>
      <p>Les trajets proposés vous coûteront <strong>2 crédits</strong>. Assurez-vous d’avoir un véhicule vérifié et les crédits nécessaires.</p>

      <form method="post" action="confirmAjoutTrajet.php" enctype="multipart/form-data" id="offerTripForm">
        <label for="departure_city">Ville de départ :</label>
        <input type="text" id="depart" name="depart" list="depart-list" required>
        <datalist id="depart-list"></datalist>

        <label for="arrival_city">Ville d’arrivée :</label>
        <input type="text" id="arrivee" name="arrivee" list="arrivee-list" required>
        <datalist id="arrivee-list"></datalist>

        <label for="date">Date du trajet :</label>
        <input type="date" id="date" name="departure_date" min="<?= date('Y-m-d') ?>" required>

        <label for="time">Heure de départ :</label>
        <input type="time" id="time" name="departure_time" required>

        <label for="vehicle">Véhicule utilisé :</label>
        <select id="vehicle" name="vehicle_id" required>
          <option value="">-- Choisir un véhicule --</option>
          <?php foreach ($user->getVehicles() as $vehicleId): ?>
            <?php $veh = new Vehicle($pdo, $vehicleId); ?>
            <option value="<?= $veh->getId() ?>"><?= $veh->getDisplayName() ?> (<?= $veh->getRegistrationNumber() ?>)</option>
          <?php endforeach; ?>
        </select>

        <label for="places">Nombre de places disponibles :</label>
        <input type="number" id="places" name="available_seats" min="1" max="6" required>

        <label for="price">Prix du trajet (en crédits) :</label>
        <input type="number" id="price" name="price" min="0" step="1" required>

        <div id="duration-section">
          <label for="estimated_duration">Durée estimée (minutes) :</label>
          <input type="number" id="estimated_duration" name="estimated_duration" readonly>
          <button type="button" id="calculateDuration">Calculer le temps de trajet</button>
        </div>

        <button type="submit">Valider le trajet</button>
      </form>
    </div>

    <!-- Carte interactive -->
    <div class="map-container">
      <div class="map-controls">
        <button id="setStart">🟢 Choisir point de départ</button>
        <button id="setEnd">🔴 Choisir point d'arrivée</button>
      </div>
      <div id="map"></div>
    </div>
  </div>
</main>

<footer>
  <?php include_once '../composants/footer.html'; ?>
</footer>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script src="../js/geoApi.js"></script>
<script src="../js/map.js"></script>

</body>
</html>
