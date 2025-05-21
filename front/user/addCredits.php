<?php
require_once '../../back/composants/autoload.php'; // Chargement des classes, session, sécurité, etc.

checkAccess(['SimpleUser', 'Driver']); // Autorisation d’accès uniquement aux utilisateurs connectés
$_SESSION['navSelected'] = 'account';

?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Ajouter des crédits - EcoRide</title>
  <link rel="stylesheet" href="../css/style.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Lemonada:wght@300..700&display=swap" rel="stylesheet">
  <style>
    .cb-simulation-box {
      background-color: #f0f0f0;
      padding: 1rem;
      border-radius: 8px;
      margin-top: 1rem;
      font-size: 0.9rem;
    }

    .cb-simulation-box label {
      margin-top: 0.5rem;
      font-weight: bold;
    }

    .cb-simulation-box input {
      background-color: #e9ecef;
      border: 1px solid #ccc;
      padding: 0.5rem;
      border-radius: 6px;
    }

    .cb-simulation-box p {
      margin-top: 0.5rem;
      font-size: 0.85rem;
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
    <h2>Ajouter des crédits</h2>
    <p>Remplissez le formulaire pour simuler un achat de crédits via carte bancaire.</p>

    <?php if (isset($_SESSION['success'])): ?>
      <p class="success"><?= $_SESSION['success'] ?></p>
      <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
      <p class="error"><?= $_SESSION['error'] ?></p>
      <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <form method="post" action="../../back/addCredits.php" onsubmit="return confirmCreditAmount()">
      <label for="creditAmount">Montant de crédits à ajouter :</label>
      <input type="number" name="creditAmount" id="creditAmount" min="1" required placeholder="Ex : 10, 20, 50...">

      <input type="hidden" name="paymentMethod" value="cb">

      <div class="cb-simulation-box">
        <h3>Carte bancaire (simulation)</h3>

        <label>Nom sur la carte :</label>
        <input type="text" value="XXXX XXXX" readonly>

        <label>Numéro de carte :</label>
        <input type="text" value="4242 4242 4242 4242" readonly>

        <label>Date d’expiration :</label>
        <input type="text" value="12/34" readonly>

        <label>Cryptogramme :</label>
        <input type="text" value="123" readonly>

        <p><strong>Simulation :</strong> Ces informations sont fictives. Aucun paiement réel ne sera effectué.</p>
      </div>

      <!-- reCAPTCHA -->
      <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
      
      <button type="submit">Valider l’achat simulé</button>
    </form>
  </div>
</main>

<?php 
  include_once '../composants/footer.php'; 
  renderRecaptcha('addCredits'); // Ajout dynamique du script reCAPTCHA
?>

<script>
  function confirmCreditAmount() {
    const amount = document.getElementById("creditAmount").value;
    if (!amount || amount <= 0) {
      alert("Veuillez entrer un montant valide.");
      return false;
    }
    return confirm(`Vous êtes sur le point d’ajouter ${amount} crédits à votre compte. Confirmez-vous cet achat simulé ?`);
  }
</script>

</body>
</html>
