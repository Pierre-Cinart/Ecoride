<?php
  require_once '../../back/composants/autoload.php';
  require_once '../../back/composants/paginate.php';
  require_once '../composants/btnBack.php';
  checkAccess(['Admin','Employee']);
?>

<!DOCTYPE html> <!-- Si le temps le permet gerer les messages internes avec no sql firebase si non back avec envoie de mail classique -->
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Contacter un utilisateur - EcoRide</title>
  <link rel="stylesheet" href="../css/style.css" />
  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Lemonada:wght@300..700&display=swap" rel="stylesheet">
</head>
<body>
  <?php include_once '../composants/navbar.php'; ?>
  </header>

  <?php include_once '../composants/inProgress.php'; 
  btnBack('./manage.php')?>
  <div class="search-bar">
    <!-- bouton de recherche pour autocompletion du nom prenom mail  -->
    <input type="text" placeholder="Pseudo utilisateur">(uniquement pseudo car reponse unique)
    <button type="submit">🔍 Rechercher</button>
  </div>
  <div class="form-container">
    <h2>Contacter l'utilisateur</h2>
    <form action="#" method="post">
      <label for="nom">Nom :</label>
      <input type="text" id="nom" name="nom" value="Dupont" readonly />

      <label for="prenom">Prénom :</label>
      <input type="text" id="prenom" name="prenom" value="Laura" readonly />

      <label for="email">Adresse e-mail :</label>
      <input type="email" id="email" name="email" value="laura.dupont@email.com" readonly />

      <label for="objet">Objet :</label>
      <input type="text" id="objet" name="objet" required />

      <label for="message">Message :</label>
      <textarea id="message" name="message" rows="6" required style="border-radius: 8px; border: 1px solid #ccc; padding: 0.6rem;"></textarea>

      <button type="submit">Envoyer</button>
    </form>
  </div>
 <?php include_once '../composants/footer.php'; ?>
</body>
</html>
