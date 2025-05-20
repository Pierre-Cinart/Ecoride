<footer>
    <div class="footer-container">
      
      <!-- Colonne 1 -->
      <div class="footer-col">
        <h3>EcoRide</h3>
        <p>"Voyageons vert ensemble"</p>
      </div>
  
      <!-- Colonne 2 -->
      <div class="footer-col">
        <a href="../user/mentionsLegales.php" class="legal-link">Mentions légales</a>
      </div>
  
      <!-- Colonne 3 -->
      <div class="footer-col">
        <p>Contact :</p>
        <div class="footer-icons">
          <a href="https://www.facebook.com" target="_blank">
            <img src="../img/logo/logofb.svg" alt="Facebook">
          </a>
          <a href="https://www.instagram.com" target="_blank">
            <img src="../img/logo/logoinsta.svg" alt="Instagram">
          </a>
          <a href="../user/contact.php">
            <img src="../img/logo/logomail.png" alt="Mail">
          </a>
        </div>
      </div>
  
    </div>
  </footer>
  <script src="../js/burger.js"></script>
  <script src="../js/popUp.js"></script>
  <!-- recupération de config google analytics -->
  <?php  
    $configAnalytics = include_once __DIR__ . '/../../back/config/configAnalytics.php';
  ?>
  <script>
    const firebaseConfig = {
    apiKey: <?= json_encode($ANALYTICS_KEY) ?>,
    authDomain: <?= json_encode($AUTDOMAIN) ?>,
    projectId: <?= json_encode($PROJECT_ID) ?>,
    storageBucket: <?= json_encode($STORAGE_BUCKET) ?>,
    messagingSenderId: <?= json_encode($MESSAGE_ID_SENDER) ?>,
    appId: <?= json_encode($APP_ID) ?>,
    measurementId: <?= json_encode($MESUREMENT_ID) ?>
  };
</script>
 
  <!-- consentement cookie et chargement google analytics -->
  <script src="../js/consent.js" defer></script>
 

