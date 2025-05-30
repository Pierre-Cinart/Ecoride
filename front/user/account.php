<?php
  // === Initialisation et sécurité ===
  require_once '../../back/composants/autoload.php';
  $_SESSION['navSelected'] = 'account';
  checkAccess(['SimpleUser', 'Driver']);

  //assigne l objet User en session
  $user = $_SESSION['user'];
  //met à jour les données du User //fonction à améliorer pour recevoir avant d envoyer
  $user->updateUserSession($pdo);

  //recupération  d informations User
  $pseudo = $user->getPseudo();
  $credits = $user->getCredits();
  $status = $user->getStatus();
  $permitStatus = $user->getPermitStatus();

  //tableau pour la liste de véhicules
  $vehicleObjects = [];
  //injecte les véhicules dans le tableau si ses documents ont étaient vérifiés
  if ($user instanceof Driver) {
    foreach ($user->getVehicles() as $vehicleId) {
      try {
        $veh = new Vehicle($pdo, $vehicleId);
      
          $vehicleObjects[] = $veh;
        
      } catch (Exception $e) {
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
    <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Lemonada:wght@300..700&display=swap" rel="stylesheet">
</head>
<body>

  <header>
    <?php include_once '../composants/navbar.php'; ?>
  </header>

  <?php include_once '../composants/inProgress.php'; ?>

  <div class="account-container">
    <!-- === Informations utilisateur === -->
    <div class="header-info">
      <div><strong>Connecté en tant que :</strong> <?= htmlspecialchars($pseudo) ?> &nbsp;&nbsp;</div>
      <div class="credit">
       <strong>Crédits :</strong> <?= $credits ?>
        <img src="../img/ico/coins.png" alt="Pièces" class="coin-icon"><br> 
        <?php if ($user instanceof Driver): ?>
          &nbsp;&nbsp; <strong>Note :</strong> <?= number_format($user->getAverageRating(), 1) ?> / 5
        <?php endif; ?>
        <!-- affiche l ' état de validation du permis  -->
        <?php if ($permitStatus != "waiting"):?>
            <strong>Verification du permis :</strong>
          <?php
            $permitStatus = $user->getPermitStatus();
            $color = match ($permitStatus) {
              'approved' => 'green',
              'pending' => 'orange',
              'refused' => 'red',
              default => 'gray'
            };
            $label = match ($permitStatus) {
              'approved' => '✔ Vérifié',
              'pending' => '⏳ En attente',
              'refused' => '❌ Refusé',
              default => 'Non défini'
            };
          ?>
        <span style="color: <?= $color ?>; font-weight:bold;"><?= $label ?></span>
        <?php endif; ?>

        
      </div>
      <!-- alertes avertissements et blocages -->
      <?php if ($status === 'blocked' || $status === 'all_blocked'): ?>
        <div class="red-alert">
          Votre compte ne permet plus de réserver de trajets <br>
          Veuillez contacter l'équipe EcoRide pour gérer votre situation.
        </div>
      <?php endif; ?>

      <?php if ($status === 'drive_blocked'): ?>
        <div class="red-alert">
          Votre compte ne permet plus de proposer de trajets ni de gérer vos véhicules <br>
          Veuillez contacter l'équipe EcoRide pour gérer votre situation.
        </div>
      <?php endif; ?>
    </div>

    <!-- === Section conducteur non inscrit === -->
    <?php if ($user instanceof SimpleUser && $permitStatus === "waiting"): ?>
      <div class="section">
        <h4>Devenir conducteur</h4>
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

    <!-- === Préférences conducteur === -->
    <?php if ($user instanceof Driver): ?>
      <div class="section">
        <h4>Vos préférences en tant que conducteur</h4>
        <p>Ces préférences s'appliquent à tous les trajets que vous proposerez.</p>
        <?php $prefs = $user->getPreferences(); ?>

        <form method="post" action="../driver/updatePreferences.php">
          <div class="preferences">
            <label>
              <input type="checkbox" name="smoker" <?= !empty($prefs['allows_smoking']) ? 'checked' : '' ?>>
              Fumeur autorisé
            </label>

            <label>
              <input type="checkbox" name="pets" <?= !empty($prefs['allows_pets']) ? 'checked' : '' ?>>
              Animaux autorisés
            </label>

            <label for="note_personnelle">Remarques personnelles :</label>
            <input type="text" id="note_personnelle" name="note_personnelle" value="<?= htmlspecialchars($prefs['note_personnelle'] ?? '') ?>" placeholder="Ex : je préfère une ambiance calme">
          </div>

          <button type="submit" id="btnSavePrefs" class="hidden">Enregistrer les préférences</button>
        </form>
      </div>

      <!-- === Gestion des véhicules === -->
      <?php if ($status != 'drive_blocked' && $status != 'all_blocked'): ?>
        <div class="section">
          <h4>Vos véhicules enregistrés</h4>
          <form method="post" action="../driver/deleteVehicle.php">
            <label for="vehicle_id">Sélectionnez un véhicule :</label>
            <select name="vehicle_id" id="vehicle_id">
              <option value="">-- Choisir un véhicule --</option>
              <?php foreach ($vehicleObjects as $vehicle): ?>
                <option value="<?= $vehicle->getId() ?>"><?= htmlspecialchars($vehicle->getDisplayName()) ?></option>
              <?php endforeach; ?>
            </select>

            <div style="margin-top: 10px;">
              <button type="button" onclick="ajaxDeleteVehicle()" id="btnDeleteVehicle" class="delete-vehicle red hidden">🗑 Supprimer le véhicule</button>
              <button type="button" id="btnUpdateDocuments" class="delete-vehicle blue hidden">Mettre à jour les documents</button>
              <button type="button" onclick="location.href='../driver/addVehicle.php'">➕ Ajouter un véhicule</button>
            </div>
          </form>
        </div>
      <?php endif; ?>
    <?php endif; ?>
    <!-- upload des documents -->
    <div id="edit-documents-block" class="document-update hidden">
        <h3>Modifier les documents du véhicule</h3>
        <form method="POST" action="#" enctype="multipart/form-data">
          <input type="hidden" name="vehicle_id" id="vehicle_id_input">

          <div class="form-group">
            <label for="registration_document">Carte grise :</label>
            <input type="file" name="registration_document" id="registration_document" accept=".pdf,.jpg,.jpeg,.png,.webp">
          </div>

          <div class="form-group">
            <label for="insurance_document">Assurance :</label>
            <input type="file" name="insurance_document" id="insurance_document" accept=".jpg,.jpeg,.png,.webp">
          </div>

          <div class="form-group">
            <label for="picture_document">Photo :</label>
            <input type="file" name="picture_document" id="picture_document" accept=".jpg,.jpeg,.png,.webp">
          </div>

          <button type="submit" id = "btnSendDocuments" class = "green" onclick = "sendDocuments()">Télécharger une photo</button>
        </form>
    </div>

    <!-- === Actions générales === -->
     <!-- consulter et annuler des voyage prévus en conducteur -->
    <div class="button-group">
      <?php if ($user instanceof Driver): ?>
        <button onclick="location.href='../driver/OwnTrips.php'">Mes trajets prévus</button>
      <?php endif; ?>

      <button onclick="location.href='./showMyTrips.php'">Mes trajets réservés</button>
      <button onclick="location.href='../user/tripsStory.php'">Historique des voyages / gestion des avis</button>
      <?php if ($status != 'blocked'):?>
        <button onclick="location.href='../user/addCredits.php'">Obtenir des crédits</button>
      <?php endif; ?>
      <button onclick="location.href='../user/cashBack.php'">Demander un remboursement</button>
        <!-- action suppplémentaires driver -->
      <?php if ($user instanceof Driver): ?>
        <?php if ($status != 'drive_blocked' && $status != 'all_blocked' && $permitStatus === "approved"): ?>
          <button onclick="location.href='../driver/addTRip.php'">Proposer un trajet</button>
        <?php endif; ?>
        <button onclick="location.href='avisRecus.php'">Mes avis reçus</button>
        <button onclick="location.href='../driver/convertCredits.php'">💰 Obtenir un paiement</button>
      <?php endif; ?>
      <button onclick="location.href='alertUser.php'">Signaler un utilisateur</button>
    </div>
  </div>

  <footer>
    <?php include_once '../composants/footer.php'; ?>
  </footer>

  <script src="../js/account.js"></script>
  </body>
</html>
