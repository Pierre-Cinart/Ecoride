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
  <title>Gestion des utilisateurs | EcoRide</title>
  <link rel="stylesheet" href="../css/style.css" />
  <link rel="stylesheet" href="../css/manageUsers.css" />
  <link rel="icon" href="../../favicon.ico" type="image/x-icon" />
  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Lemonada:wght@300..700&display=swap" rel="stylesheet">
</head>

<body>

  <header>
    <?php include_once '../composants/navbar.php'; ?>
  </header>

  <main>
    <div class="top-buttons">
      <?php btnBack('manage.php') ?>
    </div>

    <div class="manage-container">
      <h2>Gestion des utilisateurs</h2>

      <!-- ======================
           Barre de recherche
      ======================= -->
      <div class="search-bar">
        <form action="#"> <!-- options gérées en js pour éviter la répétition des requetes ( toogle class hidden )-->
          <input type="text" placeholder="Pseudo utilisateur" id="searchInput">
          <button type="submit">🔍</button>
        </form>
      </div>
    </div>
      <!-- ======================
           Menu déroulant de filtre
      ======================= -->
      <div class="filter-bar">
        <label for="userFilter">Filtrer par :</label>
        <select id="userFilter">
          <option value="allUsers">Tous les utilisateurs</option>
          <option value="drivers">Conducteurs</option>
          <option value="simpleUsers">Usagers</option>
          <option value="blockedUsers">Utilisateurs bloqués</option>
          <option value="bannedUsers">Utilisateurs bannis</option>
          <option value="pendingUsers">Documents en attente</option>
        </select>
      </div>

      <!-- ======================
           Sections dynamiques (une seule visible à la fois)
      ======================= -->

      <div id="allUsers" class = "user-cards">
        <?php include_once './showAllUsers.php'; ?>
      </div>

      <div id="drivers" class="hidden user-cards">
        <?php include_once './showDrivers.php'; ?>
      </div>

      <div id="simpleUsers" class="hidden user-cards">
        <?php include_once './showSimpleUsers.php'; ?>
      </div>

      <div id="blockedUsers" class="hidden user-cards">
        <?php include_once './showBlockedUsers.php'; ?>
      </div>

      <div id="bannedUsers" class="hidden user-cards">
        <?php include_once './showBannedUsers.php'; ?>
      </div>

      <div id="pendingUsers" class="hidden user-cards">
        <?php include_once './showPendingUsers.php'; ?>
      </div>

    
  </main>

  <footer>
    <?php include_once '../composants/footer.php'; ?>
  </footer>

  <script src="../js/manageUsers.js"></script>

</body>
</html>
