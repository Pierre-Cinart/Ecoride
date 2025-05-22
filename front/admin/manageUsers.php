<?php
require_once '../../back/composants/autoload.php';
require_once '../../back/composants/paginate.php';
require_once '../composants/btnBack.php';

checkAccess(['Admin','Employee']);
$_SESSION['navSelected'] = 'manage';
$filter = $_GET['filter'] ?? 'allUsers';
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
        <form action="" method="get">
          <input type="text" name="search" placeholder="Pseudo utilisateur" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
          <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
          <button type="submit">🔍</button>
        </form>
      </div>

      <!-- ======================
           Menu déroulant de filtre
      ======================= -->
      <div class="filter-bar">
        <label for="userFilter">Filtrer par :</label>
        <select id="userFilter">
          <option value="allUsers" <?= $filter === 'allUsers' ? 'selected' : '' ?>>Tous les utilisateurs</option>
          <option value="drivers" <?= $filter === 'drivers' ? 'selected' : '' ?>>Conducteurs</option>
          <option value="simpleUsers" <?= $filter === 'simpleUsers' ? 'selected' : '' ?>>Usagers</option>
          <option value="blockedUsers" <?= $filter === 'blockedUsers' ? 'selected' : '' ?>>Utilisateurs bloqués</option>
          <option value="bannedUsers" <?= $filter === 'bannedUsers' ? 'selected' : '' ?>>Utilisateurs bannis</option>
          <option value="pendingUsers" <?= $filter === 'pendingUsers' ? 'selected' : '' ?>>Documents en attente</option>
        </select>
      </div>

      <!-- ======================
           Section dynamique (un seul include à la fois)
      ======================= -->
      <div class="user-cards">
        <?php
          switch ($filter) {
            case 'drivers':
              include_once './showDrivers.php';
              break;
            case 'simpleUsers':
              include_once './showSimpleUsers.php';
              break;
            case 'blockedUsers':
              include_once './showBlockedUsers.php';
              break;
            case 'bannedUsers':
              include_once './showBannedUsers.php';
              break;
            case 'pendingUsers':
              include_once './showPendingUsers.php';
              break;
            default:
              include_once './showAllUsers.php';
          }
        ?>
      </div>

  </main>

  <footer>
    <?php include_once '../composants/footer.php'; ?>
  </footer>

  <script>
  document.addEventListener('DOMContentLoaded', () => {
    const filterSelect = document.getElementById('userFilter');
    filterSelect.addEventListener('change', () => {
      const selected = filterSelect.value;
      const url = new URL(window.location.href);
      url.searchParams.set('filter', selected);
      url.searchParams.set('page', '1');
      window.location.href = url.toString();
    });
  });
  </script>

</body>
</html>
