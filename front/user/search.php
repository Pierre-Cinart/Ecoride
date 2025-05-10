<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Recherche de trajets - EcoRide</title>

  <!-- CSS -->
  <link rel="stylesheet" href="../css/style.css" />
  <link rel="stylesheet" href="../css/search.css" />

  <!-- Fonts Google -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Lemonada:wght@300..700&display=swap" rel="stylesheet">

  <!-- Leaflet pour carte -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
</head>
<body>

<header>
  <?php include_once '../composants/navbar.php';
  $_SESSION['navSelected'] = 'search'; ?>
</header>

<div class="main-container">

  <!-- Colonne de gauche : Filtres -->
  <div class="filters">
    <h3>Filtres</h3>

    <!-- Formulaire de recherche avec méthode GET vers tripList.php -->
    <form action="tripList.php" method="get" id="searchForm">

      <label><input type="checkbox" name="ecoOnly" value="1" /> Trajet écologique uniquement</label>

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
        <input type="radio" id="star5" name="note_min" value="5" /><label for="star5">★</label>
        <input type="radio" id="star4" name="note_min" value="4" /><label for="star4">★</label>
        <input type="radio" id="star3" name="note_min" value="3" /><label for="star3">★</label>
        <input type="radio" id="star2" name="note_min" value="2" /><label for="star2">★</label>
        <input type="radio" id="star1" name="note_min" value="1" /><label for="star1">★</label>
      </div>
  </div>

  <!-- Colonne centrale : Formulaire principal -->
  <div class="form-container">
    <h2>Rechercher un trajet</h2>

    <label for="depart">Ville de départ</label>
    <input type="text" id="depart" name="depart" list="depart-list" required />
    <datalist id="depart-list"></datalist>

    <label for="arrivee">Ville d’arrivée</label>
    <input type="text" id="arrivee" name="arrivee" list="arrivee-list" required />
    <datalist id="arrivee-list"></datalist>

    <label for="date">Date (optionnelle)</label>
    <input type="date" id="date" name="date" min="<?= date('Y-m-d') ?>" />

    <label for="heure">Heure (optionnelle)</label>
    <input type="time" id="heure" name="heure" />

    <button type="submit">Rechercher</button>
    </form>
  </div>

  <!-- Colonne de droite : Carte -->
  <div class="map-container">
    <div id="map"></div>
  </div>

</div>

<?php include_once '../composants/footer.html'; ?>

<!-- Script Leaflet -->
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
  const map = L.map('map').setView([48.8566, 2.3522], 12);
  L.tileLayer('https://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap',
    minZoom: 1,
    maxZoom: 20
  }).addTo(map);
  L.marker([48.8566, 2.3522]).addTo(map)
    .bindPopup('Paris - Point de départ par défaut')
    .openPopup();
</script>

<!-- Script d’autocomplétion GeoAPI -->
<script src="../js/GeoAPI.js"></script>

</body>
</html>
