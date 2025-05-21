<?php
require_once './composants/autoload.php';
checkAccess(['Driver']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Méthode non autorisée.";
    header('Location: ../front/user/account.php');
    exit();
}

$tripId = isset($_POST['trip_id']) ? (int) $_POST['trip_id'] : 0;
if ($tripId <= 0) {
    $_SESSION['error'] = "ID du trajet invalide.";
    header('Location: ../front/user/ownTrips.php');
    exit();
}

$user = $_SESSION['user'];
if (!$user instanceof Driver) {
    $_SESSION['error'] = "Accès refusé.";
    header('Location: ../front/user/ownTrips.php');
    exit();
}

try {
    $pdo->beginTransaction();
    $user->cancelOwnTrip($pdo, $tripId);
    $pdo->commit();
    $_SESSION['success'] = "Trajet annulé avec succès.";
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = "Erreur lors de l'annulation : " . $e->getMessage();
}

header('Location: ../front/user/ownTrips.php');
exit();
?>
