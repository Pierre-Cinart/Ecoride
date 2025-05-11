// ===============================================
// map.js : gestion de la carte interactive Leaflet
// ===============================================

// Initialisation de la carte centrée sur Paris
const map = L.map('map').setView([48.8566, 2.3522], 12);

// Chargement des tuiles OpenStreetMap (version française)
L.tileLayer('https://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png', {
  attribution: '&copy; OpenStreetMap',
  minZoom: 1,
  maxZoom: 20
}).addTo(map);

// =======================
// Icônes personnalisées
// =======================
const greenIcon = L.icon({
  iconUrl: '../img/ico/start.png', // Icône départ (drapeau vert)
  iconSize: [32, 32],
});

const redIcon = L.icon({
  iconUrl: '../img/ico/arrived.png', // Icône arrivée (drapeau rouge)
  iconSize: [32, 32],
});

// ===========================
// Variables de contrôle état
// ===========================
let currentAction = null; // "start" ou "end" (selon le bouton cliqué)
let startMarker = null;   // Marqueur de départ
let endMarker = null;     // Marqueur d'arrivée

// ==========================
// Gestion des boutons cliqués
// ==========================
document.getElementById("setStart").addEventListener("click", () => {
  currentAction = "start";
});

document.getElementById("setEnd").addEventListener("click", () => {
  currentAction = "end";
});

// ==========================================
// Gestion du clic sur la carte pour ajouter un marqueur
// ==========================================
map.on("click", async function (e) {
  if (!currentAction) return; // Aucun bouton n’a été sélectionné

  const latlng = e.latlng;
  const coords = [latlng.lat, latlng.lng];

  // Supprime le marqueur existant si on reclique
  if (currentAction === "start") {
    if (startMarker) map.removeLayer(startMarker);
    startMarker = L.marker(coords, { icon: greenIcon }).addTo(map).bindPopup("Point de départ").openPopup();
    await updateInputWithCity(coords, "depart");
  }

  if (currentAction === "end") {
    if (endMarker) map.removeLayer(endMarker);
    endMarker = L.marker(coords, { icon: redIcon }).addTo(map).bindPopup("Point d'arrivée").openPopup();
    await updateInputWithCity(coords, "arrivee");
  }
});

// =========================================================
// Fonction pour convertir les coordonnées -> nom de ville
// Utilise l’API GeoAPI (API.gouv)
// =========================================================
async function updateInputWithCity(coords, inputId) {
  try {
    const response = await fetch(`https://api-adresse.data.gouv.fr/reverse/?lon=${coords[1]}&lat=${coords[0]}`);
    const data = await response.json();

    if (data.features && data.features.length > 0) {
      const city = data.features[0].properties.city;
      document.getElementById(inputId).value = city;
    }
  } catch (error) {
    console.error("Erreur lors de la récupération de la ville :", error);
  }
}

// ============================================================
// Quand on modifie l’input de ville => mise à jour de la carte
// ============================================================
document.getElementById("depart").addEventListener("change", function () {
  handleCityToMarker(this.value, "start");
});

document.getElementById("arrivee").addEventListener("change", function () {
  handleCityToMarker(this.value, "end");
});

// ===========================================
// Utilise GeoAPI pour récupérer les coordonnées d’une ville
// Puis place un marqueur sur la carte
// ===========================================
async function handleCityToMarker(cityName, type) {
  try {
    const response = await fetch(`https://api-adresse.data.gouv.fr/search/?q=${encodeURIComponent(cityName)}&limit=1`);
    const data = await response.json();

    if (data.features && data.features.length > 0) {
      const coords = data.features[0].geometry.coordinates;
      const latlng = [coords[1], coords[0]];

      if (type === "start") {
        if (startMarker) map.removeLayer(startMarker);
        startMarker = L.marker(latlng, { icon: greenIcon }).addTo(map).bindPopup("Point de départ").openPopup();
        map.setView(latlng, 12);
      }
      if (type === "end") {
        if (endMarker) map.removeLayer(endMarker);
        endMarker = L.marker(latlng, { icon: redIcon }).addTo(map).bindPopup("Point d'arrivée").openPopup();
        map.setView(latlng, 12);
      }
    }
  } catch (error) {
    console.error("Erreur lors de la recherche de ville :", error);
  }
}
