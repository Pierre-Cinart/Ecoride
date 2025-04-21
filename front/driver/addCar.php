<?php
session_start();
$_SESSION['navSelected'] = 'account'; // pour le menu "Mon compte"
$pseudo = $_SESSION['pseudo'] ?? 'Utilisateur';
$type = $_SESSION['typeOfUser'] ?? null;

// sécurité : redirection si l'utilisateur n'est pas connecté en tant que chauffeur
if ($type !== 'driver') {
  header("Location: login.php");
  exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Ajouter un véhicule - EcoRide</title>
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/monCompte.css">
</head>
<body>

  <?php include_once '../composants/navbar.php'; ?>

  <main>
    <div class="form-container">
      <h2>Ajouter un véhicule</h2>
      <form method="post" action="traitement_vehicule.php" enctype="multipart/form-data">
        <label for="marque">Marque</label>
        <input type="text" id="marque" name="marque" required>

        <label for="modele">Modèle</label>
        <input type="text" id="modele" name="modele" required>

        <label for="energie">Type de carburant</label>
        <select id="energie" name="energie" required>
          <option value="essence">Essence</option>
          <option value="diesel">Diesel</option>
          <option value="electrique">Électrique</option>
          <option value="hybride">Hybride</option>
        </select>

        <label for="places">Nombre de places</label>
        <input type="number" id="places" name="places" min="1"  required>

        <label for="photo">Photo du véhicule</label>
        <input type="file" id="photo" name="photo" accept="image/*" required>

        <button type="submit">Ajouter le véhicule</button>
      </form>
    </div>
  </main>

  <footer>
    <?php include_once '../composants/footer.html'; ?>
  </footer>

</body>
</html>
