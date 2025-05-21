<?php
// ==============================
// Traitement d'une action sur un avis utilisateur
// ==============================

require_once './composants/autoload.php';

checkAccess(['Admin', 'Employee']);

// Sécurisation des entrées
$_POST = sanitizeArray($_POST, '../front/admin/manageReviews.php');

// Récupération des données
$reviewId = isset($_POST['review_id']) ? (int) $_POST['review_id'] : 0;
$action = $_POST['action'] ?? '';
$status = $_GET['status'] ?? 'pending'; // Pour revenir sur la bonne vue

// Vérification de base
if (!$reviewId || !in_array($action, ['approve', 'refused', 'delete'])) {
    $_SESSION['error'] = "problème d ' action dans la gestion du commentaire";
    header("Location: ../front/admin/manageReviews.php?status=$status");
    exit;
}

// Récupération de l'objet utilisateur depuis la session
$employee = $_SESSION['user'];

// Appel de la méthode de modération
$success = $employee->manageReview($pdo, $reviewId, $action);

// Gestion du retour utilisateur
if ($success) {
    switch ($action) {
        case 'approve':
            $_SESSION['success'] = "L'avis a bien été validé.";
            break;
        case 'refused':
            $_SESSION['success'] = "L'avis a été refusé.";
            break;
        case 'delete':
            $_SESSION['success'] = "L'avis a été supprimé.";
            break;
    }
} else {
    $_SESSION['error'] = "Une erreur est survenue lors du traitement de l’avis.";
}

header("Location: ../front/admin/manageReviews.php?status=$status");
exit;
