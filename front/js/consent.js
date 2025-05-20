// === consent.js ===
// Gère le consentement utilisateur pour Google Analytics

// Utilitaire pour créer un cookie avec une durée personnalisée
function setCookie(name, value, hours) {
  const date = new Date();
  date.setTime(date.getTime() + (hours * 60 * 60 * 1000));
  document.cookie = `${name}=${value}; expires=${date.toUTCString()}; path=/; SameSite=Lax`;
}

// Fonction pour charger dynamiquement le script Google Analytics
function loadGoogleAnalytics() {
  const script = document.createElement('script');
  script.type = 'module';
  script.src = '../js/googleAnalytics.js'; // Ton script déjà préparé
  document.body.appendChild(script);
}

// Quand le DOM est prêt, on initialise les écouteurs
document.addEventListener('DOMContentLoaded', function () {
  const acceptBtn = document.getElementById('accept-cookies');
  const refuseBtn = document.getElementById('refuse-cookies');
  const banner = document.getElementById('cookie-consent-banner');

  // Si la bannière ou les boutons ne sont pas présents, on arrête
  if (!banner || (!acceptBtn && !refuseBtn)) return;

  // Bouton ACCEPTER
  acceptBtn.addEventListener('click', function () {
    setCookie('EcoRideGAConsent', 'true', 24 * 30 * 6); // 6 mois
    banner.remove();
    loadGoogleAnalytics(); // On charge GA maintenant que l'utilisateur a accepté
  });

  // Bouton REFUSER
  refuseBtn.addEventListener('click', function () {
    setCookie('EcoRideGAConsent', 'false', 2); // 2 heures seulement
    banner.remove();
    // Rien d'autre à faire, Google Analytics ne sera jamais appelé
  });

  // Bonus : si le cookie existe déjà à "true", on charge automatiquement Analytics
  const cookies = document.cookie.split(';').map(c => c.trim());
  const consentCookie = cookies.find(c => c.startsWith('EcoRideGAConsent='));
  if (consentCookie && consentCookie.split('=')[1] === 'true') {
    loadGoogleAnalytics();
  }
});
