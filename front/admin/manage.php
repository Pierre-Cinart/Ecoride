<?php
session_start();

$_SESSION['navSelected'] = 'manage';
// Redirection si non connecté
if (!isset($_SESSION['typeOfUser']) || ($_SESSION['typeOfUser']!= "user" && ( $_SESSION['typeOfUser'] != "admin" && $_SESSION['typeOfUser'] != "employee" ) ) ) {
  header('Location: ../user/login.php');
  exit();
}

$type = $_SESSION['typeOfUser'];
$pseudo = $_SESSION['pseudo'] ?? 'Utilisateur';
$credits = $_SESSION['credits'] ?? 20;

?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Espace Employé - EcoRide</title>
  <link rel="stylesheet" href="../css/style.css" />
  <link rel="stylesheet" href="../css/manage.css" />
 
</head>
<body>
  <header>
    <?php include_once '../composants/navbar.php'; ?>
  </header>
  <main>
    <div class="form-container manage-container">
      <h2>gestion - Espace Employé</h2>
      <form>
        <button type="button" onclick="location.href='manageReviews.php'">Gérer les avis des passagers</button>
        <button type="button" onclick="location.href='reportedTrips.php'">Consulter les trajets signalés</button>
        <button type="button" onclick="location.href='contactDrivers.php'">Contacter les conducteurs</button>
        <button type="button" onclick="location.href='showUsers.php'">Afficher les infos utilisateur</button>
        <button type="button" onclick="location.href='pendingDrivers.php'">Chauffeurs en attente de validation</button>
        <button type="button" onclick="location.href='blockMember.php'">Bloquer un membre</button>
        <button type="button" onclick="location.href='unblockMember.php'">Débloquer un membre</button>
      </form>
    </div>
  </main>
  <!-- footer -->
  <?php include_once '../composants/footer.html'; ?>
</body>
</html>
