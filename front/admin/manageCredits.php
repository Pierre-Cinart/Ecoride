<?php
require_once '../../back/composants/autoload.php';
require_once '../../back/composants/paginate.php';
require_once '../composants/btnBack.php';
checkAccess(['Admin']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Gestion du personnel</title>
  <link rel="stylesheet" href="../css/style.css" />
  <!-- font -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Lemonada:wght@300..700&display=swap" rel="stylesheet">
</head>
<body>
  <header><?php include_once '../composants/navbar.php'; ?></header>
   <?php include_once '../composants/InProgress.php'; ?>
  <main>
    <form method="post" action='./manageEmployees.php'>
        <input type="hidden" name="action" value=""><!-- bien configurer la search bar-->
        <input type="text" name="search" placeholder="Rechercher par nom">
        <button type="submit">🔍</button>
    </form>
      afficher le user trouver dans la barre de recherche dans une carte avec ses informations nom prenoms pseudo
      s insipirer de addCredit pour envoyer des credit avec un message personalisé en mail 
   
    
  </main>
  <?php include_once '../composants/footer.php'; ?>
</body>
</html>