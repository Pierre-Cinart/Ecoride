<?php
session_start();

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
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Lemonada:wght@300..700&display=swap" rel="stylesheet">

</head>
<body>

  <header>

    <!-- Navbar dynamique -->
  <?php include_once '../composants/navbar.php'; ?>

  </header>

  <!-- Contenu principal -->
  <main>

  <section class="home-search">
    <form class="home-search-form" action="triplist.php" method="get">
      <input type="text" name="depart" placeholder="Ville de départ" required>
      <input type="text" name="arrivee" placeholder="Ville d’arrivée" required>
      <button type="submit">🔍 Rechercher</button>
    </form>
  </section>

    <!-- Section de présentation -->
    <section class="home-presentation">
      <h2>Présentation d’EcoRide</h2><br>
      <p>Bienvenue sur <strong>EcoRide</strong>, l’alternative écologique pour vos déplacements quotidiens ! </p>
      <p>Notre mission est simple : réduire l’empreinte carbone liée aux transports tout en facilitant les trajets partagés entre particuliers.<br><br>
      Chez EcoRide, nous croyons qu’un avenir plus vert passe par des actions concrètes et accessibles. </p>
      <p>C’est pourquoi nous avons créé une plateforme de covoiturage écoresponsable, pensée pour tous — que vous soyez conducteur ou passager, habitant en ville ou en zone rurale.
      </p>
    </section>

  </main>

   <!-- Footer -->
    <?php include_once '../composants/footer.html'; ?>
 
 
</body>
</html>
