<?php
session_start();

$_SESSION['navSelected'] = 'manage';
// Redirection si non connecté
if (!isset($_SESSION['typeOfUser']) || ($_SESSION['typeOfUser']!= "admin" ) ) {
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
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Tableau de bord - Admin | EcoRide</title>

  <!-- Chart.js CDN -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <!-- Ton CSS principal -->
  <link rel="stylesheet" href="../css/style.css" />
  <link rel="stylesheet" href="../css/charts.css" />
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

      </div>

    </div>
  </main>

  <br><br>
     <!-- footer -->
     <?php include_once '../composants/footer.html'; ?>
     
  <script>
    //  Supprime les attributs height/width par défaut pour éviter le débordement
    document.querySelectorAll("canvas").forEach(canvas => {
      canvas.removeAttribute("height");
      canvas.removeAttribute("width");
    });

    // Graphique 1 - Nombre de trajets par jour
    new Chart(document.getElementById('trajetsChart'), {
      type: 'bar',
      data: {
        labels: ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
        datasets: [{
          label: 'Trajets par jour',
          data: [22, 35, 41, 28, 49, 56, 38],
          backgroundColor: '#60775D'
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false
      }
    });

    // Graphique 2 - Crédits gagnés par jour
    new Chart(document.getElementById('creditsParJourChart'), {
      type: 'line',
      data: {
        labels: ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
        datasets: [{
          label: 'Crédits par jour',
          data: [12, 20, 18, 30, 22, 40, 35],
          backgroundColor: 'rgba(96,119,93,0.3)',
          borderColor: '#60775D',
          fill: true
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false
      }
    });

    // Graphique 3 - Répartition crédits collectés
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
