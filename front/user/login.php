<?php
session_start();

$_SESSION['navSelected'] = 'login';

?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Connexion - EcoRide</title>
  <link rel="stylesheet" href="../css/style.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Lemonada:wght@300..700&display=swap" rel="stylesheet">

</head>
<body>

  <header>

    <!-- Navbar dynamique -->
  <?php include_once '../composants/navbar.php'; ?>

  </header>

  <div class="form-container">

    <h2>Connexion</h2>

    <form action="../../back/login.php" method="post">

      <label for="email">Adresse email :</label>
      <input type="email" id="email" name="email" required>

      <label for="password">Mot de passe :</label>
      <input type="password" id="password" name="password" required>

      <button type="submit">Se connecter</button>
    </form>

    <p><a href="#">Mot de passe oublié ?</a></p>
    <p>Pas encore inscrit ? <a href="../user/register.php">Créer un compte</a></p>
    
  </div>

   <!-- footer -->
  <?php include_once '../composants/footer.html'; ?>

</body>
</html>
