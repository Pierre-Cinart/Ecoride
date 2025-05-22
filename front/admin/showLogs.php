<?php
require_once '../../back/composants/autoload.php';
require_once '../../back/composants/loggerFirebase.php';
$logs =  readLogsFromFirebase();

checkAccess(['Admin','Employee']);
$_SESSION['navSelected'] = 'manage';

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
 
    <header><?php include_once '../composants/navbar.php'; ?></header> 
    <div style = 'height:100vh;background-color:green;width:80%;text-align:center;margin:auto'>
        <?php if (empty($logs)): ?>
            <p>Aucun log trouvé.</p>
        <?php else: ?>
            <ul>
            <?php foreach ($logs as $log): ?>
                <li>
                <strong><?= htmlspecialchars($log['Name']) ?></strong> —
                <?= htmlspecialchars($log['Action']) ?> —
                <em><?= htmlspecialchars($log['Timestamp']) ?></em>
                </li>
            <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</body>
<?php include_once '../composants/footer.php'; ?>

</body>
</html>