<?php
session_start();

$_SESSION['navSelected'] = 'account';
// Redirection si non connecté
if (!isset($_SESSION['typeOfUser']) || ($_SESSION['typeOfUser']!= "user" && $_SESSION['typeOfUser'] != "driver")) {
  header('Location: login.php');
  exit();
}

$type = $_SESSION['typeOfUser'];
$pseudo = $_SESSION['pseudo'] ?? 'Utilisateur';
$credits = $_SESSION['credits'] ?? 20;

?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Mon Compte - EcoRide</title>
  <link rel="stylesheet" href="../css/style.css" />
  <link rel="stylesheet" href="../css/account.css">
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
      <?php if ($type === 'driver'): ?>
        <strong>Note :</strong> 4.5 / 5 <span style="color: gold;">★ ★ ★ ★ ☆</span>
        &nbsp;&nbsp; <strong>Statut permis :</strong>
        <!-- sera dynamique une fois le back codé -->
        <span style="color: green; font-weight:bold;">✔ Vérifié</span>
      <?php endif; ?>
    </div>
  </div>

  <?php if ($type === 'user'): ?>
    <!-- Bloc utilisateur de base (non conducteur) -->
    <div class="section">
      <h3>Devenir conducteur</h3>
      <p>Vous souhaitez proposer des trajets ?</p>
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

  <?php if ($type === 'driver'): ?>
    <!-- Préférences modifiables -->
    <div class="section">
      <h4>Vos préférences</h4>
      <div class="preferences">
        <label><input type="checkbox" name="smoker" onchange="showSaveBtn()"> Fumeur autorisé</label>
        <label><input type="checkbox" name="pets" onchange="showSaveBtn()"> Animaux autorisés</label>
      </div>
      <button id="savePreferencesBtn">Enregistrer les préférences</button>
    </div>

    <!-- Véhicules -->
    <div class="section">
      <h4>Véhicules enregistrés</h4>
      <label for="vehicule">Sélectionner un véhicule :</label><br>
      <select name="vehicule" id="vehicule" onchange="showVehiclePreferences(this.value)">
        <option value="">-- Choisir un véhicule --</option>
        <option value="zoe">Renault Zoé - 4 places</option>
        <option value="tesla">Tesla Model 3 - 5 places</option>
      </select>
      <button class="delete-vehicle">🗑</button>
      <br>
      <!-- ajouter un véhicule -->
      <button onclick="location.href='../driver/addCar.php'">➕</button>


      <div id="vehicle-preferences" class="section" style="display:none;">
        <p><strong>Préférences du véhicule sélectionné :</strong></p>
        <ul>
          <li>Fumeur : Oui</li>
          <li>Animaux : Non</li>
        </ul>
      </div>
    </div>
  <?php endif; ?>

  <!-- Boutons pour tous -->
  <div class="button-group">
    <button onclick="location.href='../user/showMyTrips.php'">Mes trajets réservés</button>
    <button onclick="location.href='../user/tripsStory.php'">Historique des voyages</button>
    <button onclick="location.href='../user/showMyreviews.php'">Mes commentaires</button>
    <button onclick="location.href='../user/addCredits.php'">Obtenir des crédits</button>
    <button onclick="location.href='../user/cashBack.php'">Demander un remboursement</button>
    <?php if ($type === 'driver'): ?>
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

  function showSaveBtn() {
    document.getElementById("savePreferencesBtn").style.display = "inline-block";
  }

  function showVehiclePreferences(value) {
    const block = document.getElementById("vehicle-preferences");
    if (value) {
      block.style.display = "block";
    } else {
      block.style.display = "none";
    }
  }
</script>

</body>
</html>
