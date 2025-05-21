<?php
require_once './composants/autoload.php'; // charge les classes, PDO, checkToken, etc.

checkAccess(['SimpleUser', 'Driver']); // sécurise l'accès

$_POST = sanityzeArray($_POST , '../front/admin/manageReviews.php');
// Vérifie que la requête est bien en POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Méthode non autorisée.";
    header('Location: ../front/user/addCredits.php');
    exit();
}
