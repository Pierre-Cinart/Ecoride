<?php 
// chargement des class + demarage de sessions et control anti bot + jwt
require_once './composants/autoload.php';
//Autorisation d accés
checkAccess(['SimpleUser','Driver']);

// Vérifie que le formulaire a bien été soumis en POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Méthode non autorisée.";
    header('Location: ../front/user/reserv.php');
    exit();
}

// 2.verification googleCaptcha
verifyCaptcha('reserve', '../front/user/reserv.php'); // ← action + redirection


$user = $_SESSION['user'];
$tripId = (int) $_POST['trip_id'] ?? null;

if (!$tripId || !is_numeric($tripId)) {
    $_SESSION['error'] = "Trajet invalide.";
    header('Location: ../front/user/search.php');
    exit;
}

// Appel de la méthode orientée objet
if ($user->reserveTrip($pdo, $tripId)) {
    $_SESSION['user'] = $user; // met à jour en session
    $_SESSION['success'] = "Réservation confirmée.";
    header('Location: ../front/user/account.php');
} else {
    header('Location: ../front/user/reserv.php');
}
exit; ?>