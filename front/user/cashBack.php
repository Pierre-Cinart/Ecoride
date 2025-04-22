<!-- Page : refundRequest.php -->
<?php
session_start();
$_SESSION['navSelected'] = 'account';
$pseudo = $_SESSION['pseudo'] ?? 'NomUtilisateur';
$credits = $_SESSION['credits'] ?? 20; // affiché mais non modifiable
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Demande de remboursement</title>
  <link rel="stylesheet" href="../css/style.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Lemonada:wght@300..700&display=swap" rel="stylesheet">
</head>
<body>

<?php include_once '../composants/navbar.php'; ?>

<main>
  <div class="form-container">
    <h2>Demande de remboursement</h2>

    <form method="post" action="#">
      <label for="pseudo">Utilisateur :</label>
      <input type="text" id="pseudo" name="pseudo" value="<?= htmlspecialchars($pseudo) ?>" readonly />

      <label for="creditsTotal">Crédits disponibles :</label>
      <input type="number" id="creditsTotal" name="creditsTotal" value="<?= $credits ?>" readonly />

      <label for="creditsDemandes">Nombre de crédits à rembourser :</label>
      <input type="number" id="creditsDemandes" name="creditsDemandes" min="1" max="<?= $credits ?>" required />

      <label for="motif">Motif de la demande :</label>
      <textarea id="motif" name="motif" rows="4" placeholder="Expliquez pourquoi vous demandez un remboursement..." required></textarea>

      <button type="submit">Envoyer la demande</button>
    </form>

    <p style="margin-top: 1rem; font-size: 0.9rem; color: #555;">
      ⚠️ Cette demande sera transmise à un administrateur pour validation. Aucun remboursement automatique n’est effectué.
    </p>
  </div>
</main>

<?php include_once '../composants/footer.html'; ?>

</body>
</html>
