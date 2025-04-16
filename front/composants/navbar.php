<?php
//récupération des variables de session
$type = $_SESSION['typeOfUser'] ?? null;         // Rôle de l'utilisateur
$navSelected = $_SESSION['navSelected'] ?? '';   // Lien sélectionné
?>

<!-- Navbar HTML -->
<nav class="navbar">
  <!-- Logo aligné à gauche -->
  <div class="logo">
    <a href="../index.php">
      <img src="../img/logo/logo.png" alt="Logo EcoRide">
    </a>
  </div>

  <!-- Liens de navigation alignés à droite -->
  <div class="nav-links">
    <?php if ($type === null): ?>
      <!-- VISITEUR -->
      <a href="../user/home.php" class="<?= ($navSelected === 'home') ? 'selected' : '' ?>">Accueil</a>
      <a href="../user/contact.php" class="<?= ($navSelected === 'contact') ? 'selected' : '' ?>">Contact</a>
      <a href="../user/search.php" class="<?= ($navSelected === 'search') ? 'selected' : '' ?>">Rechercher un trajet</a>
      <a href="../user/reviews.php" class="<?= ($navSelected === 'reviews') ? 'selected' : '' ?>">Avis</a>
      <a href="../user/faq.php" class="<?= ($navSelected === 'faq') ? 'selected' : '' ?>">FAQ</a>
      <a href="../user/login.php" class="<?= ($navSelected === 'login') ? 'selected' : '' ?>">Connexion</a>
      <a href="../user/register.php" class="<?= ($navSelected === 'signup') ? 'selected' : '' ?>">Inscription</a>

    <?php elseif ($type === 'user'): ?>
      <!-- UTILISATEUR CONNECTÉ -->
      <a href="../user/home.php" class="<?= ($navSelected === 'home') ? 'selected' : '' ?>">Accueil</a>
      <a href="../user/contact.php" class="<?= ($navSelected === 'contact') ? 'selected' : '' ?>">Contact</a>
      <a href="../user/search.php" class="<?= ($navSelected === 'search') ? 'selected' : '' ?>">Rechercher un trajet</a>
      <a href="../user/reviews.php" class="<?= ($navSelected === 'reviews') ? 'selected' : '' ?>">Avis</a>
      <a href="../user/faq.php" class="<?= ($navSelected === 'faq') ? 'selected' : '' ?>">FAQ</a>
      <a href="../user/account.php" class="<?= ($navSelected === 'account') ? 'selected' : '' ?>">Mon compte</a>
      <a href="../user/logout.php">Déconnexion</a>

    <?php elseif ($type === 'driver'): ?>
      <!-- CONDUCTEUR -->
      <a href="../user/home.php" class="<?= ($navSelected === 'home') ? 'selected' : '' ?>">Accueil</a>
      <a href="../driver/offer.php" class="<?= ($navSelected === 'offer') ? 'selected' : '' ?>">Proposer un trajet</a>
      <a href="../user/search.php" class="<?= ($navSelected === 'search') ? 'selected' : '' ?>">Rechercher un trajet</a>
      <a href="../user/reviews.php" class="<?= ($navSelected === 'reviews') ? 'selected' : '' ?>">Avis</a>
      <a href="../user/faq.php" class="<?= ($navSelected === 'faq') ? 'selected' : '' ?>">FAQ</a>
      <a href="../driver/account.php" class="<?= ($navSelected === 'account') ? 'selected' : '' ?>">Mon compte</a>
      <a href="../user/logout.php">Déconnexion</a>

    <?php elseif ($type === 'employee'): ?>
      <!-- EMPLOYÉ -->
      <a href="../user/home.php" class="<?= ($navSelected === 'home') ? 'selected' : '' ?>">Accueil</a>
      <a href="../employee/manage.php" class="<?= ($navSelected === 'manage') ? 'selected' : '' ?>">Gestion</a>
      <a href="../employee/messages.php" class="<?= ($navSelected === 'messages') ? 'selected' : '' ?>">Messages</a>
      <a href="../user/logout.php">Déconnexion</a>

    <?php elseif ($type === 'admin'): ?>
      <!-- ADMIN -->
      <a href="../user/home.php" class="<?= ($navSelected === 'home') ? 'selected' : '' ?>">Accueil</a>
      <a href="../admin/stats.php" class="<?= ($navSelected === 'stats') ? 'selected' : '' ?>">Statistiques</a>
      <a href="../admin/manage.php" class="<?= ($navSelected === 'manage') ? 'selected' : '' ?>">Gestion</a>
      <a href="../admin/messages.php" class="<?= ($navSelected === 'messages') ? 'selected' : '' ?>">Messages</a>
      <a href="../user/logout.php">Déconnexion</a>
    <?php endif; ?>
  </div>
</nav>
