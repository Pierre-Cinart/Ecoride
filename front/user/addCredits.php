<?php
 // chargement des classes et demarage de session 
  require_once '../composants/autoload.php';
$_SESSION['navSelected'] = 'account';

$pseudo = $_SESSION['pseudo'] ?? 'Utilisateur';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Ajouter des crédits - EcoRide</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<header>
  <?php include_once '../composants/navbar.php'; ?>
</header>

<main>
  <?php include_once '../composants/inProgress.php'; ?>
  <div class="form-container">
    <h2>Obtenir des crédits</h2>
    <p>Ajoutez des crédits à votre compte pour réserver des trajets ou participer en tant que conducteur.</p>

    <form method="post" action="confirmCredits.php">
      <label for="creditAmount">Nombre de crédits à ajouter :</label>
      <input type="number" id="creditAmount" name="creditAmount" min="1" required placeholder="Ex : 10, 20, 50...">

      <label for="fakePayment">Méthode de paiement :</label>
      <select id="fakePayment" name="fakePayment" required>
        <option value="paypal">PayPal</option>
        <option value="cb">Carte Bancaire</option>
        <option value="crypto">Crypto-monnaie</option>
      </select>

      <button type="submit">💳 Valider l'achat</button>
    </form>

    <p style="margin-top:1rem; font-size: 0.9rem; color: #666;">
      <strong>*</strong> Cette plateforme est fictive. Aucun paiement réel ne sera effectué.
      Cette simulation permet de tester l’ajout de crédits dans un environnement de développement.
    </p>

    <p style="font-size: 0.9rem; color: #666;">
      Pour soutenir le projet, vous pouvez faire un don sur <a href="https://www.paypal.me/votreLien" target="_blank">ma page PayPal</a>.
      Merci 🙏
    </p>
  </div>
</main>

<!-- footer -->
<?php include_once '../composants/footer.html'; ?>

</body>
</html>
