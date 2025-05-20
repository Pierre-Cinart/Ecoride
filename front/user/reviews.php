<?php
  // chargement des classes et demarage de session 
  require_once '../composants/autoload.php';
  //bouton select dans la navbar
  $_SESSION['navSelected'] = 'reviews';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Avis des utilisateurs - EcoRide</title>
  <link rel="stylesheet" href="../css/style.css" />
  <link rel="stylesheet" href="../css/reviews.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Lemonada:wght@300..700&display=swap" rel="stylesheet">
</head>
<body>

  <header>
    <?php include_once '../composants/navbar.php'; ?>
  </header>

  <main>
  <?php include_once '../composants/inProgress.php'; ?>
    <section class="reviews-section">
      <h2>Les avis des passagers</h2>
      <p>Consultez les retours d’expérience des utilisateurs ayant voyagé avec nos conducteurs.</p>

      <!-- Exemple d'avis -->
      <div class="review-card">
        <p><strong>Laura</strong> sur <em>KevinDriver</em> <span class="stars">★★★★★</span></p>
        <p>Très ponctuel, agréable et voiture propre.</p>
      </div>

      <div class="review-card">
        <p><strong>Julien</strong> sur <em>JulieRider</em> <span class="stars">★★★★☆</span></p>
        <p>Tout s’est bien passé, je recommande !</p>
      </div>

      <div class="review-card">
        <p><strong>Emma</strong> sur <em>EcoRider69</em> <span class="stars">★★★☆☆</span></p>
        <p>Le trajet était correct mais un peu de retard à l’arrivée.</p>
      </div>

      <!-- Pagination factice -->
      <div class="pagination">← Précédent | Page 1 sur 3 | Suivant →</div>
    </section>
  </main>

  <!-- footer -->
 <?php include_once '../composants/footer.php'; ?>
</body>
</html>
