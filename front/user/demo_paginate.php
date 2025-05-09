<?php
session_start();
require_once '../../back/composants/paginate.php'; // chemin à adapter selon ton projet

// Simulation d'une base de données de 123 éléments (ex: trajets ou messages)
$totalItems = 123;
$itemsPerPage = 10;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Déterminer les éléments à afficher pour la page en cours (simulation)
$start = ($currentPage - 1) * $itemsPerPage + 1;
$end = min($start + $itemsPerPage - 1, $totalItems);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Démo Pagination</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<h2>Résultats (démo)</h2>
<p>Affichage des éléments <?php echo $start; ?> à <?php echo $end; ?> sur <?php echo $totalItems; ?></p>

<ul>
  <?php for ($i = $start; $i <= $end; $i++): ?>
    <li>Élément n°<?php echo $i; ?></li>
  <?php endfor; ?>
</ul>

<?php
// Appel de la pagination
renderPagination($totalItems, $itemsPerPage, $currentPage, basename(__FILE__));
?>

</body>
</html>
