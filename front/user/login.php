<?php
  // chargement des classes et demarage de session 
  require_once '../composants/autoload.php';
  //bouton select dans la navbar
  $_SESSION['navSelected'] = 'login';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Connexion - EcoRide</title>
   <link rel="icon" href="../../favicon.ico" type="image/x-icon">
  <link rel="stylesheet" href="../css/style.css" />
  <!-- google fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Lemonada:wght@300..700&display=swap" rel="stylesheet">

  <!-- Google reCAPTCHA v3 -->
  <?php 
    $captchaAction = 'login'; // action personnalisée pour cette page (ex : login, register, contact, etc.)
    require_once '../../back/composants/captcha.php'; // inclut le fichier qui contient la fonction renderRecaptcha()
  ?>


</head>
<body>

  <header>
    <!-- Navbar dynamique -->
    <?php include_once '../composants/navbar.php';?> 
    
  </header>

  <div class="form-container">
    <h2>Connexion</h2>

    <form action="../../back/login.php" method="post" id="login-form">
      <label for="email">Adresse email :</label>
      <input type="email" id="email" name="email" required>

      <label for="password">Mot de passe :</label>
      <input type="password" id="password" name="password" required>

      <!-- Champ caché pour recevoir le token reCAPTCHA -->
      <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">

      <button type="submit">Se connecter</button>
    </form>

    <p><a href="#">Mot de passe oublié ?</a></p>
    <p>Pas encore inscrit ? <a href="../user/register.php">Créer un compte</a></p>
  </div>

  <!-- footer -->
 <?php 
  include_once '../composants/footer.php'; 
  renderRecaptcha($captchaAction); // Injection du script reCAPTCHA v3 invisible avec l'action 'login' ?>

</body>
</html>
