// === Fonction globale pour supprimer un véhicule via AJAX ===
function ajaxDeleteVehicle() {
  const vehicleSelect = document.getElementById("vehicle_id");
  const vehicleId = vehicleSelect ? vehicleSelect.value : "";

  // Vérifie qu'un véhicule est bien sélectionné
  if (!vehicleId) {
    alert("Veuillez d'abord sélectionner un véhicule.");
    return;
  }

  // Confirmation utilisateur
  const confirmDelete = confirm("Êtes-vous sûr de vouloir retirer ce véhicule de la liste ?");
  if (!confirmDelete) return;

  // Requête AJAX vers le back
  fetch("../../back/ajax/AJAXdeleteVehicle.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded"
    },
    body: `vehicle_id=${encodeURIComponent(vehicleId)}`
  })
    .then(response => {
      if (!response.ok) throw new Error("Erreur lors de la suppression du véhicule.");
      return response.text();
    })
    .then(result => {
      if (result.trim() === "OK") {
        // Rafraîchit la page après suppression
        location.reload();
      } else {
        alert("Une erreur est survenue : " + result);
      }
    })
    .catch(error => {
      console.error("Erreur AJAX :", error);
      alert("Impossible de supprimer le véhicule.");
    });
}

// === Quand le DOM est prêt ===
document.addEventListener('DOMContentLoaded', () => {
  // === RÉFÉRENCES AUX ÉLÉMENTS DU DOM ===
  const toggleDriverBtn = document.getElementById("toggleDriverForm");
  const driverForm = document.getElementById("driverForm");
  const smokerInput = document.querySelector('input[name="smoker"]');
  const petsInput = document.querySelector('input[name="pets"]');
  const noteInput = document.getElementById("note_personnelle");
  const savePrefsBtn = document.getElementById("btnSavePrefs");
  const vehicleSelect = document.getElementById("vehicle_id");
  const deleteBtn = document.getElementById("btnDeleteVehicle");
  const updateDocumentsBtn = document.getElementById("btnUpdateDocuments");
  const vehiclePrefBlock = document.getElementById("vehicle-preferences");

  // === 🔧 FONCTIONS ===

  // Affiche/Masque le formulaire "Devenir conducteur"
  function handleToggleDriverForm() {
    if (toggleDriverBtn && driverForm) {
      toggleDriverBtn.addEventListener("click", () => {
        driverForm.style.display = driverForm.style.display === "none" ? "block" : "none";
      });
    }
  }

  // Gère l'affichage des boutons liés au véhicule sélectionné
  function handleVehicleButtonsVisibility() {
    if (vehicleSelect && deleteBtn && updateDocumentsBtn) {
      const toggleButtons = () => {
        const hasSelection = vehicleSelect.value !== "";
        deleteBtn.classList.toggle("hidden", !hasSelection);
        updateDocumentsBtn.classList.toggle("hidden", !hasSelection);
      };

      toggleButtons(); // au chargement
      vehicleSelect.addEventListener("change", toggleButtons);
    }
  }

  // Gère l’apparition du bouton "Enregistrer" si changement dans les préférences
  function watchPreferencesChanges() {
    if (smokerInput && petsInput && noteInput && savePrefsBtn) {
      const initialState = {
        smoker: smokerInput.checked,
        pets: petsInput.checked,
        note: noteInput.value.trim()
      };

      const checkForChanges = () => {
        const currentState = {
          smoker: smokerInput.checked,
          pets: petsInput.checked,
          note: noteInput.value.trim()
        };

        const hasChanged =
          currentState.smoker !== initialState.smoker ||
          currentState.pets !== initialState.pets ||
          currentState.note !== initialState.note;

        savePrefsBtn.classList.toggle("hidden", !hasChanged);
        savePrefsBtn.classList.toggle("fade-in", hasChanged);
      };

      smokerInput.addEventListener("change", checkForChanges);
      petsInput.addEventListener("change", checkForChanges);
      noteInput.addEventListener("input", checkForChanges);

      savePrefsBtn.classList.add("hidden");
    }
  }

  // Gère l'affichage conditionnel d'une section personnalisée
  window.showVehiclePreferences = function (value) {
    if (vehiclePrefBlock) {
      vehiclePrefBlock.style.display = value ? "block" : "none";
    }
  };

  // === 🚀 INITIALISATION ===
  handleToggleDriverForm();
  handleVehicleButtonsVisibility();
  watchPreferencesChanges();
});
