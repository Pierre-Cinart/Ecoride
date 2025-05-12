<?php
require_once './composants/autoload.php';

checkAccess(['SimpleUser', 'Driver']);

$user = $_SESSION['user'];
$userId = $user->getId();

$tripId = $_POST['trip_id'] ?? null;
$rating = $_POST['rating'] ?? null;
$comment = trim($_POST['comment'] ?? '');

if (!$tripId || !$rating || !is_numeric($rating)) {
    $_SESSION['error'] = "Note invalide ou données incomplètes.";
    header("Location: ../front/user/tripsStory.php");
    exit;
}

// Vérifie la participation au trajet
$stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM trip_participants WHERE trip_id = :trip AND user_id = :user AND confirmed = 1");
$stmtCheck->execute([':trip' => $tripId, ':user' => $userId]);
if ($stmtCheck->fetchColumn() == 0) {
    $_SESSION['error'] = "Vous n’avez pas participé à ce trajet.";
    header("Location: ../front/user/tripsStory.php");
    exit;
}

// Vérifie s'il existe déjà un rating
$stmtExists = $pdo->prepare("SELECT id, status FROM ratings WHERE author_id = :user AND trip_id = :trip");
$stmtExists->execute([':user' => $userId, ':trip' => $tripId]);
$existing = $stmtExists->fetch(PDO::FETCH_ASSOC);

if ($existing) {
    if (in_array($existing['status'], ['waiting', 'pending'])) {
        // Met à jour un avis en attente ou non encore posté
        $update = $pdo->prepare("UPDATE ratings SET rating = :rating, comment = :comment, status = 'pending', created_at = NOW() WHERE id = :id");
        $update->execute([
            ':rating' => $rating,
            ':comment' => $comment ?: null,
            ':id' => $existing['id']
        ]);
        $_SESSION['success'] = "Avis mis à jour. En attente de validation.";
    } else {
        $_SESSION['error'] = "Vous avez déjà un avis validé pour ce trajet.";
    }
} else {
    // Insertion d’un nouvel avis
    $stmtInsert = $pdo->prepare("INSERT INTO ratings (author_id, trip_id, rating, comment, status, created_at)
                                 VALUES (:author, :trip, :rating, :comment, 'pending', NOW())");
    $stmtInsert->execute([
        ':author' => $userId,
        ':trip' => $tripId,
        ':rating' => $rating,
        ':comment' => $comment ?: null,
    ]);
    $_SESSION['success'] = "Merci pour votre avis ! Il sera publié après validation.";
}

header("Location: ../front/user/tripsStory.php");
exit;
