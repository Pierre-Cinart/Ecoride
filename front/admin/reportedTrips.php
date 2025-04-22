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
  <title>Trajets signalés - Employé | EcoRide</title>
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
      <h2>Trajets signalés</h2>

      <!-- Boutons haut -->
      <div class="top-buttons">
        <button type="button"  class = "blue" onclick="location.href='manage.php'" >⬅ Retour</button>
        <button type="button" class="yellow">Trajets en attente</button>
        <button type="button" class="green">Trajets traités</button>
      </div>

      <!-- Liste des trajets signalés -->
      <div class="signalement-liste">

        <div class="signalement-card">
          <p><strong>Numéro trajet :</strong> #TRJ1024</p>
          <p><strong>Conducteur :</strong> Kevin</p>
          <p><strong>Passager :</strong> Laura</p>
          <p><strong>Date :</strong> 2025-04-10</p>
          <p><strong>Motif :</strong> Le conducteur ne s’est pas présenté au rendez-vous.</p>
          <p><strong>Contact :</strong> kevin@mail.com | laura@mail.com</p>
          <button type="button" class="green">Traiter le signalement</button>
          <button type="button" class="blue">Contacter les utilisateurs</button>
        </div>

        <div class="signalement-card">
          <p><strong>Numéro trajet :</strong> #TRJ1027</p>
          <p><strong>Conducteur :</strong> Emma</p>
          <p><strong>Passager :</strong> Jules</p>
          <p><strong>Date :</strong> 2025-04-09</p>
          <p><strong>Motif :</strong> Trajet annulé à la dernière minute.</p>
          <p><strong>Contact :</strong> emma@mail.com | jules@mail.com</p>
          <button type="button" class="green">Traiter le signalement</button>
          <button type="button" class="blue">Contacter les utilisateurs</button>
        </div>

      </div>

      <!-- Pagination simulée -->
      <div class="pagination">
        ← Précédent | Page 1 sur 2 | Suivant →
      </div>
    </div>

  </main>
  <!-- footer -->
  <?php include_once '../composants/footer.html'; ?>
  
</body>
</html>
