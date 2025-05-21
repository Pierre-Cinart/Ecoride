<?php
require_once '../../back/composants/autoload.php';
require_once '../../back/composants/paginate.php';
require_once '../composants/btnBack.php';
checkAccess(['Admin','Employee']);
$_GET = sanitizeArray($_GET, './manage.php');
$status = $_GET['status'] ?? 'waiting';
if (!in_array($status, ['waiting', 'accepted'])) {
    header('Location: ./manage.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Gérer les avis | EcoRide</title>
  <link rel="stylesheet" href="../css/style.css" />
  <link rel="stylesheet" href="../css/manage.css" />
    <!-- font -->
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
   <div class="form-container manage-container">
    <h2>Gérer les avis</h2>

    <!-- Boutons haut -->
    <div class="top-buttons">
      <button type="button"  class = "blue" onclick="location.href='manage.php'" >⬅ Retour</button>
      <button type="button" class="yellow" onclick="location.href='manageReviews.php?status=waiting'">Avis en attente</button>
      <button type="button" class="green" onclick="location.href='manageReviews.php?status=accepted'">Avis validés</button>
    </div>
    <form method="post action='./manageReviews.php'">
        <input type="hidden" name="action" value=""><!-- bien configurer la search bar-->
        <input type="text" name="search" placeholder="Rechercher par pseudo">
        <button type="submit">🔍</button>
    </form>
    <!-- Liste des avis a chargé selon le post status de cette facon dynamique -->
    <div class="avis-liste">

      <div class="avis-card"><!-- charger les info d une facon dynamique-->
        <p><strong>Passager :</strong> Laura</p>
        <p><strong>Conducteur :</strong> Kevin</p>
        <p><strong>Note :</strong> ★★★★☆</p><!-- les étoile par rapport à la note en attente -->
        <p><strong>Avis :</strong> Le trajet s'est bien passé, le conducteur est ponctuel.</p>
        <!-- pour les boutons ici afficher ca si post staus === 'wait' si non si status === approuved afficher un bouton supprimer -->
        <form action="../../back/manageReviews.php" method="post">
          <input type="hidden" name="review_id" value="123"> <!-- ID de l'avis à traiter -->
          <button type="submit" name="action" value="approve">Valider l'avis</button>
          <button type="submit" name="action" value="refused">Refuser l'avis</button>
        </form>

      </div>
    </div>
 </main>

    <!-- footer -->
  <?php include_once '../composants/footer.php'; ?>
  <!-- lancer la fonction de pagination correctement paramétrée -->
  
</body>
</html>
