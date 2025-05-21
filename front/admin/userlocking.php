<?php
require_once '../../back/composants/autoload.php';
require_once '../composants/btnBack.php';

checkAccess(['Admin','Employee']);
//empeches les injections en nettoyant les données get
$_GET = sanitizeArray($_GET , './manage.php');

$action = $_GET['action'];
$actionTitle = '';//titre dynamique
switch ($action) {
  case 'block':
    $actionTitle = 'Bloquer un utilisateur';
    // SELECT * FROM users WHERE status IN ('authorized', 'drive_blocked', ...) AND pseudo LIKE ?
    break;
  case 'unblock':
    $actionTitle = 'Débloquer un utilisateur';
    // SELECT * FROM users WHERE status IN ('blocked', 'all_blocked') AND pseudo LIKE ?
    break;
  default:
    header ('location: ./manage.php');
    // erreur ou redirection
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?php echo $actionTitle . ' un utilisateur'?> | EcoRide</title>
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
    <form method="get">
        <input type="hidden" name="action" value="<?= $action?>">
        <input type="text" name="search" placeholder="Rechercher un pseudo">
        <button type="submit">🔍</button>
    </form>
    <div id="show-user">
        <?php // si utilisateur trouvé echo $titleAction . ' ' . $userName 
        //form method post action : '../../back/userlocking.php'
        // btn confirmer avec un renvoie sur le traitement back?>

    </div>
  </main>
  
  <!-- footer -->
  <?php include_once '../composants/footer.php'; ?>
    
</body>
</html>
