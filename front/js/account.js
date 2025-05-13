document.addEventListener('DOMContentLoaded', () => {
  // === 1. Bouton pour afficher le formulaire de demande de conducteur ===
  const toggleDriverBtn = document.getElementById("toggleDriverForm");
  const driverForm = document.getElementById("driverForm");

  if (toggleDriverBtn && driverForm) {
    toggleDriverBtn.addEventListener("click", () => {
      driverForm.style.display = driverForm.style.display === "none" ? "block" : "none";
    });
  }

  // === 2. Affichage conditionnel d’un bloc selon un véhicule sélectionné  ===
  window.showVehiclePreferences = function (value) {
    const block = document.getElementById("vehicle-preferences");
    if (block) {
      block.style.display = value ? "block" : "none";
    }
  };

  // === 3. Affichage dynamique du bouton "Supprimer le véhicule" ===
  const vehicleSelect = document.getElementById("vehicle_id");
  const deleteBtn = document.getElementById("btnDeleteVehicle");
  const UpdateDocuments = document.getElementById("btnUpdateDocuments");
  

  if (vehicleSelect && deleteBtn) {
    const toggleDeleteButton = () => {
      if (vehicleSelect.value === "") {
        deleteBtn.classList.add("hidden");
        UpdateDocuments.classList.add("hidden");
      } else {
        deleteBtn.classList.remove("hidden");
        UpdateDocuments.classList.remove("hidden");
      }
    };

    // Initialisation et écouteur
    toggleDeleteButton();
    vehicleSelect.addEventListener("change", toggleDeleteButton);
  }

  // === 4. Affichage du bouton "💾 Enregistrer les préférences" si changement détecté ===
  const smokerInput = document.querySelector('input[name="smoker"]');
  const petsInput = document.querySelector('input[name="pets"]');
  const noteInput = document.getElementById("note_personnelle");
  const savePrefsBtn = document.getElementById("btnSavePrefs");

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

      if (hasChanged) {
        savePrefsBtn.classList.remove("hidden");
        savePrefsBtn.classList.add("fade-in");
      } else {
        savePrefsBtn.classList.add("hidden");
        savePrefsBtn.classList.remove("fade-in");
      }
    };

    // Attache les écouteurs
    smokerInput.addEventListener("change", checkForChanges);
    petsInput.addEventListener("change", checkForChanges);
    noteInput.addEventListener("input", checkForChanges);

    // Démarre masqué
    savePrefsBtn.classList.add("hidden");
  }
});
