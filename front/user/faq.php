<?php
session_start();
$_SESSION['navSelected'] = 'faq';
?>
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

<header>
  <?php include_once '../composants/navbar.php'; ?>
</header>

<div class="faq-container">
  <h1>Foire Aux Questions</h1>

  <div class="faq-item">
    <h3>🔐 Comment créer un compte sur EcoRide ?</h3>
    <p>Vous pouvez créer un compte en cliquant sur "Inscription" dans la barre de navigation. Il vous suffira de remplir vos informations et de valider.</p>
    <p>Vous pouvez vous inscrire en tant qu'utilisateur ou chauffeur en cochant la case correspondante. Si vous postulez en tant que conducteur, une photo de votre permis de conduire vous sera demandée. Vos documents seront soumis à une vérification manuelle par un administrateur.</p>
  </div>

  <div class="faq-item">
    <h3>🚘 Comment proposer un trajet en tant que conducteur ?</h3>
    <p>Après validation de votre permis, vous aurez accès à la fonctionnalité "Proposer un trajet" via votre compte ou le menu conducteur.</p>
  </div>

  <div class="faq-item">
    <h3>💳 Comment fonctionnent les crédits ?</h3>
    <p>Chaque nouvel utilisateur reçoit 20 crédits. Participer à un trajet utilise un certain nombre de crédits défini par le conducteur. Une commission de 2 crédits est prélevée par la plateforme sur chaque trajet.</p>
  </div>

  <div class="faq-item">
    <h3>📤 Puis-je demander un remboursement ?</h3>
    <p>Oui. Si vous avez acheté des crédits et que vous n’en avez plus l’utilité, vous pouvez en demander le remboursement via le formulaire de remboursement dans votre espace utilisateur. Cette demande sera soumise à validation par un administrateur.</p>
  </div>

  <div class="faq-item">
    <h3>❌ Que se passe-t-il si j’annule un trajet réservé ?</h3>
    <p>L’annulation d’un trajet entraîne la perte d’une partie des crédits engagés. Un message vous en informe avant validation.</p>
  </div>

  <div class="faq-item">
    <h3>💰 Comment recevoir un paiement si je suis conducteur ?</h3>
    <p>Vous pouvez convertir vos crédits en argent réel une fois un certain seuil atteint. Il vous suffit d’accéder à la page "Obtenir un paiement" dans votre espace conducteur.</p>
  </div>

  <div class="faq-item">
    <h3>📝 Comment consulter les avis sur les conducteurs ?</h3>
    <p>Vous pouvez consulter les avis sur la page "Avis" accessible depuis le menu ou via la fiche de trajet d’un conducteur.</p>
  </div>

  <div class="faq-item">
    <h3>📄 Mon permis est-il vérifié automatiquement ?</h3>
    <p>Non, la vérification est manuelle et effectuée par un employé une fois le document téléchargé depuis votre espace personnel.</p>
  </div>

  <div class="faq-item">
      <h3>📧 Comment contacter l’équipe EcoRide ?</h3>
      <p class = "y-";>Vous pouvez utiliser le formulaire de contact disponible via le bouton <a href="../user/contact.php">"Contact"</a> dans le menu ainsi que sur l ' icone <a href="../user/contact.php"><img src="../img/logo/logomail.png" alt="logo mail" ></a>présent sur le footer du site</a></p>
  </div>
</div>

<?php include_once '../composants/footer.html'; ?>

</body>
</html>

