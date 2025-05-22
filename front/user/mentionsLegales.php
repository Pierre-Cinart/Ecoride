<?php
 // chargement des classes et demarage de session 
  require_once '../composants/autoload.php';?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Mentions légales - EcoRide</title>
  <!-- style -->
    <link rel="icon" href="../../favicon.ico" type="image/x-icon">
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

  <div class="mention-container">
    <h2>Mentions légales</h2>

    <h3>Éditeur du site</h3>
    <p class="arial">
      Le site <strong>EcoRide</strong> est édité par la startup EcoRide, société fictive à but pédagogique.<br>
      Directeur de la publication : <strong>Pierre Cinart</strong>
    </p>

    <h3>Hébergement</h3>
    <p class="arial">
      Ce site est hébergé dans le cadre d’un projet de formation et ne fait l'objet d'aucune exploitation commerciale.
    </p>

    <h3>Propriété intellectuelle</h3>
    <p class="arial">
      L'ensemble des contenus du site (textes, images, logos, code source) sont la propriété de leurs auteurs respectifs dans le cadre d'un projet d'apprentissage. <strong>Toute reproduction ou utilisation non autorisée est interdite.</strong>
    </p>

    <h3>Données personnelles et confidentialité</h3>
    <p class="arial">
      Ce site étant fictif, aucune donnée personnelle réelle n'est requise. Il est donc inutile de renseigner des informations vérifiables. Aucune donnée n'est transmise à des tiers.
    </p>

    <h3>Sécurité et enregistrement d’activités</h3>
    <p class="arial">
      En cas de tentative d'accès non autorisé aux zones protégées du site (administration, espace employé...), l'adresse IP de l'utilisateur peut être enregistrée à des fins de sécurité. 
      Ces données sont conservées dans une base sécurisée pour une durée maximale de 30 jours, conformément au Règlement Général sur la Protection des Données (RGPD).
      Cette mesure est prévue mais pas encore activée sur la plateforme actuelle.
    </p>

    <h3>Contact</h3>
    <p class="arial y-">
      Pour toute question, vous pouvez utiliser le formulaire de contact présent sur la page <a href="../user/contact.php">Contact</a><br>
      ainsi que sur l ' icone <a href="../user/contact.php"><img src="../img/logo/logomail.png" alt="logo mail" ></a>présent sur le footer du site .</a>
    </p>
  </div>

  <!-- footer script menu burger et popUP -->
 <?php include_once '../composants/footer.php'; ?>
</body>
</html>
