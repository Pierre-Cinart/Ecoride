// ==============================================
// map.js : Carte Leaflet + géocodage dynamique
// ==============================================

// 1. Initialisation de la carte centrée sur Paris
const map = L.map('map').setView([48.8566, 2.3522], 12);

// 2. Tuiles OpenStreetMap France
L.tileLayer('https://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png', {
  attribution: '&copy; OpenStreetMap',
  minZoom: 1,
  maxZoom: 20
}).addTo(map);

// 3. Icônes personnalisées
const greenIcon = L.icon({
  iconUrl: '../img/ico/start.png',
  iconSize: [32, 32],
});

const redIcon = L.icon({
  iconUrl: '../img/ico/arrived.png',
  iconSize: [32, 32],
});

// 4. État actuel
let currentAction = null;
let startMarker = null;
let endMarker = null;

// 5. Gestion des boutons pour activer le clic sur la carte
document.getElementById("setStart").addEventListener("click", () => currentAction = "start");
document.getElementById("setEnd").addEventListener("click", () => currentAction = "end");

// 6. Clic sur la carte → récupération adresse + positionnement marqueur
map.on("click", async function (e) {
  if (!currentAction) return;

  const coords = [e.latlng.lat, e.latlng.lng];

  if (currentAction === "start") {
    if (startMarker) map.removeLayer(startMarker);
    startMarker = L.marker(coords, { icon: greenIcon }).addTo(map).bindPopup("Départ").openPopup();
    await updateInputsFromCoords(coords, "depart");
  }

  if (currentAction === "end") {
    if (endMarker) map.removeLayer(endMarker);
    endMarker = L.marker(coords, { icon: redIcon }).addTo(map).bindPopup("Arrivée").openPopup();
    await updateInputsFromCoords(coords, "arrivee");
  }
});

// 7. Mise à jour des inputs à partir de coordonnées (reverse geocoding)
async function updateInputsFromCoords(coords, inputPrefix) {
  try {
    const response = await fetch(`https://api-adresse.data.gouv.fr/reverse/?lon=${coords[1]}&lat=${coords[0]}`);
    const data = await response.json();

    if (data.features && data.features.length > 0) {
      const feature = data.features[0].properties;

      const city = feature.city || feature.context?.split(',').find(e => e.match(/\\d{5}/)) || "Ville inconnue";
      const address = feature.name && feature.street
        ? `${feature.name} ${feature.street}`
        : feature.label || "Adresse inconnue";

      // Injection dans les champs
      document.getElementById(inputPrefix).value = city;
      const addressInputId = inputPrefix === "depart" ? "departure_address" : "arrival_address";
      document.getElementById(addressInputId).value = address;
    } else {
      showAddressError(inputPrefix);
    }
  } catch (error) {
    console.error("Erreur reverse geocoding :", error);
    showAddressError(inputPrefix);
  }
}

// 8. Affichage d'erreur si adresse non trouvée
function showAddressError(inputPrefix) {
  const addressInputId = inputPrefix === "depart" ? "departure_address" : "arrival_address";
  document.getElementById(addressInputId).value = "Adresse inconnue";
  alert("Aucune adresse précise n’a pu être trouvée ici. Veuillez saisir manuellement.");
}

// 9. Lorsqu’on modifie une VILLE (champ texte) → marqueur approximatif
document.getElementById("depart").addEventListener("change", function () {
  handleTextToMarker(this.value, "start");
});

document.getElementById("arrivee").addEventListener("change", function () {
  handleTextToMarker(this.value, "end");
});

// 10. Lorsqu’on modifie une ADRESSE précise → mise à jour du marqueur
document.getElementById("departure_address").addEventListener("change", function () {
  const city = document.getElementById("depart").value;
  const fullAddress = `${this.value}, ${city}`;
  handleTextToMarker(fullAddress, "start");
});

document.getElementById("arrival_address").addEventListener("change", function () {
  const city = document.getElementById("arrivee").value;
  const fullAddress = `${this.value}, ${city}`;
  handleTextToMarker(fullAddress, "end");
});

// 11. Géocodage : adresse → coordonnées GPS → pose d’un marqueur
async function handleTextToMarker(addressText, type) {
  try {
    const response = await fetch(`https://api-adresse.data.gouv.fr/search/?q=${encodeURIComponent(addressText)}&limit=1`);
    const data = await response.json();

    if (data.features && data.features.length > 0) {
      const coords = data.features[0].geometry.coordinates;
      const latlng = [coords[1], coords[0]];

      if (type === "start") {
        if (startMarker) map.removeLayer(startMarker);
        startMarker = L.marker(latlng, { icon: greenIcon }).addTo(map).bindPopup("Départ").openPopup();
        map.setView(latlng, 12);
      }

      if (type === "end") {
        if (endMarker) map.removeLayer(endMarker);
        endMarker = L.marker(latlng, { icon: redIcon }).addTo(map).bindPopup("Arrivée").openPopup();
        map.setView(latlng, 12);
      }
    } else {
      alert(`Adresse non trouvée : \"${addressText}\". Merci de vérifier la formulation.`);
    }
  } catch (error) {
    console.error("Erreur lors du géocodage adresse :", error);
    alert("Erreur réseau : impossible de localiser cette adresse.");
  }
}
