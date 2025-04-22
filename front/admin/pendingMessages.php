<?php
session_start();

$_SESSION['navSelected'] = 'messages';
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
  <title>Historique des messages - EcoRide</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="../css/style.css" />
  <link rel="stylesheet" href="../css/manage.css" />

</head>
<body>
  <header>
      <?php include_once '../composants/navbar.php'; ?>
  </header>

    <?php include_once '../composants/inProgress.php'; ?>

  <div class="form-container manage-container">
    <!-- Bouton retour -->
    <button class="blue" onclick="location.href='messages.php'">⬅ Retour</button>

    <h2>Historique des messages</h2>

    <!-- Barre de recherche -->
    <div class="search-bar">
      <input type="text" placeholder="Laisser vide pour afficher tous les messages...">
      <button type="submit">🔍 Rechercher</button>
    </div>

    <!-- Liste des messages -->
    <div class="message-list">
      <div class="message-item">
        <p><strong>Objet :</strong> Suggestion</p>
        <p><strong>De :</strong> emilie.travail@email.com</p>
        <p><strong>Aperçu :</strong> Bonjour, je voulais simplement dire que j’ai trouvé le service très pratique...</p>
        <div class="message-actions">
          <button onclick="location.href='conversation.php?id=1'">📄 Lire toute la conversation</button>
          <button class="red" onclick="alert('Message supprimé (exemple)')">🗑 Supprimer le message</button>
        </div>
      </div>

      <div class="message-item">
        <p><strong>Objet :</strong> Problème de paiement</p>
        <p><strong>De :</strong> pierre87@email.com</p>
        <p><strong>Aperçu :</strong> J’ai effectué un paiement pour un trajet mais je n’ai pas reçu de confirmation...</p>
        <div class="message-actions">
          <button onclick="location.href='conversation.php?id=2'">📄 Lire toute la conversation</button>
          <button class="red" onclick="alert('Message supprimé (exemple)')">🗑 Supprimer le message</button>
        </div>
      </div>
    </div>

    <!-- Pagination -->
    <div class="pagination">
      ← Précédent | Page 1 sur 3 | Suivant →
    </div>
  </div>
    <!-- footer -->
    <?php include_once '../composants/footer.html'; ?>
</body>
</html>
