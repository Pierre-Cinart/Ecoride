document.addEventListener('DOMContentLoaded', () => {
  // === 🌐 RÉFÉRENCES AUX ÉLÉMENTS DU DOM ===

  // Formulaire pour devenir conducteur
  const toggleDriverBtn = document.getElementById("toggleDriverForm");
  const driverForm = document.getElementById("driverForm");

  // Préférences conducteur
  const smokerInput = document.querySelector('input[name="smoker"]');
  const petsInput = document.querySelector('input[name="pets"]');
  const noteInput = document.getElementById("note_personnelle");
  const savePrefsBtn = document.getElementById("btnSavePrefs");

  // Sélecteur de véhicule
  const vehicleSelect = document.getElementById("vehicle_id");
  const deleteBtn = document.getElementById("btnDeleteVehicle");
  const updateDocumentsBtn = document.getElementById("btnUpdateDocuments");

  // Bloc des préférences véhicule (optionnel)
  const vehiclePrefBlock = document.getElementById("vehicle-preferences");

  // === 🔧 FONCTIONS UTILITAIRES ===

  // Affiche/Masque le formulaire pour devenir conducteur
  function handleToggleDriverForm() {
    if (toggleDriverBtn && driverForm) {
      toggleDriverBtn.addEventListener("click", () => {
        driverForm.style.display = driverForm.style.display === "none" ? "block" : "none";
      });
    }
  }

  // Affiche/Masque dynamiquement les boutons liés au véhicule
  function handleVehicleButtonsVisibility() {
    if (vehicleSelect && deleteBtn && updateDocumentsBtn) {
      const toggleButtons = () => {
        const hasSelection = vehicleSelect.value !== "";
        deleteBtn.classList.toggle("hidden", !hasSelection);
        updateDocumentsBtn.classList.toggle("hidden", !hasSelection);
      };

      toggleButtons(); // initial
      vehicleSelect.addEventListener("change", toggleButtons);
    }
  }

  // Confirme avant suppression d’un véhicule
  function attachDeleteConfirmation() {
    if (deleteBtn) {
      deleteBtn.addEventListener("click", (e) => {
        const confirmDelete = confirm("Êtes-vous sûr de vouloir retirer ce véhicule de la liste ?");
        if (!confirmDelete) e.preventDefault();
      });
    }
  }

  // Affiche ou masque dynamiquement le bouton d’enregistrement des préférences
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

      savePrefsBtn.classList.add("hidden"); // au chargement
    }
  }

  // Permet d'afficher dynamiquement un bloc lié au véhicule sélectionné
  window.showVehiclePreferences = function (value) {
    if (vehiclePrefBlock) {
      vehiclePrefBlock.style.display = value ? "block" : "none";
    }
  };

  // === 🚀 INITIALISATION ===
  handleToggleDriverForm();
  handleVehicleButtonsVisibility();
  attachDeleteConfirmation();
  watchPreferencesChanges();
});
