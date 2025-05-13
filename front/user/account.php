<?php
// chargement des classes et demarage de session 
require_once '../composants/autoload.php';
// bouton selected navBarr
$_SESSION['navSelected'] = 'account';

// Redirection si non connecté en tant que SimpleUser ou Driver
include_once '../../back/composants/checkAccess.php';
checkAccess(['SimpleUser', 'Driver']);

// Récupération des infos utilisateur depuis l'objet User en session
$user = $_SESSION['user'];
$pseudo = $user->getPseudo();
$credits = $user->getCredits();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Mon Compte - EcoRide</title>
  <link rel="stylesheet" href="../css/style.css" />
  <link rel="stylesheet" href="../css/account.css">
  <!-- google font -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Lemonada:wght@300..700&display=swap" rel="stylesheet">
</head>
<body>

<header>
  <?php include_once '../composants/navbar.php'; ?>
</header>
<?php include_once '../composants/inProgress.php'; ?>
<div class="account-container">
  <div class="header-info">
    <div><strong>Connecté en tant que :</strong> <?= htmlspecialchars($pseudo) ?> &nbsp;</div>
    <div class="credit">
      <strong>Crédits :</strong> <?= $credits ?>
      <img src="../img/ico/coins.png" alt="Pièces" class="coin-icon">
      &nbsp;&nbsp;
      <?php if ($user instanceof Driver): ?>
        <strong>Note :</strong> <?= number_format($user->getAverageRating(), 1) ?> / 5
        &nbsp;&nbsp; <strong>Statut permis :</strong>
        <span style="color: green; font-weight:bold;">✔ Vérifié</span>
      <?php endif; ?>
    </div>
  </div>

  <!-- Profil passager -->
  <?php if ($user instanceof SimpleUser && !$user instanceof Driver ): ?>
    <div class="section">
      <h4>Devenir conducteur</h4>
      <p> Vous souhaitez proposer des trajets ?</p>
      <button id="toggleDriverForm">Postuler en tant que conducteur</button>
    </div>
    <div class="section" id="driverForm" style="display: none;">
      <h4>Fournir votre permis</h4>
      <form method="post" enctype="multipart/form-data" action="upload_permis.php">
        <label for="permis">Téléverser une photo de votre permis :</label>
        <input type="file" name="permis" id="permis" accept="image/*" required>
        <button type="submit">Envoyer la demande</button>
      </form>
    </div>
  <?php endif; ?>

  <?php if ($user instanceof Driver): ?>
    <!-- Préférences conducteur -->
    <div class="section">
      <h4>Vos préférences</h4>
      <?php $prefs = $user->getPreferences(); ?>
      <form method="post" action="../driver/updatePreferences.php">
        <div class="preferences">
          <label><input type="checkbox" name="smoker" <?= !empty($prefs['allows_smoking']) ? 'checked' : '' ?>> Fumeur autorisé</label>
          <label><input type="checkbox" name="pets" <?= !empty($prefs['allows_pets']) ? 'checked' : '' ?>> Animaux autorisés</label>
          <label>Remarques personnelles :</label>
          <input type="text" name="note_personnelle" value="<?= htmlspecialchars($prefs['note_personnelle'] ?? '') ?>">
        </div>
        <button type="submit">Enregistrer les préférences</button>
      </form>
    </div>

    <!-- Gestions des véhicules -->
    <div class="section">
      <h4>Véhicules enregistrés</h4>
      <form method="post" action="../driver/deleteVehicle.php">
        <label for="vehicule">Sélectionner un véhicule :</label><br>
        <select name="vehicle_id" id="vehicule" onchange="showVehiclePreferences(this.value)">
          <option value="">-- Choisir un véhicule --</option>
          <?php foreach ($user->getVehicles() as $v): ?>
            <option value="<?= $v['id'] ?>"><?= htmlspecialchars($v['brand'] . ' ' . $v['model']) ?> - <?= $v['seats'] ?> places</option>
          <?php endforeach; ?>
        </select>
        <button type="submit" class="delete-vehicle">🗑 Supprimer</button>
        <button type="button" onclick="location.href='../driver/updateDocuments.php'">📄 Mettre à jour les documents</button>
      </form>

      <label>Ajouter un véhicule :</label><br>
      <button onclick="location.href='../driver/addCar.php'">➕ Ajouter un véhicule</button>

      <div id="vehicle-preferences" class="section" style="display:none;">
        <p><strong>Préférences du véhicule sélectionné :</strong></p>
        <ul>
          <li>Fumeur : Oui</li>
          <li>Animaux : Non</li>
        </ul>
      </div>
    </div>
  <?php endif; ?>

  <!-- Actions disponibles pour tous les utilisateurs -->
  <div class="button-group">
    <button onclick="location.href='./showMyTrips.php'">Mes trajets réservés</button>
    <button onclick="location.href='../user/tripsStory.php'">Historique des voyages / gestion des avis</button>
    <button onclick="location.href='../user/addCredits.php'">Obtenir des crédits</button>
    <button onclick="location.href='../user/cashBack.php'">Demander un remboursement</button>
    <?php if ($user instanceof Driver): ?>
      <button onclick="location.href='../driver/addTRip.php'">Proposer un trajet</button>
      <button onclick="location.href='avisRecus.php'">Mes avis reçus</button>
      <button onclick="location.href='../driver/convertCredits.php'">💰 Obtenir un paiement</button>
    <?php endif; ?>
  </div>
</div>

<!-- footer -->
<?php include_once '../composants/footer.html'; ?>

<!-- JS interactivité -->
<script>
  const toggleBtn = document.getElementById("toggleDriverForm");
  if (toggleBtn) {
    toggleBtn.addEventListener("click", function () {
      const form = document.getElementById("driverForm");
      form.style.display = form.style.display === "none" ? "block" : "none";
    });
  }

  function showVehiclePreferences(value) {
    const block = document.getElementById("vehicle-preferences");
    block.style.display = value ? "block" : "none";
  }
</script>

</body>
</html>
