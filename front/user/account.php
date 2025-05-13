<?php
  // chargement des classes et demarage de session 
  require_once '../../back/composants/autoload.php';
  // bouton selected navBarr
  $_SESSION['navSelected'] = 'account';

  // Redirection si non connecté en tant que SimpleUser ou Driver
  checkAccess(['SimpleUser', 'Driver']);

  // Récupération des infos utilisateur depuis l'objet User en session
  $user = $_SESSION['user'];
  $pseudo = $user->getPseudo();
  $credits = $user->getCredits();
  // Initialise un tableau vide, dispo globalement pour la sélection de véhicules 
  $vehicleObjects = [];
  // Mise à jour automatique des infos session si c’est un conducteur
  if ($user instanceof Driver) {
      $user->updateUserSession($pdo);

      // Génère dynamiquement les objets Vehicle à partir des IDs
      $vehicleObjects = [];
      foreach ($user->getVehicles() as $vehicleId) {
          try {
              $vehicleObjects[] = new Vehicle($pdo, $vehicleId);
          } catch (Exception $e) {
              // Optionnel : journaliser ou ignorer les erreurs (véhicule introuvable, etc.)
              continue;
          }
      }    
  }

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
  <!-- === SECTION PRÉFÉRENCES CONDUCTEUR === -->
  <div class="section">
    <h4>Vos préférences en tant que conducteur</h4>
    <p>Ces préférences sont personnelles et s’appliqueront à tous les trajets que vous proposerez, quel que soit le véhicule.</p>

    <?php
      // Récupération des préférences via l'objet Driver
      $prefs = $user->getPreferences();  // ['allows_smoking' => 0/1, 'allows_pets' => 0/1, 'note_personnelle' => string]
    ?>

    <!-- Formulaire de mise à jour des préférences conducteur -->
    <form method="post" action="../driver/updatePreferences.php">
      <div class="preferences">

        <!-- Préférence : fumeur autorisé -->
        <label>
          <input type="checkbox" name="smoker" <?= !empty($prefs['allows_smoking']) ? 'checked' : '' ?>>
          Fumeur autorisé
        </label>

        <!-- Préférence : animaux autorisés -->
        <label>
          <input type="checkbox" name="pets" <?= !empty($prefs['allows_pets']) ? 'checked' : '' ?>>
          Animaux autorisés
        </label>

        <!-- Remarque personnelle -->
        <label for="note_personnelle">Remarques personnelles :</label>
        <input
          type="text"
          id="note_personnelle"
          name="note_personnelle"
          value="<?= htmlspecialchars($prefs['note_personnelle'] ?? '') ?>"
          placeholder="Ex : je préfère une ambiance calme"
        >

      </div>

      <!-- Bouton d'enregistrement   A CAMOUFLER SI PAS DE CHANGEMENT DE PREFERENCE--> 
      <button type="submit" id="btnSavePrefs" class="hidden">Enregister les préférences</button>

    </form>
  </div>

  <!-- === SECTION GESTION DES VÉHICULES === -->
<div class="section">
  <h4>Vos véhicules enregistrés</h4>
  <form method="post" action="../driver/deleteVehicle.php">
    <label for="vehicle_id">Sélectionnez un véhicule :</label><br>
    <!-- Menu déroulant liste de véhicules -->
    <select name="vehicle_id" id="vehicle_id">
      <option value="">-- Choisir un véhicule --</option>
      <?php foreach ($vehicleObjects as $vehicle): ?>
        <option value="<?= $vehicle->getId() ?>">
          <?= htmlspecialchars($vehicle->getDisplayName()) ?>
        </option>
      <?php endforeach; ?>
    </select>

    <!-- Actions liées au véhicule sélectionné -->
    <div style="margin-top: 10px;">
      <button type="button" onclick="location.href='../driver/deleteCar.php'" id="btnDeleteVehicle" class="delete-vehicle red hidden">🗑 Supprimer le véhicule</button>
      <button type="button" onclick="location.href='../driver/updateCar.php'" id="btnUpdateDocuments" class="delete-vehicle blue hidden">Mettre à jour les documents</button>
      <button type="button" onclick="location.href='../driver/addCar.php'">➕ Ajouter un véhicule</button>
    </div>
  </form>
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

<script src="../js/account.js"></script>

</body>
</html>
