<?php
session_start();

$_SESSION['navSelected'] = 'search';

?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Recherche de trajets - EcoRide</title>
  <!-- style -->
  <link rel="stylesheet" href="../css/style.css" />
  <link rel="stylesheet" href="../css/search.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Lemonada:wght@300..700&display=swap" rel="stylesheet">
  <!-- map -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />


</head>
<body>

  <header>

    <!-- Navbar dynamique -->
  <?php include_once '../composants/navbar.php'; ?>

  </header>

  <div class="main-container">

    <!-- Colonne : FILTRES -->
    <div class="filters">
      <h3>Filtres</h3>

      <label for="ecoOnly">
        <input type="checkbox" id="ecoOnly" name="ecoOnly" />
        Trajet écologique uniquement
      </label>

      <label for="vehicleType">Type de véhicule</label>
      <select id="vehicleType" name="vehicleType">
        <option value="">Tous types</option>
        <option value="essence">Essence</option>
        <option value="diesel">Diesel</option>
        <option value="hybride">Hybride</option>
        <option value="electrique">Électrique</option>
      </select>

      <label for="prix_max">Prix maximum (€)</label>
      <input type="number" id="prix_max" name="prix_max" min="0" step="0.5" />

      <label for="duree_max">Durée max (minutes)</label>
      <input type="number" id="duree_max" name="duree_max" min="1" />

      <label>Note minimale</label>
      <div class="star-rating">
        <input type="radio" id="star5" name="note_min" value="5" /><label for="star5">★</label>
        <input type="radio" id="star4" name="note_min" value="4" /><label for="star4">★</label>
        <input type="radio" id="star3" name="note_min" value="3" /><label for="star3">★</label>
        <input type="radio" id="star2" name="note_min" value="2" /><label for="star2">★</label>
        <input type="radio" id="star1" name="note_min" value="1" /><label for="star1">★</label>
      </div>
    </div>

    <!-- Colonne : FORMULAIRE -->
    <div class="form-container" style = "margin-top:0px;">
      <h2>Rechercher un trajet</h2>
      <form>
        <label for="depart">Ville de départ</label>
        <input type="text" id="depart" name="depart" required />

        <label for="destination">Ville d’arrivée</label>
        <input type="text" id="destination" name="destination" required />

        <label for="date">Date</label>
        <input type="date" id="date" name="date" required />

        <label for="heure">Heure (optionnelle)</label>
        <input type="time" id="heure" name="heure" />

        <button type="submit">Rechercher</button>
      </form>
    </div>

    <!-- Colonne : MAP -->
    <div class="map-container">
      <div id="map"></div>
    </div>

  </div>

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

</body>
</html>
