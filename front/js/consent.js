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
  script.src = '../js/googleAnalytics.js';
  document.body.appendChild(script);
}

// Quand le DOM est prêt, on initialise les écouteurs
document.addEventListener('DOMContentLoaded', function () {
  const acceptBtn = document.getElementById('accept-cookies');
  const refuseBtn = document.getElementById('refuse-cookies');
  const banner = document.getElementById('cookie-consent-banner');

  // Si cookie déjà accepté, on charge GA sans poser de question
  const cookies = document.cookie.split(';').map(cookie => cookie.trim());
  const consentCookie = cookies.find(cookie => cookie.startsWith('EcoRideGAConsent='));
  const hasConsented = consentCookie && consentCookie.split('=')[1] === 'true';

  if (hasConsented) {
    loadGoogleAnalytics();
    if (banner) banner.remove(); // On supprime la bannière même si elle est là par erreur
    return;
  }

  // Sinon, on attend le clic utilisateur
  if (!banner || (!acceptBtn && !refuseBtn)) return;

    acceptBtn.addEventListener('click', function () {
    setCookie('EcoRideGAConsent', 'true', 24 * 30 * 6); // 6 mois
    banner.remove();
    loadGoogleAnalytics();
  });

  refuseBtn.addEventListener('click', function () {
    setCookie('EcoRideGAConsent', 'false', 2); // 2 heures seulement
    banner.remove();
  });
});
