<?php
session_start();

// Simule un visiteur (à modifier plus tard selon les connexions)
$_SESSION['typeOfUser'] = null;
$_SESSION['navSelected'] = 'faq';
?>

<header>

<!-- Navbar dynamique -->
<?php include_once '../composants/navbar.php'; ?>

</header>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>FAQ - EcoRide</title>
  <link rel="stylesheet" href="../css/style.css" />
  <link rel="stylesheet" href="../css/FAQ.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Lemonada:wght@300..700&display=swap" rel="stylesheet">

</head>
<body>

  <div class="faq-container">
    <h1>Foire Aux Questions</h1>

    <div class="faq-item">
      <h3>🔐 Comment créer un compte sur EcoRide ?</h3>
      <p>Vous pouvez créer un compte en cliquant sur "Inscription" dans la barre de navigation. Il vous suffira de remplir vos informations et de valider.</p>
      <br>
      <p>Vous pouvez vous inscrire en tant qu' utilisateur ou chauffeur en cliquant sur la case correspondante . Si vous décidé de postulé en tant que conducteur
        une photo de votre permis sera demandé en format jpeg. Vos documents seront soumis à une vérification par un adminstrateur .
      </p>
      <br>
      <p>
      </p>
    </div>

    <div class="faq-item">
      <h3>🚘 Comment proposer un trajet en tant que conducteur ?</h3>
      <p>Après vous être inscrit en tant que conducteur , une fois vos documents validés vous aurez la possibilité de proposer vos trajet via le bouton "proposer un trajet dans le menu ou 
      via votre gestion de compte (bouton "mon compte" dans le menu) .</p>
    </div>

    <div class="faq-item">
      <h3>💳 Comment fonctionnent les crédits ?</h3>
      <p>Chaque nouvel utilisateur reçoit 20 crédits. Participer à un trajet utilise un nombre de crédits défini par le conducteur, dont 2 sont prélevés pour la plateforme.</p>
    </div>

    <div class="faq-item">
      <h3>📝 Comment consulter les avis sur les conducteurs ?</h3>
      <p>Vous pouvez consulter les avis sur la page "Avis" accessible depuis le menu ou via la page de détails d’un covoiturage.</p>
    </div>

    <div class="faq-item">
      <h3>📄 Mon permis est-il vérifié automatiquement ?</h3>
      <p>Non, une vérification manuelle est faite par un employé après l’envoi du scan de votre permis de conduire.</p>
    </div>

    <div class="faq-item">
      <h3>📧 Comment contacter l’équipe EcoRide ?</h3>
      <p>Vous pouvez utiliser le formulaire de contact disponible via le bouton <a href="../user/contact.php">"Contact"</a> dans le menu ainsi que sur l ' icone <a href="../user/contact.php"><img src="../img/logo/logomail.png" alt="logo mail" style = "transform : translate(0 , 25px);">présent sur le footer du site</a></p>
    </div>
  </div>
  
   <!-- Footer -->
   <footer>
    <?php include_once '../composants/footer.html'; ?>
  </footer>

</body>
</html>
