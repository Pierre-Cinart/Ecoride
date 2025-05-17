// === Fonction AJAX pour supprimer un véhicule ===
async function ajaxDeleteVehicle() {
  const vehicleSelect = document.getElementById("vehicle_id");

  if (!vehicleSelect || !vehicleSelect.value) {
    alert("Veuillez d'abord sélectionner un véhicule.");
    return;
  }

  // Récupération du nom du véhicule pour l'affichage du message
  const selectedOption = vehicleSelect.options[vehicleSelect.selectedIndex];
  const vehicleName = selectedOption.textContent.trim();

  const confirmation = confirm(
    `⚠ Vous êtes sur le point de supprimer le véhicule : "${vehicleName}".\n\n` +
    "Si vous avez des trajets prévus avec ce véhicule, ils seront tous annulés.\n" +
    "Vous risquez de recevoir des pénalités.\n\n" +
    "Êtes-vous sûr de vouloir continuer ?"
  );

  if (!confirmation) return;

  try {
    const response = await fetch("../../back/ajax/deleteVehicle.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded"
      },
      body: `vehicle_id=${encodeURIComponent(vehicleSelect.value)}`
    });

    const result = await response.text();

    if (result.trim() === "OK") {
      location.reload(); // recharge la page pour mettre à jour la liste des véhicules
    } else {
      alert("Une erreur est survenue : " + result);
    }
  } catch (err) {
    console.error("Erreur AJAX :", err);
    alert("Impossible de supprimer le véhicule.");
  }
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
  const editDocumentsBlock = document.getElementById("edit-documents-block");
  const vehicleIdInput = document.getElementById("vehicle_id_input");

  // === Affiche/Masque le formulaire "Devenir conducteur"
  function handleToggleDriverForm() {
    if (toggleDriverBtn && driverForm) {
      toggleDriverBtn.addEventListener("click", () => {
        driverForm.style.display = driverForm.style.display === "none" ? "block" : "none";
      });
    }
  }

  // === Gère l'affichage des boutons liés au véhicule sélectionné
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

  // === Gère l’apparition du bouton "Enregistrer" si changement dans les préférences
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

  // === Affiche dynamiquement le bloc de mise à jour des documents
  function handleEditDocumentsBlock() {
    if (updateDocumentsBtn && vehicleSelect && editDocumentsBlock && vehicleIdInput) {
      updateDocumentsBtn.addEventListener("click", () => {
        const selectedId = vehicleSelect.value;
        if (!selectedId) return;

        // Injecte l'ID du véhicule sélectionné dans le champ caché du formulaire
        vehicleIdInput.value = selectedId;

        // Affiche le bloc
        editDocumentsBlock.classList.remove("hidden");
        editDocumentsBlock.scrollIntoView({ behavior: "smooth" });
      });
    }
  }

  // === Gère l'affichage conditionnel d'une section personnalisée (utile si utilisée ailleurs)
  window.showVehiclePreferences = function (value) {
    if (vehiclePrefBlock) {
      vehiclePrefBlock.style.display = value ? "block" : "none";
    }
  };

  // === 🚀 INITIALISATION ===
  handleToggleDriverForm();
  handleVehicleButtonsVisibility();
  watchPreferencesChanges();
  handleEditDocumentsBlock(); // gestion dynamique des documents
});
