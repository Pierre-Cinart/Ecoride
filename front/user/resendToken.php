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
  <title>Renvoyer un lien de vérification - EcoRide</title>
  <link rel="stylesheet" href="../css/style.css" />
  <link rel="stylesheet" href="../css/home.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Lemonada:wght@300..700&display=swap" rel="stylesheet">

  <?php 
    $captchaAction = 'resend'; 
    include_once '../composants/captcha.php'; 
  ?>
</head>
<body>

  <header>
    <?php include_once '../composants/navbar.php';?>
  </header>

  <main>
    <div class="form-container">
      <h2>Renvoyer le lien de vérification</h2>
      <p>Votre lien de vérification a expiré ou vous ne l'avez pas reçu ? Entrez votre adresse email pour en recevoir un nouveau.</p>

      <form action="../../back/resendToken.php" method="post">
        <label for="email">Adresse email :</label>
        <input type="email" id="email" name="email" required>

        <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">

        <button type="submit">Renvoyer le lien</button>
      </form>
    </div>
  </main>

  <?php include_once '../composants/footer.html'; ?>
</body>
</html>
