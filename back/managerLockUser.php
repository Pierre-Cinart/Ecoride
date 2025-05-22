<?php

require_once './composants/autoload.php';

try {
    // Sécurité : s'assurer que l'utilisateur est connecté et autorisé
    if (!isset($_SESSION['user']) || !$_SESSION['user'] instanceof Employee) {
        throw new Exception("Accès non autorisé.");
    }

    // Vérification des données reçues
    $userId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : null;
    $status = $_POST['status'] ?? '';
    $action = $_POST['action'] ?? '';

    if (!$userId || !$action) {
        throw new Exception("Données invalides.");
    }

    /** @var Employee $employe */
    $employe = $_SESSION['user'];
  
    // Traitement selon l'action
    switch ($action) {
        case 'block':
            $employe->blockUserCompletely($pdo, $userId);
            break;
        case 'unblock_user':
            $employe->unblockUser($pdo, $userId);
            break;
        case 'unblock_driver':
            $employe->unblockDriver($pdo, $userId);
            break;
        default:
            throw new Exception("Action inconnue.");
    }
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
}

// Redirection finale
header("Location: ../front/admin/manageUsers.php");
exit;
