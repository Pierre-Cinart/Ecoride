<?php
require_once './composants/autoload.php';
checkAccess(['SimpleUser', 'Driver']);

$user = $_SESSION['user'];
$tripId = $_POST['trip_id'] ?? null;
$rating = $_POST['rating'] ?? null;
$comment = trim($_POST['comment'] ?? '');

if (!$tripId || !$rating || !is_numeric($rating)) {
    $_SESSION['error'] = "Note invalide ou données incomplètes.";
    header("Location: ../front/user/tripsStory.php");
    exit;
}

$user->leaveReview($pdo, (int)$tripId, (float)$rating, $comment);

header("Location: ../front/user/tripsStory.php");
exit;
?>