// Récupération du formulaire et de tous les champs
const form = document.querySelector('form');
const pseudo = document.getElementById('pseudo');
const firstName = document.getElementById('first-name');
const name = document.getElementById('name');
const email = document.getElementById('email');
const confirmEmail = document.getElementById('confirm-email');
const phone = document.getElementById('phone');
const password = document.getElementById('password');
const confirmPassword = document.getElementById('confirm-password');
const isDriver = document.getElementById('is-driver');
const permitInput = document.getElementById('permit');
const checkbox = document.getElementById('is-driver');
const permitField = document.getElementById('driver-permit');
const errorInput = document.getElementById('errorMessage'); // champ caché pour l'erreur

// Affiche/masque le champ "permis" si l'utilisateur coche la case conducteur
checkbox.addEventListener('change', () => {
  if (checkbox.checked) {
    permitField.classList.remove('hidden');
    permitInput.required = true;
  } else {
    permitField.classList.add('hidden');
    permitInput.required = false;
  }
});

// Fonction de validation : Email
function validateEmail(emailValue) {
  const regex = /^[a-zA-Z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/i;
  return regex.test(emailValue);
}

// Fonction de validation : Téléphone FR
function validatePhone(phoneValue) {
  const regex = /^(\+33|0)[1-9](\d{2}){4}$/;
  return regex.test(phoneValue);
}

// Fonction de validation : Mot de passe fort
function validatePassword(passwordValue) {
  const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/;
  return regex.test(passwordValue);
}

// Fonction de validation : Prénom/Nom (lettres, tirets, espaces)
function validateName(nameValue) {
  const regex = /^[a-zA-ZÀ-ÿ\s'-]+$/;
  return regex.test(nameValue);
}

// Fonction de validation : Pseudo (3+ caractères, lettres, chiffres, tirets, underscores)
function validatePseudo(pseudoValue) {
  const regex = /^[a-zA-Z0-9_-]{3,}$/;
  return regex.test(pseudoValue);
}

// Fonction de validation : Fichier image (formats autorisés)
function validatePermitFile(file) {
  if (!file) return false;
  const fileType = file.type;
  return (
    fileType === "image/jpeg" ||
    fileType === "image/jpg" ||
    fileType === "image/png" ||
    fileType === "image/webp"
  );
}

// Soumission du formulaire
form.addEventListener('submit', function (e) {
  e.preventDefault(); // on empêche le comportement natif
  let errors = [];

  // Vérifications des champs
  if (!validatePseudo(pseudo.value)) {
    errors.push('Le pseudo doit faire au moins 3 caractères et ne pas contenir d\'espaces.');
  }

  if (!validateName(firstName.value)) {
    errors.push('Le prénom ne doit contenir que des lettres, espaces ou tirets.');
  }

  if (!validateName(name.value)) {
    errors.push('Le nom ne doit contenir que des lettres, espaces ou tirets.');
  }

  if (!validateEmail(email.value)) {
    errors.push('Email invalide.');
  }

  if (email.value !== confirmEmail.value) {
    errors.push('Les emails ne correspondent pas.');
  }

  if (!validatePhone(phone.value)) {
    errors.push('Numéro de téléphone invalide.');
  }

  if (!validatePassword(password.value)) {
    errors.push('Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule et un chiffre.');
  }

  if (password.value !== confirmPassword.value) {
    errors.push('Les mots de passe ne correspondent pas.');
  }

  if (isDriver.checked) {
    if (!permitInput.files.length) {
      errors.push('Le fichier du permis est obligatoire pour devenir conducteur.');
    } else if (!validatePermitFile(permitInput.files[0])) {
      errors.push('Le permis doit être au format JPEG, PNG ou WebP.');
    }
  }

  // Si erreurs trouvées
  if (errors.length > 0) {
    const errorMessage = errors.join('\n');
    errorInput.value = errorMessage; // on met dans le champ caché

    // Envoi AJAX vers back/ajax/checkerror.php
    fetch('../../back/ajax/checkerror.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: 'error=' + encodeURIComponent(errorMessage),
    }).then(() => {
      window.location.reload(); // on recharge pour que PHP affiche l'erreur
    });

    return; // on bloque le submit
  }

  // Sinon, aucun souci → on soumet
  form.submit();
});
