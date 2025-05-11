<?php
  // cahargement des classes et demarage de session 
  require_once '../composants/autoload.php';
  //bouton select dans la navbar
  $_SESSION['navSelected'] = 'home';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Accueil - EcoRide</title>
  <link rel="stylesheet" href="../css/style.css" />
  <link rel="stylesheet" href="../css/home.css" />
  <!-- Font Google -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Lemonada:wght@300..700&display=swap" rel="stylesheet">
</head>
<body>

<header>
  <?php  
  include_once '../composants/navbar.php'; 
 ?>
</header>

<main>
  <section class="home-search">
    <form class="home-search-form" action="tripList.php" method="get">
      <input type="text" name="depart" id="depart" placeholder="Ville de départ" list="depart-list" required>
      <datalist id="depart-list"></datalist>

      <input type="text" name="arrivee" id="arrivee" placeholder="Ville d’arrivée" list="arrivee-list" required>
      <datalist id="arrivee-list"></datalist>

      <button type="submit">🔍 Rechercher</button>
    </form>
  </section>

  <section class="home-presentation">
    <h2>Présentation d’EcoRide</h2>
    <p>
      Bienvenue sur <strong>EcoRide</strong>, l’alternative écologique pour vos déplacements quotidiens !

      Notre mission est simple : réduire l’empreinte carbone liée aux transports tout en facilitant les trajets partagés entre particuliers.

      Chez EcoRide, nous croyons qu’un avenir plus vert passe par des actions concrètes et accessibles.

      C’est pourquoi nous avons créé une plateforme de covoiturage écoresponsable, pensée pour tous — que vous soyez conducteur ou passager, habitant en ville ou en zone rurale.

      Ensemble, roulons vers un futur plus propre. 🌱
    </p>
  </section>
</main>

<?php include_once '../composants/footer.html'; ?>

<!-- Autocomplétion avec GeoAPI.gouv -->
<script src="../js/geoApi.js"></script>

</body>
</html>
