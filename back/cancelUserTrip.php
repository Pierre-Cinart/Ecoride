<?php
// =============================================================
// Script : cancelUserTrip.php
// Rôle : annuler un trajet réservé par un passager
// Conditions :
// - Token valide et mis à jour
// - L'utilisateur est bien le participant du trajet
// - Le trajet est à venir et annulable (plus de 24h avant)
// - L'utilisateur retrouve ses crédits, moins une pénalité
// - Le trajet retrouve une place disponible
// =============================================================

// chargement des class accé à la bdd verification et update JWT
require_once './composants/autoload.php';
// control d accés
checkAccess(['SimpleUser']);

// 1. Protection par méthode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Accès non autorisé.";
    header('Location: ../front/user/account.php');
    exit();
}

// 2. Récupération des données du formulaire
$tripId = $_POST['trip_id'] ?? null;
$user = $_SESSION['user'];
$userId = $user->getId();

if (!$tripId || !is_numeric($tripId)) {
    $_SESSION['error'] = "Trajet invalide.";
    header('Location: ../front/user/showMyTrips.php');
    exit();
}

try {
    // 3. Vérification de la participation de l'utilisateur à ce trajet
    $stmt = $pdo->prepare("SELECT tp.*, t.departure_date FROM trip_participants tp JOIN trips t ON tp.trip_id = t.id WHERE tp.trip_id = :tripId AND tp.user_id = :userId AND tp.confirmed = 1");
    $stmt->execute([':tripId' => $tripId, ':userId' => $userId]);
    $participation = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$participation) {
        $_SESSION['error'] = "Aucune réservation active trouvée.";
        header('Location: ../front/user/showMyTrips.php');
        exit();
    }

    // 4. Vérifie l'éligibilité à l'annulation (24h à l'avance)
    $departureDate = new DateTime($participation['departure_date']);
    $now = new DateTime();
    $hoursUntilDeparture = ($departureDate->getTimestamp() - $now->getTimestamp()) / 3600;

    if ($hoursUntilDeparture < 24) {
        $_SESSION['error'] = "Impossible d'annuler un trajet moins de 24h avant le départ.";
        header('Location: ../front/user/showMyTrips.php');
        exit();
    }

    // 5. Appel à la méthode de la classe User pour gérer l'annulation complète
    $logMessage = "Annulation du trajet ID #$tripId le " . date('d/m/Y', strtotime($participation['departure_date']));

    $success = $user->cancelTrip(
        $pdo,
        $tripId,
        (int)$participation['id'],
        (int)$participation['credits_used'],
        $logMessage
    );

    if ($success) {
        $_SESSION['success'] = "Votre réservation a été annulée. Des crédits vous ont été remboursés (pénalité de 2 crédits appliquée).";
    } else {
        $_SESSION['error'] = $_SESSION['error'] ?? "Une erreur est survenue lors de l'annulation.";
    }
    
    header('Location: ../front/user/showMyTrips.php');
    exit();

} catch (Exception $e) {
    $_SESSION['error'] = "Erreur interne : annulation impossible.";
    header('Location: ../front/user/showMyTrips.php');
    exit();
}
