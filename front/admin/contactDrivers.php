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
  <title>Contacter les conducteurs - Employé | EcoRide</title>
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
      <h2>Contacter les conducteurs</h2>

      <!-- Boutons haut -->
      <div class="top-buttons">
      <button type="button"  class = "blue" onclick="location.href='manage.php'" >⬅ Retour</button>
      </div>

      <!-- Barre de recherche -->
      <div class="search-bar">
        <input type="text" placeholder="Rechercher un conducteur par pseudo..." />
        <button type="submit" >🔍 Rechercher</button>
      </div>

      <!-- Liste des conducteurs -->
      <div class="conducteurs-liste">

        <div class="conducteur-card">
          <p><strong>Nom :</strong> Kevin Dubois</p>
          <p><strong>Email :</strong> kevin.dubois@mail.com</p>
          <p><strong>Nombre de trajets :</strong> 12</p>
          <p><strong>Note moyenne :</strong> ★★★★☆</p>
          <button type="button" class="yellow">Contacter par mail</button>
        </div>

        <div class="conducteur-card">
          <p><strong>Nom :</strong> Emma Moreau</p>
          <p><strong>Email :</strong> emma.moreau@mail.com</p>
          <p><strong>Nombre de trajets :</strong> 8</p>
          <p><strong>Note moyenne :</strong> ★★★★★</p>
          <button type="button" class="yellow">Contacter par mail</button>
        </div>

      </div>

      <!-- Pagination simulée -->
      <div class="pagination">
        ← Précédent | Page 1 sur 2 | Suivant →
      </div>
    </div>
  </main>
    <br><br>
     <!-- footer -->
     <?php include_once '../composants/footer.html'; ?>

</body>
</html>
