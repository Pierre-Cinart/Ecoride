<?php
require_once '../../back/composants/autoload.php'; // Class BDD JWT control d accés et Recaptcha

checkAccess(['SimpleUser', 'Driver']);//(autorisation d accés )
$_SESSION['navSelected'] = 'account';

?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Ajouter des crédits - EcoRide</title>
  <link rel="stylesheet" href="../css/style.css">
   <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Lemonada:wght@300..700&display=swap" rel="stylesheet">
   <!-- Google reCAPTCHA v3 -->
  <?php 
    $captchaAction = 'addCredits'; // action personnalisée pour cette page (ex : login, register, contact, etc.)
  ?>
  <style>
    .info-box {
      background-color: #f8f9fa;
      border-left: 4px solid #17a2b8;
      padding: 1rem;
      margin-bottom: 1.5rem;
      font-size: 0.9rem;
      color: #333;
    }
    .info-box strong {
      color: #17a2b8;
    }
    .don-link {
      font-size: 0.9rem;
      margin-top: 1rem;
      display: block;
      color: #666;
    }
   
  </style>
</head>
<body>

<header>
  <?php include_once '../composants/navbar.php'; ?>
</header>

<main>
  <div class="form-container">
    <h2>Obtenir des crédits</h2>
    <p>Ajoutez des crédits à votre compte pour réserver des trajets ou participer en tant que conducteur.</p>

    <!-- Message de confirmation (session) -->
    <?php if (isset($_SESSION['success'])): ?>
      <p class="success"><?= $_SESSION['success'] ?></p>
      <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
      <p class="error"><?= $_SESSION['error'] ?></p>
      <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Message de simulation -->
    <div class="info-box">
      <strong>🔒 Simulation :</strong> Cette plateforme est fictive. Aucun paiement réel ne sera effectué.
      Ce formulaire permet uniquement de <strong>simuler l’ajout de crédits</strong> dans un environnement de test.
    </div>

    <!-- Formulaire -->
    <form method="post" action="../../back/confirmCredits.php" id="creditForm">
      <label for="creditAmount">Nombre de crédits à ajouter :</label>
      <input type="number" id="creditAmount" name="creditAmount" min="1" required placeholder="Ex : 10, 20, 50...">

      <label for="fakePayment">Méthode de paiement :</label>
      <select id="fakePayment" name="fakePayment" required>
        <option value="paypal">PayPal</option>
        <option value="cb">Carte Bancaire</option>
        <option value="crypto">Crypto-monnaie</option>
      </select>
      <!-- Champ caché pour recevoir le token reCAPTCHA -->
      <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
      <button type="submit">💳 Valider l'achat</button>
    </form>

    <!-- Don -->
    <p class="don-link">
      ❤️ Ce projet est un exercice de simulation. Pour le soutenir, vous pouvez faire un don sur
      <a href="https://www.paypal.me/votreLien" target="_blank">ma page PayPal</a>. Merci 🙏
    </p>
  </div>

</main>

<?php 
  include_once '../composants/footer.php'; 
  renderRecaptcha($captchaAction); // Injection du script reCAPTCHA v3 invisible avec l'action 'reserve' 
  ?>

<script src="../js/credits.js"></script>

</body>
</html>
