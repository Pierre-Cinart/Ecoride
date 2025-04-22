<?php
session_start();

$_SESSION['navSelected'] = 'messages';
// Redirection si non connecté
if (!isset($_SESSION['typeOfUser']) || ($_SESSION['typeOfUser']!= "user" && ( $_SESSION['typeOfUser'] != "admin" && $_SESSION['typeOfUser'] != "employee" ) ) ) {
  header('Location: ../user/login.php');
  exit();
}

$type = $_SESSION['typeOfUser'];
$pseudo = $_SESSION['pseudo'] ?? 'Utilisateur';

?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <title>Messagerie - EcoRide</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="../css/style.css" />
  <link rel="stylesheet" href="../css/manage.css" />

</head>
<body>
  <header>
      <?php include_once '../composants/navbar.php'; ?>
  </header>
  <?php include_once '../composants/inProgress.php'; ?>
  <main>
    <div class="form-container messagerie-container">
      <h2>Messagerie</h2>
      <button onclick="location.href='./pendingMessages.php'"> Messages en attente</button>
      <button onclick="location.href='storyMessages.php'"> Historique des messages</button>
    </div>
  </main>

   <!-- footer -->
   <?php include_once '../composants/footer.html'; ?>
</body>
</html>
