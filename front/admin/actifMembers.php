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
  <title>Membres actifs | EcoRide</title>
  <link rel="stylesheet" href="../css/style.css" />
  <link rel="stylesheet" href="../css/manage.css" />
</head>
<body>

  <div class="form-container manage-container">
    <h2>Membres actifs</h2>

    <div class="search-bar">
      <input type="text" placeholder="Laisser vide pour afficher tous les membres...">
      <button type="submit">🔍 Rechercher</button>
    </div>

    <div class="user-list">
      <div class="user-card">
        <p><strong>Pseudo :</strong> exemple_user</p>
        <p><strong>Email :</strong> user@email.com</p>
        <p><strong>Rôle :</strong> Utilisateur actif</p>
        
<button class="red">Bloquer ce membre</button>
<button class="blue">Contacter</button>

      </div>
    </div>

    <div class="pagination">
      ← Précédent | Page 1 sur 1 | Suivant →
    </div>
  </div>

</body>
</html>
