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
</head>
<body>
  <header><?php include_once '../composants/navbar.php'; ?></header>
  <main>
    <form method="post" action='./manageEmployees.php'>
        <input type="hidden" name="action" value=""><!-- bien configurer la search bar-->
        <input type="text" name="search" placeholder="Rechercher par nom">
        <button type="submit">🔍</button>
    </form>
      afficher le personnel trouver dans la barre de recherche dans une carte avec ses informations et un bouton suspendre et un bouton update (facultatif)
      <button class = "green" onclick="location.href='addEmployee.php'">ajouter un nouveau membre</button>
      <button class = "red">afficher les anciens membre</button>
    <?php include_once '../composants/InProgress.php'; ?>
    
  </main>
  <?php include_once '../composants/footer.php'; ?>
</body>
</html>