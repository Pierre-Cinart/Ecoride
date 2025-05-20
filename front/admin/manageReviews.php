<?php
session_start();

$_SESSION['navSelected'] = 'manage';
// Redirection si non connecté
if (!isset($_SESSION['typeOfUser']) || ($_SESSION['typeOfUser']!= "user" && ( $_SESSION['typeOfUser'] != "admin" && $_SESSION['typeOfUser'] != "employee" ) ) ) {
  header('Location: ../user/login.php');
  exit();
}

$type = $_SESSION['typeOfUser'];
$pseudo = $_SESSION['pseudo'] ?? 'Utilisateur';

?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Gérer les avis - Employé | EcoRide</title>
  <link rel="stylesheet" href="../css/style.css" />
  <link rel="stylesheet" href="../css/manage.css" />
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
      <button type="button" class = "yellow">Avis en attente</button>
      <button type="button" class = "green">Avis validés</button>
    </div>

    <!-- Liste des avis -->
    <div class="avis-liste">

      <div class="avis-card">
        <p><strong>Passager :</strong> Laura</p>
        <p><strong>Conducteur :</strong> Kevin</p>
        <p><strong>Note :</strong> ★★★★☆</p>
        <p><strong>Avis :</strong> Le trajet s'est bien passé, le conducteur est ponctuel.</p>
        <button type="button">Valider l'avis</button>
        <button type="button">Refuser l'avis</button>
      </div>

      <div class="avis-card">
        <p><strong>Passager :</strong> Jules</p>
        <p><strong>Conducteur :</strong> Emma</p>
        <p><strong>Note :</strong> ★★★★★</p>
        <p><strong>Avis :</strong> Chauffeuse au top, très agréable.</p>
        <button type="button">Valider l'avis</button>
        <button type="button">Refuser l'avis</button>
      </div>

    </div>

    <!-- Pagination simulée -->
    <div class="pagination">
      ← Précédent | Page 1 sur 3 | Suivant →
    </div>
  </div>
 </main>

    <!-- footer -->
  <?php include_once '../composants/footer.php'; ?>
  
</body>
</html>
