// Sélection des champs
const form = document.querySelector('form');
const popup = document.getElementById('popup');
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


// event listener sur la checkbox  pour afficher ou masquer l upload de permis en cas d inscription en tant que chauffeur
checkbox.addEventListener('change', () => {
  if (checkbox.checked) {
    permitField.classList.remove('hidden');
    permitInput.required = true;
  } else {
    permitField.classList.add('hidden');
    permitInput.required = false;
  }
});

// Fonction validation email
function validateEmail(emailValue) {
  const regex = /^[a-zA-Z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/i;
  return regex.test(emailValue);
}

// Fonction validation téléphone français
function validatePhone(phoneValue) {
  const regex = /^(\+33|0)[1-9](\d{2}){4}$/;
  return regex.test(phoneValue);
}

// Fonction validation mot de passe sécurisé
function validatePassword(passwordValue) {
  const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/;
  return regex.test(passwordValue);
}

// Fonction validation prénom/nom
function validateName(nameValue) {
  const regex = /^[a-zA-ZÀ-ÿ\s'-]+$/;
  return regex.test(nameValue);
}

// Fonction validation pseudo
function validatePseudo(pseudoValue) {
  const regex = /^[a-zA-Z0-9_-]{3,}$/; // 3 caractères minimum, lettres, chiffres, tirets
  return regex.test(pseudoValue);
}

// Fonction validation du fichier JPEG
function validatePermitFile(file) {
  if (!file) return false;
  const fileType = file.type;
  return (fileType === "image/jpeg" || fileType === "image/jpg");
}

// Fonction d'affichage d'erreur propre via POPUP
function showError(message) {
  if (popup) {
    popup.className = 'error';  // remet la classe à error
    popup.textContent = message;
    popup.style.display = 'block';
    popup.style.opacity = '1';

    setTimeout(() => {
      popup.style.opacity = '0';
      setTimeout(() => {
        popup.style.display = 'none';
      }, 500);
    }, 4000);
  } else {
    alert(message); // sécurité au cas où pas de div popup
  }
}

// Soumission du formulaire
form.addEventListener('submit', function(e) {
  let errors = [];

  // Validation pseudo
  if (!validatePseudo(pseudo.value)) {
    errors.push('Le pseudo doit faire au moins 3 caractères et ne pas contenir d\'espaces.');
  }

  // Validation prénom
  if (!validateName(firstName.value)) {
    errors.push('Le prénom ne doit contenir que des lettres, espaces ou tirets.');
  }

  // Validation nom
  if (!validateName(name.value)) {
    errors.push('Le nom ne doit contenir que des lettres, espaces ou tirets.');
  }

  // Validation email
  if (!validateEmail(email.value)) {
    errors.push('Email invalide.');
  }
  if (email.value !== confirmEmail.value) {
    errors.push('Les emails ne correspondent pas.');
  }

  // Validation téléphone
  if (!validatePhone(phone.value)) {
    errors.push('Numéro de téléphone invalide.');
  }

  // Validation mot de passe
  if (!validatePassword(password.value)) {
    errors.push('Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule et un chiffre.');
  }
  if (password.value !== confirmPassword.value) {
    errors.push('Les mots de passe ne correspondent pas.');
  }

  // Validation permis si inscription chauffeur
  if (isDriver.checked) {
    if (!permitInput.files.length) {
      errors.push('Le fichier du permis est obligatoire pour devenir conducteur.');
    } else if (!validatePermitFile(permitInput.files[0])) {
      errors.push('Le permis doit être au format JPEG.');
    }
  }

  // Si erreurs, empêcher la soumission
  if (errors.length > 0) {
    e.preventDefault();
    showError(errors.join('\n'));
  }
});
