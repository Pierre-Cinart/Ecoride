<?php
require_once './composants/autoload.php';

// Contrôle d'accès
checkAccess(['SimpleUser', 'Driver']);

// Vérifie que le formulaire est bien soumis
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Méthode non autorisée.";
    header('Location: ../front/user/addCredits.php');
    exit();
}

// Vérification Google reCAPTCHA (action = addCredits)
verifyCaptcha('addCredits', '../front/user/addCredits.php');

// Nettoyage des données
$_POST = sanitizeArray($_POST, '../front/user/addCredits.php');

// Récupération du montant à ajouter
$creditAmount = (int) ($_POST['creditAmount'] ?? 0);
if ($creditAmount <= 0) {
    $_SESSION['error'] = "Montant de crédits invalide.";
    header('Location: ../front/user/addCredits.php');
    exit();
}

// Récupération de l'utilisateur
$user = $_SESSION['user'] ?? null;

// Vérifie que l'objet est bien une instance autorisée
if (!$user || !($user instanceof SimpleUser || $user instanceof Driver)) {
    $_SESSION['error'] = "Accès non autorisé.";
    header('Location: ../front/user/addCredits.php');
    exit();
}

// Mise à jour des crédits via la méthode de classe
if ($user->updateCredits($pdo, $creditAmount)) {
    $_SESSION['success'] = "$creditAmount crédits ont été ajoutés à votre compte.";
    header('Location: ../front/user/home.php');
    exit();
} else {
    $_SESSION['error'] = "Une erreur est survenue lors de l’ajout des crédits.";
    header('Location: ../front/user/addCredits.php');
    exit();
}
