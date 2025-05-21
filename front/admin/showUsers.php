<?php
require_once '../../back/composants/autoload.php';
require_once '../../back/composants/paginate.php';
require_once '../composants/btnBack.php';

checkAccess(['Admin','Employee']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Documents & Avis - Employé | EcoRide</title>
  <link rel="stylesheet" href="../css/style.css" />
  <link rel="stylesheet" href="../css/manage.css" />
  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Lemonada:wght@300..700&display=swap" rel="stylesheet">
</head>

<body>

  <header>
    <?php include_once '../composants/navbar.php'; ?>
  </header>

  <?php include_once '../composants/inProgress.php'; ?>
  <main>
    <div class="top-buttons">
        <?php btnBack('manage.php') ?>
      </div>
    <div class="manage-container">
      <h2><!-- mettre un titre dynamique Tout les utilisateurs par defaut --></h2>
        <div class="search-bar">
        <!-- bouton de recherche précise pour gain de temps  -->
        <input type="text" placeholder="Nom ou Pseudo utilisateur">
        <button type="submit">🔍 Rechercher</button>
      </div>
      <!-- mettre un menu déroulant pour choisir entre les utilisateurs (client , conducteur , utilisateurs bloqués , utilisateurs bannit) -->
      <div class="user-list">
      <!-- faire un include du bout de page selon le get  -->
      <!-- selon le choix afficher dynamiquement dans des cartes avec pagination
       la carte contient les informations nom prénom satus et avertissements 
       si des avis ou des documents sont en attente , les affiché avec des boutons pour consulter et possibilité de valider ou refuser
        -->
       </div>
  </main>
  
  <!-- footer -->
  <?php include_once '../composants/footer.php'; ?>
  <!-- js module dynamique importe les script utiles en fonction des gets -->
  <script type="module" src="../js/managerUser.js"></script>

</body>
</html>
