<?php
require_once './composants/autoload.php';


checkAccess(['SimpleUser', 'Driver']);

$user = $_SESSION['user'];
$userId = $user->getId();

// Vérification des données envoyées
$tripId = $_POST['trip_id'] ?? null;
$rating = $_POST['rating'] ?? null;
$comment = trim($_POST['comment'] ?? '');

if (!$tripId || !is_numeric($tripId) || !$rating || !is_numeric($rating)) {
    $_SESSION['error'] = "Formulaire invalide.";
    header('Location: ../front/user/tripsStory.php');
    exit;
}

try {
    // Vérifie si un avis existe déjà
    $check = $pdo->prepare("SELECT id FROM ratings WHERE author_id = :author AND trip_id = :trip");
    $check->execute([':author' => $userId, ':trip' => $tripId]);
    if ($check->fetch()) {
        $_SESSION['error'] = "Vous avez déjà laissé une note pour ce trajet.";
        header('Location: ../front/user/tripsStory.php');
        exit;
    }

    // Insertion
    $insert = $pdo->prepare("INSERT INTO ratings (author_id, trip_id, rating, comment, status, created_at)
                             VALUES (:author, :trip, :rating, :comment, 'accepted', NOW())");

    $insert->execute([
        ':author' => $userId,
        ':trip' => $tripId,
        ':rating' => $rating,
        ':comment' => $comment
    ]);

    $_SESSION['success'] = "Votre avis a été enregistré.";
} catch (Exception $e) {
    $_SESSION['error'] = "Erreur lors de l'enregistrement : " . $e->getMessage();
}

header('Location: ../front/user/tripsStory.php');
exit;
