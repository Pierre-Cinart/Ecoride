<?php
   // chargement des classes et demarage de session 
  require_once '../composants/autoload.php';
  //bouton select dans la navbar
  $_SESSION['navSelected'] = 'signup';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Inscription - EcoRide</title>
  <link rel="stylesheet" href="../css/style.css" />
  <link rel="stylesheet" href="../css/home.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Lemonada:wght@300..700&display=swap" rel="stylesheet">

  <!-- google recaptcha v3 -->
  <?php $captchaAction = 'register'; // action personnalisée pour cette page
  require_once '../../back/composants/captcha.php'; ?> 

</head>
<body>

  <header>

    <!-- Navbar dynamique -->
  <?php include_once '../composants/navbar.php'; ?>

  </header>

  <main>
    <div class="form-container" >
      <h2>Créer un compte</h2>
      <form action="../../back/register.php" method="post" enctype="multipart/form-data">
        <!-- prénom-->
        <label for="first-name">Prénom :</label>
        <input type="text" id="first-name" name="first-name" required>

        <!-- nom-->
        <label for="name">Nom :</label>
        <input type="text" id="name" name="name" required>

        <!-- Date de naissance -->
        <label for="birthdate">Date de naissance :</label>
        <input type="date" id="birthdate" name="birthdate" required>

        <!-- Sexe -->
        <label>Sexe :</label>
        <div class="gender-options">
          <label>
            <input type="radio" name="gender" id="gender-male" value="male" required>
            Homme
          </label>
          <label>
            <input type="radio" name="gender" id="gender-female" value="female">
            Femme
          </label>
        </div>

        <!-- pseudo-->
        <label for="pseudo">Pseudo :</label>
        <input type="text" id="pseudo" name="pseudo" >

        <!-- mail -->
        <label for="email">Adresse email :</label>
        <input type="email" id="email" name="email" required>

        <label for="confirm-email">Confirmer email :</label>
        <input type="email" id="confirm-email" name="confirm-email" required>

        <!-- téléphone -->
        <label for="phone">Numéro de téléphone :</label>
        <input type="tel" id="phone" name="phone" required>

        <!-- mot de passe -->
        <label for="password">Mot de passe :</label>
        <input type="password" id="password" name="password" required>

        <label for="confirm-password">Confirmer mot de passe :</label>
        <input type="password" id="confirm-password" name="confirm-password" required>

        <!-- Checkbox chauffeur -->
        <label>
          <input type="checkbox" id="is-driver" name="is-driver">
          Je souhaite m’inscrire en tant que chauffeur
        </label>

        <!--  Input pour le permis, caché par défaut -->
        <div id="driver-permit" class="hidden">
          <label for="permit">Télécharger votre permis de conduire :</label>
          <input type="file" id="permit" name="permit" accept="image/*">
        </div>

        <!-- Mentions légales -->
        <label>
          <input type="checkbox" id="terms" name="terms" required>
          J’accepte les <a href="mentionsLegales.php" target="_blank">mentions légales</a>
        </label>

        <!-- input caché pour recevoir le token reCAPTCHA -->
        <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">

        <!-- input caché pour capture d erreurs  -->
        <input type="hidden" id="errorMessage" name="errorMessage">

        <button type="submit">S'inscrire</button>
      </form>

      <p>Déjà un compte ? <a href="../user/login.php">Se connecter</a></p>
    </div>
  </main>
  
   <!-- footer -->
  <?php 
    include_once '../composants/footer.php';
    renderRecaptcha($captchaAction); // Injection du script reCAPTCHA v3 invisible avec l'action 'login' 
  ?>
  <script src="../js/register.js"></script>
</body>
</html>
