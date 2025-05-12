<?php
require_once './composants/autoload.php';

checkAccess(['SimpleUser', 'Driver']);

$user = $_SESSION['user'];
$userId = $user->getId();

// Récupération et validation
$creditAmount = $_POST['creditAmount'] ?? null;
if (!$creditAmount || !is_numeric($creditAmount) || (int)$creditAmount <= 0) {
    $_SESSION['error'] = "Montant de crédits invalide.";
    header("Location: ajouterCredits.php");
    exit;
}

$creditsToAdd = (int)$creditAmount;

// Mise à jour du solde de l'utilisateur
$stmt = $pdo->prepare("UPDATE users SET credits = credits + :amount WHERE id = :id");
$stmt->execute([
    ':amount' => $creditsToAdd,
    ':id' => $userId
]);

$_SESSION['success'] = "$creditsToAdd crédits ont été ajoutés à votre compte.";
header("Location: ../front/user/home.php");
exit;
