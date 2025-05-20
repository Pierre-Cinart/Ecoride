<?php
// Affiche la bannière uniquement si le cookie n'existe pas
if (!isset($_COOKIE['EcoRideGAConsent'])):
?>
  <div id="cookie-consent-banner" style=" background: #2c3e50; color: white; padding: 15px; text-align: center; z-index: 9999;">
    Ce site utilise Google Analytics pour analyser la fréquentation et améliorer votre expérience utilisateur.
    Les données collectées sont anonymes (pages visitées, durée de session, type de navigateur...) et servent uniquement à des fins statistiques.
    <br><br>
    <button id="accept-cookies" style="margin: 10px; padding: 8px 15px; background-color: #27ae60; color: white; border: none; cursor: pointer;">Accepter</button>
    <button id="refuse-cookies" style="margin: 10px; padding: 8px 15px; background-color: #c0392b; color: white; border: none; cursor: pointer;">Refuser</button>
  </div>
<?php endif; ?>
