<?php
require_once '../../back/composants/autoload.php';
checkAccess(['Admin']);

$_SESSION['navSelected'] = 'stats';
$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Tableau de bord - Admin | EcoRide</title>
  <link rel="stylesheet" href="../css/style.css" />
  <link rel="stylesheet" href="../css/charts.css" />
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
  <main>
    <div class="admin-dashboard">

      <!--  Bouton pour afficher les comptes bloqués -->
      <button id="show-blocked">Afficher les comptes bloqués</button>

      <!--  Filtre date par semaine -->
      <div class="date-filter">
        <label for="week-date">Choisir une semaine :</label>
        <input type="date" id="week-date" name="week-date">
      </div>

      <!--  Conteneur des graphiques -->
      <div class="charts">

        <!--  Graphique 1 : trajets par jour -->
        <div class="chart-block">
          <canvas id="trajetsChart"></canvas>
        </div>

        <!--  Graphique 2 : crédits gagnés par jour -->
        <div class="chart-block">
          <canvas id="creditsParJourChart"></canvas>
        </div>

        <!--  Graphique 3 : crédits totaux -->
        <div class="chart-block">
          <canvas id="creditsTotauxChart"></canvas>
        </div>

        <!--  Graphique 4 : visites par jour -->
        <div class="chart-block">
          <canvas id="visitesChart"></canvas>
        </div>

      </div>
    </div>
  </main>
  <br><br>

  <?php include_once '../composants/footer.php'; ?>
  <!-- Chart.js CDN -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="../js/charts.js"></script>
  

  <script>
    // Suppression des attributs height/width par défaut
    document.querySelectorAll("canvas").forEach(canvas => {
      canvas.removeAttribute("height");
      canvas.removeAttribute("width");
    });

   



    // Graphique 3 - Répartition crédits collectés (exemple)
    new Chart(document.getElementById('creditsTotauxChart'), {
      type: 'doughnut',
      data: {
        labels: ['Cette semaine', 'Total restants'],
        datasets: [{
          data: [48, 1734],
          backgroundColor: ['#60775D', '#ccc']
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false
      }
    });

    
  </script>
</body>
</html>
