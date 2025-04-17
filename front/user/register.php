<?php
session_start();

// Simule un visiteur (à modifier plus tard selon les connexions)
$_SESSION['typeOfUser'] = null;
$_SESSION['navSelected'] = 'signup';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Inscription - EcoRide</title>
  <link rel="stylesheet" href="../css/style.css" />
  <link rel="stylesheet" href="../css/home.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Lemonada:wght@300..700&display=swap" rel="stylesheet">

</head>
<body>

  <header>

    <!-- Navbar dynamique -->
  <?php include_once '../composants/navbar.php'; ?>

  </header>

  <div class="form-container" >
    <h2>Créer un compte</h2>
    <form action="#" method="post" enctype="multipart/form-data">
      <label for="first-name">Prénom :</label>
      <input type="text" id="first-name" name="first-name" required>

      <label for="name">Nom :</label>
      <input type="text" id="name" name="name" required>

      <label for="email">Adresse email :</label>
      <input type="email" id="email" name="email" required>

      <label for="confirm-email">Confirmer email :</label>
      <input type="email" id="confirm-email" name="confirm-email" required>

      <label for="phone">Numéro de téléphone :</label>
      <input type="tel" id="phone" name="phone" required>

      <label for="password">Mot de passe :</label>
      <input type="password" id="password" name="password" required>

      <label for="confirm-password">Confirmer mot de passe :</label>
      <input type="password" id="confirm-password" name="confirm-password" required>

      <!-- Checkbox chauffeur -->
      <label>
        <input type="checkbox" id="is-driver" name="is-driver">
        Je souhaite m’inscrire en tant que chauffeur
      </label>

      <!--  Input pour le permis, caché par défaut -->
      <div id="driver-permit" class="hidden">
        <label for="permit">Télécharger votre permis de conduire :</label>
        <input type="file" id="permit" name="permit" accept="image/*">
      </div>

      <!-- Mentions légales -->
      <label>
        <input type="checkbox" id="terms" name="terms" required>
        J’accepte les <a href="mentionsLegales.php" target="_blank">mentions légales</a>
      </label>

      <button type="submit">S'inscrire</button>
    </form>

    <p>Déjà un compte ? <a href="../user/login.php">Se connecter</a></p>
  </div>
  
   <!-- Footer -->
   <footer>
    <?php include_once '../composants/footer.html'; ?>
  </footer>

  <script>
    // ***script pour afficher ou masquer l upload de permis en cas d inscription en tant que chauffeur***
    //récupération des champs concernés
    const checkbox = document.getElementById('is-driver');
    const permitField = document.getElementById('driver-permit');
    const permitInput = document.getElementById('permit');
    // event listener sur la checkbox
    checkbox.addEventListener('change', () => {
      // affiche le champs d upload et le rend obligatoire si inscription chauffeur est cliqué
      if (checkbox.checked) {
        permitField.classList.remove('hidden');
        permitInput.required = true;
        //si non le masque et supprime sont obligation
      } else {
        permitField.classList.add('hidden');
        permitInput.required = false;
      }
    });
  </script>

</body>
</html>
