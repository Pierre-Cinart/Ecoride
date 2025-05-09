<?php
require_once '../../back/classes/User.php';
require_once '../../back/classes/SimpleUser.php';
require_once '../../back/classes/Driver.php';
require_once '../../back/classes/Employee.php';
require_once '../../back/classes/Admin.php';

session_start();
var_dump($_SESSION['user']);
// Mise en surbrillance du lien actif
$navSelected = $_SESSION['navSelected'] ?? '';
$user = $_SESSION['user'] ?? null;

?>

<nav class="navbar">
  <!-- Logo aligné à gauche -->
  <div class="logo">
    <a href="../user/home.php">
      <img src="../img/logo/logo.png" alt="Logo EcoRide">
    </a>
  </div>

  <!-- Liens de navigation -->
  <div class="nav-links">
    <?php if (!$user): ?>
      <!-- VISITEUR -->
      <a href="../user/home.php" class="<?= ($navSelected === 'home') ? 'selected' : '' ?>">Accueil</a>
      <a href="../user/contact.php" class="<?= ($navSelected === 'contact') ? 'selected' : '' ?>">Contact</a>
      <a href="../user/search.php" class="<?= ($navSelected === 'search') ? 'selected' : '' ?>">Rechercher un trajet</a>
      <a href="../user/reviews.php" class="<?= ($navSelected === 'reviews') ? 'selected' : '' ?>">Avis</a>
      <a href="../user/faq.php" class="<?= ($navSelected === 'faq') ? 'selected' : '' ?>">FAQ</a>
      <a href="../user/login.php" class="<?= ($navSelected === 'login') ? 'selected' : '' ?>">Connexion</a>
      <a href="../user/register.php" class="<?= ($navSelected === 'signup') ? 'selected' : '' ?>">Inscription</a>

    <?php elseif ($user instanceof SimpleUser): ?>
      <!-- UTILISATEUR SIMPLE -->
      <a href="../user/home.php" class="<?= ($navSelected === 'home') ? 'selected' : '' ?>">Accueil</a>
      <a href="../user/contact.php" class="<?= ($navSelected === 'contact') ? 'selected' : '' ?>">Contact</a>
      <a href="../user/search.php" class="<?= ($navSelected === 'search') ? 'selected' : '' ?>">Rechercher un trajet</a>
      <a href="../user/reviews.php" class="<?= ($navSelected === 'reviews') ? 'selected' : '' ?>">Avis</a>
      <a href="../user/faq.php" class="<?= ($navSelected === 'faq') ? 'selected' : '' ?>">FAQ</a>
      <a href="../user/account.php" class="<?= ($navSelected === 'account') ? 'selected' : '' ?>">Mon compte</a>
      <a href="../user/logout.php">Déconnexion</a>

    <?php elseif ($user instanceof Driver): ?>
      <!-- CONDUCTEUR -->
      <a href="../user/home.php" class="<?= ($navSelected === 'home') ? 'selected' : '' ?>">Accueil</a>
      <a href="../driver/addTrip.php" class="<?= ($navSelected === 'offer') ? 'selected' : '' ?>">Proposer un trajet</a>
      <a href="../user/search.php" class="<?= ($navSelected === 'search') ? 'selected' : '' ?>">Rechercher un trajet</a>
      <a href="../user/reviews.php" class="<?= ($navSelected === 'reviews') ? 'selected' : '' ?>">Avis</a>
      <a href="../user/faq.php" class="<?= ($navSelected === 'faq') ? 'selected' : '' ?>">FAQ</a>
      <a href="../user/account.php" class="<?= ($navSelected === 'account') ? 'selected' : '' ?>">Mon compte</a>
      <a href="../user/logout.php">Déconnexion</a>

    <?php elseif ($user instanceof Employee): ?>
      <!-- EMPLOYÉ -->
      <a href="../user/home.php" class="<?= ($navSelected === 'home') ? 'selected' : '' ?>">Accueil</a>
      <a href="../admin/manage.php" class="<?= ($navSelected === 'manage') ? 'selected' : '' ?>">Gestion</a>
      <a href="../admin/messages.php" class="<?= ($navSelected === 'messages') ? 'selected' : '' ?>">Messages</a>
      <a href="../user/logout.php">Déconnexion</a>

    <?php elseif ($user instanceof Admin): ?>
      <!-- ADMINISTRATEUR -->
      <a href="../user/home.php" class="<?= ($navSelected === 'home') ? 'selected' : '' ?>">Accueil</a>
      <a href="../admin/charts.php" class="<?= ($navSelected === 'stats') ? 'selected' : '' ?>">Statistiques</a>
      <a href="../admin/manage.php" class="<?= ($navSelected === 'manage') ? 'selected' : '' ?>">Gestion</a>
      <a href="../admin/messages.php" class="<?= ($navSelected === 'messages') ? 'selected' : '' ?>">Messages</a>
      <a href="../user/logout.php">Déconnexion</a>
    <?php endif; ?>
  </div>

  <div class="burger">
    <span></span>
    <span></span>
    <span></span>
  </div>

  <!-- Pop-up messages -->
  <div id="popup" class="<?= isset($_SESSION['success']) ? 'success' : (isset($_SESSION['error']) ? 'error' : '') ?>">
    <?= $_SESSION['success'] ?? $_SESSION['error'] ?? '' ?>
  </div>
  <?php unset($_SESSION['success'], $_SESSION['error']); ?>
</nav>
