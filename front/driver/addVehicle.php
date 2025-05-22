<?php
// Initialisation et sécurité
require_once '../../back/composants/autoload.php';
checkAccess(['Driver']);

$user = $_SESSION['user'];
$pseudo = $user->getPseudo();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Ajouter un véhicule - EcoRide</title>
   <link rel="icon" href="../../favicon.ico" type="image/x-icon">
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/account.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Lemonada:wght@300..700&display=swap" rel="stylesheet">
</head>
<body>

  <?php include_once '../composants/navbar.php'; ?>

  <main>
    <div class="form-container">
      <h2>Ajouter un véhicule</h2>

      <form method="post" action="../../back/addVehicle.php" enctype="multipart/form-data">

        <label for="brand">Marque</label>
        <input type="text" id="brand" name="brand" required>

        <label for="model">Modèle</label>
        <input type="text" id="model" name="model" required>

        <label for="fuel_type">Type de carburant</label>
        <select id="fuel_type" name="fuel_type" required>
          <option value="essence">Essence</option>
          <option value="diesel">Diesel</option>
          <option value="electrique">Électrique</option>
          <option value="hybride">Hybride</option>
        </select>

        <label for="seats">Nombre de places</label>
        <input type="number" id="seats" name="seats" min="1" required>

        <label for="registration_number">Numéro d'immatriculation</label>
        <input type="text" id="registration_number" name="registration_number" required>

        <label for="first_registration_date">Date de première mise en circulation</label>
        <input type="date" id="first_registration_date" name="first_registration_date" max="<?= date('Y-m-d') ?>" required>

        <label for="photo">Photo du véhicule (optionnelle)</label>
        <input type="file" id="photo" name="photo" accept=".jpg,.jpeg,.png,.gif,.webp">

        <label for="registration_document">Carte grise (obligatoire)</label>
        <input type="file" id="registration_document" name="registration_document" accept=".jpg,.jpeg,.png,.gif,.webp" required>

        <label for="insurance_document">Assurance (obligatoire)</label>
        <input type="file" id="insurance_document" name="insurance_document" accept=".jpg,.jpeg,.png,.gif,.webp" required>

        <button type="submit" class="green">Ajouter le véhicule</button>

      </form>
    </div>
  </main>

  <?php include_once '../composants/footer.php'; ?>
  <script src="../js/addVehicle.js"></script>

</body>
</html>
