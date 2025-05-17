// ============================
// addTrip.js : JS pour addTrip.php
// ============================

// === Références aux éléments du DOM ===
const form = document.getElementById('offerTripForm');
const vehicleSelect = document.getElementById('vehicle');
const seatsInput = document.getElementById('places');
const seatsLabel = document.querySelector('label[for="places"]');
const durationBtn = document.getElementById('calculateDuration');
const durationDisplay = document.getElementById('estimated_duration_display');
const durationHidden = document.getElementById('estimated_duration');

// =============================
// 1. Affichage dynamique des places selon véhicule
// =============================
vehicleSelect.addEventListener('change', () => {
  const selectedId = vehicleSelect.value;
  if (selectedId && VEHICLE_DATA[selectedId]) {
    const maxSeats = VEHICLE_DATA[selectedId].seats;
    seatsInput.max = maxSeats;
    seatsInput.value = '';
    seatsInput.style.display = 'block';
    seatsLabel.style.display = 'block';
  } else {
    seatsInput.style.display = 'none';
    seatsLabel.style.display = 'none';
  }
});

// =============================
// 2. Fonction de calcul de durée avec ORS
// =============================
async function calculateORS() {
  const from = document.getElementById('departure_address').value.trim();
  const fromCity = document.getElementById('depart').value.trim();
  const to = document.getElementById('arrival_address').value.trim();
  const toCity = document.getElementById('arrivee').value.trim();

  if (!from || !fromCity || !to || !toCity) {
    await sendError("Veuillez remplir les adresses complètes.");
    return false;
  }

  try {
    const fromFull = `${from}, ${fromCity}`;
    const toFull = `${to}, ${toCity}`;

    const geoFrom = await fetch(`https://api-adresse.data.gouv.fr/search/?q=${encodeURIComponent(fromFull)}&limit=1`);
    const fromData = await geoFrom.json();
    if (!fromData.features.length) throw new Error("Adresse de départ introuvable");

    const geoTo = await fetch(`https://api-adresse.data.gouv.fr/search/?q=${encodeURIComponent(toFull)}&limit=1`);
    const toData = await geoTo.json();
    if (!toData.features.length) throw new Error("Adresse d’arrivée introuvable");

    const start = fromData.features[0].geometry.coordinates;
    const end = toData.features[0].geometry.coordinates;

    const orsResp = await fetch("https://api.openrouteservice.org/v2/directions/driving-car/geojson", {
      method: "POST",
      headers: {
        "Authorization": OPEN_ROUTE_KEY,
        "Content-Type": "application/json"
      },
      body: JSON.stringify({
        coordinates: [start, end]
      })
    });

    const orsData = await orsResp.json();
    if (!orsData.features?.[0]?.properties?.summary?.duration) {
      throw new Error("Durée introuvable dans la réponse ORS.");
    }

    const durationSec = Math.round(orsData.features[0].properties.summary.duration);
    const minutes = Math.floor(durationSec / 60) % 60;
    const hours = Math.floor(durationSec / 3600);
    const display = `${hours > 0 ? `${hours}h` : ''}${minutes > 0 ? `${minutes}min` : ''}`;

    durationHidden.value = durationSec;
    durationDisplay.value = display;
    return true;

  } catch (err) {
    console.error("Erreur ORS :", err);
    await sendError("Erreur dans les adresses fournies. Impossible de calculer la durée.");
    return false;
  }
}

// Clic manuel sur le bouton de calcul
durationBtn.addEventListener('click', async (e) => {
  e.preventDefault();
  await calculateORS();
});

// =============================
// 3. Validation complète du formulaire avant soumission
// =============================
form.addEventListener('submit', async (e) => {
  e.preventDefault();

  const from = document.getElementById('departure_address').value.trim();
  const fromCity = document.getElementById('depart').value.trim();
  const to = document.getElementById('arrival_address').value.trim();
  const toCity = document.getElementById('arrivee').value.trim();
  const vehicleId = vehicleSelect.value;
  const seats = parseInt(seatsInput.value, 10);
  const maxSeats = vehicleId && VEHICLE_DATA[vehicleId] ? VEHICLE_DATA[vehicleId].seats : null;
  const duration = durationHidden.value;
  const date = document.getElementById('date').value.trim();
  const time = document.getElementById('time').value.trim();
  const price = document.getElementById('price').value.trim();

  // === Vérifications ===
  if (!fromCity || !from) return await sendError("Adresse de départ invalide.");
  if (!toCity || !to) return await sendError("Adresse d’arrivée invalide.");
  if (!date || !time) return await sendError("Date ou heure manquante.");
  if (!vehicleId || !maxSeats) return await sendError("Véhicule non sélectionné.");
  if (isNaN(seats) || seats < 1) return await sendError("Places non valides.");
  if (seats >= maxSeats) return await sendError("Le nombre de places doit être inférieur au nombre total de sièges du véhicule.");
  if (!price || isNaN(price) || price < 0) return await sendError("Prix invalide.");

  // === Durée estimée : si vide → tentative de calcul
  if (!duration) {
    const success = await calculateORS();
    if (!success) return; // Erreur ORS déjà envoyée
  }

  // === Tout est bon
  form.submit();
});

// =============================
// 4. Envoi des erreurs vers PHP (checkerror.php) via fetch
// =============================
async function sendError(message) {
  await fetch("../../back/ajax/checkerror.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: "error=" + encodeURIComponent(message)
  });
  location.reload();
}
