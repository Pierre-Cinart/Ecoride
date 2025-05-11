<?php 
require_once './composants/autoload.php';
require_once './composants/db_connect.php';
session_start();

if (!isset($_SESSION['user']) || !($_SESSION['user'] instanceof SimpleUser || $_SESSION['user'] instanceof Driver)) {
    $_SESSION['error'] = "Vous devez être connecté pour réserver.";
    header('Location: ../front/user/login.php');
    exit;
}

$user = $_SESSION['user'];
$tripId = $_POST['trip_id'] ?? null;

if (!$tripId || !is_numeric($tripId)) {
    $_SESSION['error'] = "Trajet invalide.";
    header('Location: ../front/user/search.php');
    exit;
}

// Appel de la méthode orientée objet
if ($user->reserv($pdo, (int)$tripId)) {
    $_SESSION['user'] = $user; // met à jour en session
    $_SESSION['success'] = "Réservation confirmée.";
    header('Location: ../front/user/account.php');
} else {
    header('Location: ../front/user/reserv.php');
}
exit; ?>