<?php
session_start();

$_SESSION['navSelected'] = 'manage';
// Redirection si non connecté
if (!isset($_SESSION['typeOfUser']) || ($_SESSION['typeOfUser'] != "admin" )){
  header('Location: ../user/login.php');
  exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Offrir des crédits - EcoRide</title>
  <link rel="stylesheet" href="../css/style.css" />
</head>
<body>
  <header><?php include_once '../composants/navbar.php'; ?></header>
  <main>
    <?php include_once '../composants/InProgress.php'; ?>
  </main>
  <?php include_once '../composants/footer.php'; ?>
</body>
</html>
