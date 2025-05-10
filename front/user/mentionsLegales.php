<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Mentions légales - EcoRide</title>
  <!-- style -->
  <link rel="stylesheet" href="../css/style.css" />
  <link rel="stylesheet" href="../css/mentions.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Lemonada:wght@300..700&display=swap" rel="stylesheet">

</head>
<body>

  <header>

    <!-- Navbar dynamique -->
  <?php include_once '../composants/navbar.php'; 
  $_SESSION['navSelected'] = '';?>

  </header>

  <div class="form-container ">
    <h2>Mentions Légales</h2>

    <h3>Éditeur du site</h3>
    <p class = "arial">
      Le site EcoRide est édité par la startup EcoRide, société fictive à but pédagogique.<br>
      Directeur de la publication : Cinart Pierre
    </p>

    <h3>Hébergement</h3>
    <p class = "arial">
      Le site est hébergé dans le cadre d’un projet de formation.
    </p>

    <h3>Propriété intellectuelle</h3>
    <p class = "arial">
      Tous les contenus présents sur le site (textes, images, logo, code) sont la propriété de leurs auteurs respectifs dans le cadre d’un projet d’apprentissage. Toute reproduction est interdite sans autorisation.
    </p>

    <h3>Données personnelles</h3>
    <p class = "arial">
      Aucune donnée personnelle réelle n’est collectée. Les données saisies sont utilisées uniquement à des fins pédagogiques.
    </p>

    <h3>Contact</h3>
    <p class = "arial">
      Pour toute question, un formulaire est à votre dispostion via la page <a href="../user/contact.php">Contact</a>.
    </p>
  </div>

   <!-- footer -->
  <?php include_once '../composants/footer.html'; ?>

</body>
</html>
