<?php
  session_start();
  $_SESSION['navSelected'] = 'search';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Détail du covoiturage - EcoRide</title>
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/trip.css">
</head>
<body>
<header>

<!-- Navbar dynamique -->
<?php include_once '../composants/navbar.php'; ?>

</header>
  <main>
    <?php include_once '../composants/inProgress.php'; ?>
    <div class="form-container trip-container">
      <h2>Détail du covoiturage</h2>

      <div class="grid-container">
        <!-- Colonne gauche -->
        <div class="column">
          <!-- Infos trajet -->
          <div class="trip-info">
            <h3>Informations du trajet</h3>
            <p><strong>Départ :</strong> Paris</p>
            <p><strong>Arrivée :</strong> Lyon</p>
            <p><strong>Date :</strong> 22/04/2025</p>
            <p><strong>Heure :</strong> 08:30</p>
            <p><strong>Prix :</strong> 15 crédits</p>
            <p><strong>Places restantes :</strong> 2</p>
            <p><strong>Écologique :</strong> ✅ Oui</p>
          </div>

          <!-- Véhicule -->
          <div class="trip-info">
            <h3>Véhicule utilisé</h3>
            <img src="../img/images/vehicules/KevinDriver/vehicule1.png" alt="Photo du véhicule" class="vehicle-photo">
            <p><strong>Marque :</strong> Tesla</p>
            <p><strong>Modèle :</strong> Model 3</p>
            <p><strong>Couleur :</strong> Blanc</p>
            <p><strong>Énergie :</strong> Électrique</p>
          </div>
        </div>

        <!-- Colonne droite -->
        <div class="column">
          <!-- Infos conducteur -->
          <div class="trip-info">
            <h3>Conducteur</h3>
            <div class="driver-section">
              <img src="../img/images/profils/KevinDriver.png" alt="Photo conducteur" class="driver-photo">
              <div>
                <p><strong>Pseudo :</strong> KevinDriver</p>
                <p><strong>Note :</strong>
                  <span class="stars">★★★★☆</span> (4.0/5)
                </p>
              </div>
            </div>
          </div>

          <!-- Préférences conducteur -->
          <div class="trip-info preferences">
            <h3>Préférences du conducteur</h3>
            <label>🚬 Non fumeur</label>
            <label>🐶 Animaux acceptés</label>
            <label>💬 Discute volontiers</label>
          </div>

          <!-- Avis -->
          <div class="trip-info">
            <h3>Avis passagers</h3>
            <div class="avis">
              <p><strong>Laura :</strong> Très ponctuel, agréable et voiture propre.</p>
              <p><span class="stars">★★★★★</span></p>
            </div>
            <div class="avis">
              <p><strong>Julien :</strong> Tout s’est bien passé, je recommande !</p>
              <p><span class="stars">★★★★☆</span></p>
            </div>
          </div>
        </div>
      </div>

      <!-- Bouton en bas -->
      <div class="btn-center">
        <button type="button">Participer au covoiturage</button>
      </div>
    </div>
  </main>
    <!-- Footer -->
    <?php include_once '../composants/footer.html'; ?>
</body>
</html>
