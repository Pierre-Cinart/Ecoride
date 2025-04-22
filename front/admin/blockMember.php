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
  <title>Infos utilisateurs - Employé | EcoRide</title>
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
       <!-- Boutons haut -->
       <div class="top-buttons">
        <button type="button"  class = "blue" onclick="location.href='manage.php'" >⬅ Retour</button>
      </div>
      
      <h2>Liste des utilisateurs</h2>

      <!-- Barre de recherche -->
      <div class="search-bar">
        <input type="text" placeholder="Rechercher un pseudo...">
        <button type="submit">🔍 Rechercher</button>
      </div>
      
      <!-- Liste des utilisateurs -->
      <div class="user-list">

        <div class="user-card">
          <p><strong>Pseudo :</strong> eco_user1</p>
          <p><strong>Nom :</strong> Dupont</p>
          <p><strong>Prénom :</strong> Laura</p>
          <p><strong>Email :</strong> laura.dupont@email.com</p>
          <p><strong>Rôle :</strong> Utilisateur</p>
          <p><strong>Statut :</strong> En attente</p>
          <button type="button" class="green">consulter les documents et avis </button>
          <button type="button" class="red">Bloquer l utilisateurs</button>
          <button type="button" class="blue">Contacter l utilisateurs</button>
        </div>

        <div class="user-card">
          <p><strong>Pseudo :</strong> eco_driver2</p>
          <p><strong>Nom :</strong> Martin</p>
          <p><strong>Prénom :</strong> Kevin</p>
          <p><strong>Email :</strong> kevin.martin@email.com</p>
          <p><strong>Rôle :</strong> Conducteur</p>
          <p><strong>Statut :</strong> Suspendu</p>
          <button type="button" class="green">consulter les documents et avis </button>
          <button type="button" class="red">Bloquer l utilisateurs</button>
          <button type="button" class="blue">Contacter l utilisateurs</button>
        </div>

      </div>

      <!-- Pagination simulée -->
      <div class="pagination">
        ← Précédent | Page 1 sur 5 | Suivant →
      </div>
    </div>
  </main>
     <!-- footer -->
     <?php include_once '../composants/footer.html'; ?>
</body>
</html>
