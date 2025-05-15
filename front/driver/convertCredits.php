<?php
session_start();
$_SESSION['navSelected'] = 'account';



$pseudo = $_SESSION['pseudo'] ?? 'Conducteur';
$credits = $_SESSION['credits'] ?? 20;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Obtenir un paiement - EcoRide</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<header>
  <?php include_once '../composants/navbar.php'; ?>
</header>

<main>
  <div class="form-container">
    <h2>Convertir vos crédits</h2>
    <p>Vous pouvez convertir vos crédits en argent fictif pour simulation. Le taux de conversion est de <strong>1 crédit = 0,50 €</strong>.</p>

    <form method="post" action="#">
      <label for="creditsToConvert">Nombre de crédits à convertir :</label>
      <input type="number" id="creditsToConvert" name="creditsToConvert" min="1" max="<?= $credits ?>" required>

      <label for="iban">RIB / IBAN (simulation) :</label>
      <input type="text" id="iban" name="iban" placeholder="FR76...." required>

      <button type="submit">Simuler le paiement</button>
    </form>

    <p style="margin-top: 1rem; font-size: 0.9rem; color: #666;">
      <strong>*</strong> Ceci est une simulation. Aucun virement réel n’est effectué. 
    </p>
  </div>
</main>

<footer>
  <?php include_once '../composants/footer.html'; ?>
</footer>

</body>
</html>
