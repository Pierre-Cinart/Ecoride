<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Contacter un utilisateur - EcoRide</title>
  <link rel="stylesheet" href="../css/style.css" />
</head>
<body>

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

</body>
</html>
