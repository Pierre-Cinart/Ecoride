// charts.js — Script de gestion des graphiques administrateur (visites + trajets)

document.addEventListener("DOMContentLoaded", () => {

  // === Initialisation des éléments HTML ===
  const visitesCanvas = document.getElementById("visitesChart");
  const trajetsCanvas = document.getElementById("trajetsChart");
  const weekDateInput = document.getElementById("week-date");

  // Variables globales pour stocker les instances de Chart.js
  let visitesChartInstance = null;
  let trajetsChartInstance = null;

  // === Initialisation de la date du jour ===
  const today = new Date();
  const isoToday = today.toISOString().split('T')[0]; // format "YYYY-MM-DD"
  weekDateInput.value = isoToday;
  weekDateInput.max = isoToday; // Empêche la sélection de dates futures

  // === Fonction utilitaire : retourner les 7 dates de la semaine depuis une date donnée ===
  function getWeekRange(selectedDateStr) {
    const selectedDate = new Date(selectedDateStr);
    const dayOfWeek = selectedDate.getDay(); // 0 = dimanche, 1 = lundi, etc.

    // Calcul du lundi de la semaine sélectionnée
    const monday = new Date(selectedDate);
    monday.setDate(selectedDate.getDate() - (dayOfWeek === 0 ? 6 : dayOfWeek - 1));

    // Génération des 7 jours de la semaine
    const weekDates = [];
    for (let i = 0; i < 7; i++) {
      const d = new Date(monday);
      d.setDate(monday.getDate() + i);

      // Si la date dépasse aujourd’hui, on marque null (jour futur)
      if (d > today) {
        weekDates.push(null);
      } else {
        weekDates.push(d.toISOString().split('T')[0]); // format "YYYY-MM-DD"
      }
    }

    return weekDates;
  }

  // === Chargement du graphique des visiteurs (Google Analytics) ===
  function loadVisitesFromDate(selectedDateStr) {
    const weekDates = getWeekRange(selectedDateStr);

    fetch(`../../back/api/apiChartsAnalytics.php?type=visitors&dates=${encodeURIComponent(JSON.stringify(weekDates))}`)
      .then(res => res.json())
      .then(({ labels, data }) => {
        if (!visitesCanvas) return;

        // Supprimer l'ancien graphique s’il existe
        if (visitesChartInstance) visitesChartInstance.destroy();

        visitesChartInstance = new Chart(visitesCanvas, {
          type: 'bar',
          data: {
            labels: labels, // libellés formatés côté serveur (ex: "Lun. 20 mai")
            datasets: [{
              label: 'Visiteurs uniques par jour',
              data: data,
              backgroundColor: '#60775D'
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
              y: {
                beginAtZero: true,
                ticks: {
                  // Affiche les grands nombres correctement (ex : 1 000 au lieu de 1000)
                  callback: value => value.toLocaleString('fr-FR')
                }
              }
            }
          }
        });
      })
      .catch(err => {
        console.error("Erreur lors du chargement des visiteurs :", err);
      });
  }

  // === Chargement du graphique des trajets (depuis base SQL) ===
  function loadTripsFromDate(selectedDateStr) {
    const weekDates = getWeekRange(selectedDateStr);

    fetch(`../../back/api/apiChartsTrips.php?dates=${encodeURIComponent(JSON.stringify(weekDates))}`)
      .then(res => res.json())
      .then(({ labels, data }) => {
        if (!trajetsCanvas) return;

        // Supprimer l'ancien graphique s’il existe
        if (trajetsChartInstance) trajetsChartInstance.destroy();

        trajetsChartInstance = new Chart(trajetsCanvas, {
          type: 'bar',
          data: {
            labels: labels,
            datasets: [{
              label: 'Trajets publiés par jour',
              data: data,
              backgroundColor: '#60775D'
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
              y: {
                beginAtZero: true,
                ticks: {
                  callback: value => value.toLocaleString('fr-FR')
                }
              }
            }
          }
        });
      })
      .catch(err => {
        console.error("Erreur lors du chargement des trajets :", err);
      });
  }
  
  // === Réaction au changement de date sélectionnée ===
  weekDateInput.addEventListener("change", () => {
    const selected = weekDateInput.value;
    loadVisitesFromDate(selected);
    loadTripsFromDate(selected);
  });

  // === Chargement initial au démarrage de la page ===
  loadVisitesFromDate(isoToday);
  loadTripsFromDate(isoToday);
});
