<?php
require_once './composants/autoload.php';
checkAccess(['SimpleUser', 'Driver', 'Admin', 'Employee']);

$user = $_SESSION['user'];
$ratingId = $_POST['rating_id'] ?? null;

if (!$ratingId) {
    $_SESSION['error'] = "Aucun avis spécifié pour suppression.";
    header("Location: ../front/user/tripsStory.php");
    exit;
}

// Appelle la méthode métier
$user->deleteReview($pdo, (int)$ratingId);

header("Location: ../front/user/tripsStory.php");
exit;
