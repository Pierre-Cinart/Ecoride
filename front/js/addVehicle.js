// === Code exécuté une fois que le DOM est chargé ===
document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('form');

  form.addEventListener('submit', async (e) => {
    e.preventDefault(); // Empêche la soumission immédiate du formulaire

    let errors = "";

    // === Récupération des valeurs des champs (tous les ID sont en anglais) ===
    const brand = document.getElementById('brand').value.trim();
    const model = document.getElementById('model').value.trim();
    const registrationNumber = document.getElementById('registration_number').value.trim();
    const fuelType = document.getElementById('fuel_type').value;
    const seats = document.getElementById('seats').value;
    const firstDate = document.getElementById('first_registration_date').value;
    const today = new Date().toISOString().split('T')[0]; // Format YYYY-MM-DD

    const photo = document.getElementById('photo');
    const carteGrise = document.getElementById('registration_document');
    const assurance = document.getElementById('insurance_document');

    // === Extensions de fichiers autorisées ===
    const allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    // === Vérifications des champs texte / numériques ===
    if (brand === "") errors += "Veuillez indiquer une marque. ";
    if (model === "") errors += "Veuillez indiquer un modèle. ";
    if (fuelType === "") errors += "Veuillez choisir un type de carburant. ";
    if (seats === "" || parseInt(seats) < 1 || parseInt(seats) > 6) {
      errors += "Le nombre de places doit être entre 1 et 6. ";
    }
    if (!registrationNumber) {
      errors += "Veuillez renseigner le numéro d'immatriculation. ";
    }

    // === Vérification de la date (pas dans le futur) ===
    if (!firstDate || firstDate > today) {
      errors += "La date de mise en circulation ne peut pas être dans le futur. ";
    }

    // === PHOTO (facultative mais vérification si fournie) ===
    if (photo.files.length > 1) {
      errors += "Un seul fichier autorisé pour la photo du véhicule. ";
    } else if (photo.files.length === 1) {
      const ext = photo.files[0].name.split('.').pop().toLowerCase();
      if (!allowedExtensions.includes(ext)) {
        errors += "Format de la photo non autorisé. (jpg, jpeg, png, gif, webp uniquement) ";
      }
    }

    // === CARTE GRISE (obligatoire) ===
    if (!carteGrise.files.length) {
      errors += "Veuillez fournir la carte grise. ";
    } else if (carteGrise.files.length > 1) {
      errors += "Un seul fichier autorisé pour la carte grise. ";
    } else {
      const ext = carteGrise.files[0].name.split('.').pop().toLowerCase();
      if (!allowedExtensions.includes(ext)) {
        errors += "Format de la carte grise non autorisé. (jpg, jpeg, png, gif, webp uniquement) ";
      }
    }

    // === ASSURANCE (obligatoire) ===
    if (!assurance.files.length) {
      errors += "Veuillez fournir le document d’assurance. ";
    } else if (assurance.files.length > 1) {
      errors += "Un seul fichier autorisé pour l’assurance. ";
    } else {
      const ext = assurance.files[0].name.split('.').pop().toLowerCase();
      if (!allowedExtensions.includes(ext)) {
        errors += "Format du document d’assurance non autorisé. (jpg, jpeg, png, gif, webp uniquement) ";
      }
    }

    // === Envoie des erreurs vers checkerror.php si besoin ===
    if (errors !== "") {
      try {
        await fetch("../../back/ajax/checkerror.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          body: "error=" + encodeURIComponent(errors)
        });
        location.reload(); // Recharge pour déclencher l'affichage via pop-up
      } catch (e) {
        console.error("Erreur lors de l’envoi vers checkerror.php", e);
        location.reload(); // Fallback de sécurité
      }
      return;
    }

    // === Tout est valide → soumission normale du formulaire ===
    form.submit();
  });
});
