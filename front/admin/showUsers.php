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
  <title>Documents & Avis - Employé | EcoRide</title>
  <link rel="stylesheet" href="../css/style.css" />
  <link rel="stylesheet" href="../css/manage.css" />
</head>

<body>

  <header>
    <?php include_once '../composants/navbar.php'; ?>
  </header>

  <?php include_once '../composants/inProgress.php'; ?>
  <main>
    <div class="manage-container">
      <h2>Documents & Avis du conducteur</h2>

      <div class="top-buttons">
        <button type="button"  class = "blue" onclick="location.href='manage.php'" >⬅ Retour</button>
        <button class="green">Valider le permis</button>
        <button class="red">Rejeter le permis</button>
      </div>

      <div class="document-box">
        <h3>Permis de conduire</h3>
        <!-- Simulation d’un PDF ou image -->
        <iframe src="../img/permis.png" title="Permis de conduire"></iframe>
      </div>

      <div class="avis-box">
        <h3>Avis reçus</h3>

        <div class="avis-item">
          <p><strong>Passager :</strong> Jules</p>
          <p><strong>Note :</strong> <span class="stars">★★★★☆</span></p>
          <p><strong>Avis :</strong> Conducteur ponctuel, très respectueux des règles.</p>
        </div>

        <div class="avis-item">
          <p><strong>Passager :</strong> Emma</p>
          <p><strong>Note :</strong> <span class="stars">★★★★★</span></p>
          <p><strong>Avis :</strong> Très agréable et voiture propre, je recommande !</p>
        </div>
      </div>
    </div>
  </main>
  
  <!-- footer -->
  <?php include_once '../composants/footer.html'; ?>

</body>
</html>
