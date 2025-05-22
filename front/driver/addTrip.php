<?php
require_once '../../back/composants/autoload.php';
require_once '../../back/config/configORS.php'; // Clé ORS
//controle d accés
checkAccess(['Driver']);

//verification de l enregistrement de l utilisateur en tant que 
$_SESSION['navSelected'] = 'offer';

$user = $_SESSION['user'];
$status = $user->getStatus();
$credits = $user->getCredits();

//verification des blocages d utilisation du service
if ($status === 'driver_blocked' || $status === 'all_blocked' || !$user instanceof Driver) {
  $_SESSION['error'] = "Impossible de proposer un trajet.";
  header("Location: ../user/account.php");
  exit;
}

//vérification du minimum de crédit requit
if ($credits < 2){
   $_SESSION['error'] = "Impossible de proposer un trajet.";
  header("Location: ../user/addCredits.php");
  exit;
}

// Liste des véhicules approuvés + leurs infos pour JS
$vehicleObjects = [];
$vehicleData = [];

if ($user instanceof Driver) {
  foreach ($user->getVehicles() as $vehicleId) {
    try {
      $veh = new Vehicle($pdo, $vehicleId);
      if ($veh->getDocumentsStatus() === "approved") {
        $vehicleObjects[] = $veh;
        $vehicleData[$veh->getId()] = ['seats' => $veh->getSeats()];
      }
    } catch (Exception $e) {
      continue;
    }
  }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Proposer un trajet - EcoRide</title>
   <link rel="icon" href="../../favicon.ico" type="image/x-icon">
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/search.css">
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
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
    <div class="form-container">
      <h2>Proposer un trajet</h2>
      <p>Les trajets proposés vous coûteront <strong>2 crédits</strong>. Assurez-vous d’avoir un véhicule vérifié et les crédits nécessaires.</p>

      <form method="post" action="../../back/confirmAddTrip.php" id="offerTripForm">
        <label for="depart">Ville de départ :</label>
        <input type="text" id="depart" name="departure_city" list="depart-list" required>
        <datalist id="depart-list"></datalist>

        <label for="departure_address">Adresse précise de départ :</label>
        <input type="text" id="departure_address" name="departure_address" required placeholder="Ex : 10 rue de la Paix">

        <label for="arrivee">Ville d’arrivée :</label>
        <input type="text" id="arrivee" name="arrival_city" list="arrivee-list" required>
        <datalist id="arrivee-list"></datalist>

        <label for="arrival_address">Adresse précise d’arrivée :</label>
        <input type="text" id="arrival_address" name="arrival_address" required placeholder="Ex : 25 avenue du Général de Gaulle">

        <label for="date">Date du trajet :</label>
        <input type="date" id="date" name="departure_date" min="<?= date('Y-m-d') ?>" required>

        <label for="time">Heure de départ :</label>
        <input type="time" id="time" name="departure_time" required>

        <label for="vehicle">Véhicule utilisé :</label>
        <select id="vehicle" name="vehicle_id" required>
          <option value="">-- Choisir un véhicule --</option>
          <?php foreach ($vehicleObjects as $veh): ?>
            <option value="<?= $veh->getId() ?>">
              <?= htmlspecialchars($veh->getDisplayName()) ?> (<?= htmlspecialchars($veh->getRegistrationNumber()) ?>)
            </option>
          <?php endforeach; ?>
        </select>

        <label for="places" style="display: none;">Nombre de places disponibles :</label>
        <input type="number" id="places" name="available_seats" min="1" style="display: none;" required>

        <label for="price">Prix du trajet (en crédits) :</label>
        <input type="number" id="price" name="price" min="0" step="1" required>

        <div id="duration-section">
          <label for="estimated_duration">Durée estimée :</label>
          <input type="text" id="estimated_duration_display" readonly placeholder="Cliquez pour calculer">
          <input type="hidden" id="estimated_duration" name="estimated_duration">
          <button type="button" id="calculateDuration">Calculer le temps de trajet</button>
        </div>

        <button type="submit">Valider le trajet</button>
      </form>
    </div>

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
  <?php include_once '../composants/footer.php'; ?>
</footer>

<!-- SCRIPTS -->
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script src="../js/geoApi.js"></script>
<script src="../js/map.js"></script>

<script>
  const OPEN_ROUTE_KEY = '<?= $OPEN_ROUTE_KEY ?>';
  const VEHICLE_DATA = <?= json_encode($vehicleData) ?>;
</script>
<script src="../js/addTrip.js"></script>

</body>
</html>
