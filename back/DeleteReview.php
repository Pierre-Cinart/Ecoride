<?php
// Chargement des classes, connexion BDD, contrôle d'accès
require_once './composants/autoload.php';

checkAccess(['SimpleUser', 'Driver','Admin','Employee']);

$user = $_SESSION['user'];
$userId = $user->getId();

// Vérifie que l'ID du rating est bien envoyé
$ratingId = $_POST['rating_id'] ?? null;
if (!$ratingId) {
    $_SESSION['error'] = "Aucun avis spécifié pour suppression.";
    header("Location: ../front/user/tripsStory.php");
    exit;
}

// Vérifie que l'avis appartient bien à l'utilisateur
$stmt = $pdo->prepare("SELECT id, author_id FROM ratings WHERE id = :id AND author_id = :uid");
$stmt->execute([':id' => $ratingId, ':uid' => $userId]);
$rating = $stmt->fetch();

if (!$rating) {
    $_SESSION['error'] = "Avis non trouvé ou non autorisé.";
    header("Location: ../front/user/tripsStory.php");
    exit;
}

// Mise à jour : suppression du commentaire uniquement
$stmtUpdate = $pdo->prepare("UPDATE ratings SET comment = NULL , status = 'deleted' WHERE id = :id");
$stmtUpdate->execute([':id' => $ratingId]);

$_SESSION['success'] = "Votre commentaire a bien été supprimé.";
header("Location: ../front/user/tripsStory.php");
exit;
