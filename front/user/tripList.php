<?php
session_start();

$_SESSION['navSelected'] = 'search';

?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Résultats de recherche - EcoRide</title>
  <link rel="stylesheet" href="../css/style.css" />
  <link rel="stylesheet" href="../css/tripList.css" />
 
</head>
<body>
  <header>

  <!-- Navbar dynamique -->
  <?php include_once '../composants/navbar.php'; ?>

  </header>
  <?php include_once '../composants/inProgress.php'; ?>
  <main>
  
    <div class="trips-container">
        
      <h2 style = "color:black">Résultats de recherche</h2>
        <div class="top-buttons">
            <button type="button"  class = "blue" onclick="location.href='search.php'" >⬅ Retour</button>
        </div>
      <div class="trip-cards">

        <!-- Exemple de carte -->
        <div class="trip-card">

          <p><strong>Départ :</strong> Paris</p>
          <p><strong>Arrivée :</strong> Lyon</p>
          <p><strong>Date :</strong> 22/04/2025 - <strong>Heure :</strong> 08:30</p>
          <p><strong>Conducteur :</strong> KevinDriver - <span class="stars">★★★★☆</span> (4.0)</p>
          <p><strong>Véhicule :</strong> Tesla Model 3</p>
          <p><strong>Places restantes :</strong> 2</p>
          <p><strong>Prix :</strong> 15 crédits</p>
          <p><strong>Écologique :</strong> ✅ Oui</p>
          <div class="see-details">
            <button>Voir les détails</button>
          </div>

      </div>

      <!-- Exemple 2 -->
      <div class="trip-card">

        <p><strong>Départ :</strong> Marseille</p>
        <p><strong>Arrivée :</strong> Toulouse</p>
        <p><strong>Date :</strong> 23/04/2025 - <strong>Heure :</strong> 10:15</p>
        <p><strong>Conducteur :</strong> JulieRider - <span class="stars">★★★★★</span> (5.0)</p>
        <p><strong>Véhicule :</strong> Renault Zoé</p>
        <p><strong>Places restantes :</strong> 1</p>
        <p><strong>Prix :</strong> 12 crédits</p>
        <p><strong>Écologique :</strong> ✅ Oui</p>
        <div class="see-details">
          <button>Voir les détails</button>
        </div>
      </div>

    </div>

    <!-- Pagination -->
    <div class="pagination">
      ← Précédent | Page 1 sur 3 | Suivant →
    </div>
    </div>
  </main>
    <!-- Footer -->
    <?php include_once '../composants/footer.html'; ?>
</body>
</html>
