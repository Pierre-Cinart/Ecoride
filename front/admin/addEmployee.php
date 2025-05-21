<?php
require_once '../../back/composants/autoload.php';
require_once '../../back/composants/paginate.php';
require_once '../composants/btnBack.php';
checkAccess(['Admin']);
$_SESSION['navSelected'] = 'manage'; 
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Ajouter un employé - EcoRide</title>
  <link rel="stylesheet" href="../css/style.css" />
  <link rel="stylesheet" href="../css/home.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Lemonada:wght@300..700&display=swap" rel="stylesheet">
</head>
<body>
  <?php include_once '../composants/navbar.php'; ?>

  <main>
    <div class="form-container">
      <h2>Ajouter un nouvel employé</h2>
      <form action="../../back/addEmployee.php" method="post">
        <label for="first-name">Prénom :</label>
        <input type="text" id="first-name" name="first-name" required>

        <label for="name">Nom :</label>
        <input type="text" id="name" name="name" required>

        <label for="birthdate">Date de naissance :</label>
        <input type="date" id="birthdate" name="birthdate" required>

        <label>Sexe :</label>
        <div class="gender-options">
          <label><input type="radio" name="gender" value="male" required> Homme</label>
          <label><input type="radio" name="gender" value="female"> Femme</label>
        </div>

        <label for="email">Adresse email :</label>
        <input type="email" id="email" name="email" required>

        <label for="phone">Numéro de téléphone :</label>
        <input type="tel" id="phone" name="phone" required>

        <button type="submit">Ajouter l’employé</button>
      </form>
    </div>
  </main>

  <?php include_once '../composants/footer.php'; ?>
</body>
</html>
