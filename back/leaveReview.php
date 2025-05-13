<?php
//Dépendances et vérifications 
require_once './composants/autoload.php';
// control d ' accés
checkAccess(['SimpleUser', 'Driver']);

// Vérifie que le formulaire est bien soumis en post
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Méthode non autorisée.";
    header('Location: ../front/user/leaveReview.php');
    exit();
}

// verification googleCaptcha
verifyCaptcha('leaveReview', '../front/user/reserv.php'); // ← action + redirection

// Nettoyage des données postées (et sécurité contre les tableaux malicieux)
$_POST = sanitizeArray($_POST, '../front/user/contact.php');

// assignations inputs
$user = $_SESSION['user'];
$tripId = getPostInt('trip_id');
$rating = getPostFloat('rating', 1);

$comment = $_POST['comment'] ?? '';

if (!$tripId || !$rating ) {
    $_SESSION['error'] = "Note invalide ou données incomplètes.";
    header("Location: ../front/user/tripsStory.php");
    exit;
}

$user->leaveReview($pdo, $tripId, $rating, $comment);

header("Location: ../front/user/tripsStory.php");
exit;
?>