<?php
require_once '../../back/composants/autoload.php';
checkAccess(['Admin','Employee']);

$user = $_SESSION['user'];

if ($user instanceof Employee){
  $gestionTitle = 'Employé';
} else {$gestionTitle = 'Admin';}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Espace <?php echo $gestionTitle ?> - EcoRide</title>
  <link rel="stylesheet" href="../css/style.css" />
  <link rel="stylesheet" href="../css/manage.css" />
   <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Lemonada:wght@300..700&display=swap" rel="stylesheet">
</head>
 
</head>
<body>
  <header>
    <?php include_once '../composants/navbar.php'; ?>
  </header>
  <main>
    <div class="form-container manage-container">
      <h2>Gestion - Espace Employé</h2>
      <form>
        <button type="button" onclick="location.href='manageReviews.php'">Gérer les avis des passagers</button>
        <!-- <button type="button" onclick="location.href='reportedTrips.php'">Consulter les signalements</button> (manque de temps deadline à développer)-->
        <button type="button" onclick="location.href='contactUser.php'">Contacter un utilisateur</button>
        <button type="button" onclick="location.href='manageUsers.php'">Afficher les infos utilisateur</button>
        <button type="button" onclick="location.href='manageUsers.php?type=driver&status=authorized&documents=waiting'">
          Chauffeurs en attente de validation
        </button>
        <!-- <button type="button" onclick="location.href='userlocking.php?action=block'">Bloquer un membre</button>
        <button type="button" onclick="location.href='userlocking.php?action=unblock'">Débloquer un membre</button> (manque de temps deadline à développer)--> 
        <!-- bouton admin pour gérer les employee -->
        <?php if ($user instanceof Admin): ?>
          <button type="button" onclick="location.href='manageEmployees.php'">Gérer les employés</button>
          <button type="button" onclick="location.href='manageCredits.php'">Gestions de crédits</button>
           <button type="button" onclick="location.href='showLogs.php'">voir les logs</button>
        <?php endif; ?>
      </form>
    </div>
  </main>
  <!-- footer -->
  <?php include_once '../composants/footer.php'; ?>
</body>
</html>
