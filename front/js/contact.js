// JS de validation du formulaire de contact (comme pour register.js)
document.addEventListener('DOMContentLoaded', function () {
  const form = document.querySelector('form');
  const nom = document.getElementById('nom');
  const prenom = document.getElementById('prenom');
  const email = document.getElementById('email');
  const objet = document.getElementById('objet');
  const message = document.getElementById('message');
  const popup = document.getElementById('popup');

  function validateName(value) {
    const regex = /^[a-zA-ZÀ-ÿ\s'-]{2,}$/;
    return regex.test(value);
  }

  function validateEmail(value) {
    const regex = /^[a-zA-Z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/i;
    return regex.test(value);
  }

  function validateObjet(value) {
    return value.trim().length >= 5;
  }

  function validateMessage(value) {
    return value.trim().length >= 10;
  }

  form.addEventListener('submit', function (e) {
    let errors = [];

    if (!validateName(nom.value)) {
      errors.push("Le nom est invalide.");
    }

    if (!validateName(prenom.value)) {
      errors.push("Le prénom est invalide.");
    }

    if (!validateEmail(email.value)) {
      errors.push("Adresse email invalide.");
    }

    if (!validateObjet(objet.value)) {
      errors.push("L'objet doit contenir au moins 5 caractères.");
    }

    if (!validateMessage(message.value)) {
      errors.push("Le message doit contenir au moins 10 caractères.");
    }

    if (errors.length > 0) {
      e.preventDefault();
      if (popup) {
        popup.className = 'error';
        popup.textContent = errors.join('\n');
        popup.style.display = 'block';
        popup.style.opacity = '1';
      }
    }
  });
});
