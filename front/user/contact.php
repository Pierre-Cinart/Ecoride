<?php
require_once '../composants/autoload.php';
$_SESSION['navSelected'] = 'contact';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Contact - EcoRide</title>
  <link rel="stylesheet" href="../css/style.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Lemonada:wght@300..700&display=swap" rel="stylesheet">
   <!-- Google reCAPTCHA v3 -->
  <?php 
    $captchaAction = 'contact'; // action personnalisée pour cette page (ex : login, register, contact, etc.)
    require_once '../../back/composants/captcha.php'; // inclut le fichier qui contient la fonction renderRecaptcha()
  ?>

</head>

<body>

<header>
  <?php include_once '../composants/navbar.php'; ?>
</header>

<main>
  <div class="form-container">
    <h2>Contactez-nous</h2>

    <form action="../../back/contact.php" method="post" novalidate>
      <label for="nom">Nom :</label>
      <input type="text" id="nom" name="nom" required />

      <label for="prenom">Prénom :</label>
      <input type="text" id="prenom" name="prenom" required />

      <label for="email">Adresse e-mail :</label>
      <input type="email" id="email" name="email" required />

      <label for="objet">Objet :</label>
      <input type="text" id="objet" name="objet" required />

      <label for="message">Message :</label>
      <textarea id="message" name="message" rows="6" required style="border-radius: 8px; border: 1px solid #ccc; padding: 0.6rem;"></textarea>
      
      <!-- Champ caché pour recevoir le token reCAPTCHA -->
      <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">

      <button type="submit">Envoyer</button>
    </form>
  </div>
</main>
<?php 
  include_once '../composants/footer.html';
  renderRecaptcha($captchaAction); // Injection du script reCAPTCHA v3 invisible avec l'action 'contact' ?>

<script src="../js/contact.js"></script>
</body>
</html>
