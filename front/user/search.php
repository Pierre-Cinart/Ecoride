<?php
 // chargement des classes et demarage de session 
  require_once '../composants/autoload.php';

  //btn selected navbarr
  $_SESSION['navSelected'] = 'search';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
   <link rel="icon" href="../../favicon.ico" type="image/x-icon">
  <title>Recherche de trajets - EcoRide</title>

  <!-- Feuilles de style -->
  <link rel="stylesheet" href="../css/style.css" />
  <link rel="stylesheet" href="../css/search.css" />

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Lemonada:wght@300..700&display=swap" rel="stylesheet">

  <!-- Leaflet pour la carte -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
</head>
<body>

<?php include_once '../composants/navbar.php'; ?>

<div class="main-container">
  <!-- Colonne Filtres -->
  <div class="filters">
    <h3>Filtres</h3>
    <form action="tripList.php" method="get" id="searchForm">

      <label><input type="checkbox" name="ecoOnly" value="1"> Trajet écologique uniquement</label>

      <label for="vehicleType">Type de véhicule</label>
      <select id="vehicleType" name="vehicleType">
        <option value="">Tous types</option>
        <option value="gasoline">Essence</option>
        <option value="diesel">Diesel</option>
        <option value="hybrid">Hybride</option>
        <option value="electric">Électrique</option>
      </select>

      <label for="prix_max">Prix max (crédits)</label>
      <input type="number" id="prix_max" name="prix_max" min="1" />

      <label>Note minimale</label>
      <div class="star-rating">
        <input type="radio" id="star5" name="note_min" value="5"><label for="star5">★</label>
        <input type="radio" id="star4" name="note_min" value="4"><label for="star4">★</label>
        <input type="radio" id="star3" name="note_min" value="3"><label for="star3">★</label>
        <input type="radio" id="star2" name="note_min" value="2"><label for="star2">★</label>
        <input type="radio" id="star1" name="note_min" value="1"><label for="star1">★</label>
      </div>

      <!-- Durée max estimée (affichée si calculée) -->
      <label for="maxDuration">Durée max (minutes)</label>
      <input type="number" id="maxDuration" name="maxDuration" min="1" />
  </div>

  <!-- Formulaire principal -->
  <div class="form-container">
    <h2>Rechercher un trajet</h2>

    <label for="depart">Ville de départ</label>
    <input type="text" id="depart" name="depart" list="depart-list" required>
    <datalist id="depart-list"></datalist>
    <br><br>

    <label for="arrivee">Ville d’arrivée</label>
    <input type="text" id="arrivee" name="arrivee" list="arrivee-list" required>
    <datalist id="arrivee-list"></datalist>
    <br><br>

    <label for="date">Date</label>
    <input type="date" id="date" name="date" min="<?= date('Y-m-d') ?>"> (optionnel)
    <br><br>

    <label for="heure">Heure</label>
    <input type="time" id="heure" name="heure"> (optionnel)
    <br><br>

    <button type="submit">Rechercher</button>
    </form>
  </div>

  <!-- Colonne Carte -->
  <div class="map-container">
    <div class="map-controls">
      <button id="setStart">🟢 Choisir point de départ</button>
      <button id="setEnd">🔴 Choisir point d'arrivée</button>
    </div>
    <div id="map" style="height: 400px;"></div>
  </div>
</div>

 <?php include_once '../composants/footer.php'; ?>
 
<!-- Librairies JS -->
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script src="../js/geoApi.js"></script>
<script src="../js/map.js"></script>
</body>
</html>
