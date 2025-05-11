<?php
/**
 * Composant d'autorisation d'accès basé sur les classes d'objet utilisateur
 * 
 * Utilisation :
 *   include_once '../../back/composants/checkAccess.php';
 *   checkAccess(['SimpleUser', 'Driver']);
 *   
 * Cela permet uniquement aux utilisateurs de type SimpleUser ou Driver d'accéder à la page.
 * Sinon, redirection vers login.php.
 */

function checkAccess(array $allowedClasses, string $redirect = 'login.php') {
    // On vérifie que la session est bien démarrée
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    // Si aucun utilisateur connecté => redirection
    if (!isset($_SESSION['user'])) {
        $_SESSION['error'] = "Accès interdit. Veuillez vous connecter.";
        header("Location: $redirect");
        exit();
    }

    // Vérifie que l'objet utilisateur correspond à une des classes autorisées
    foreach ($allowedClasses as $class) {
        if ($_SESSION['user'] instanceof $class) {
            return; // Accès autorisé, on ne fait rien
        }
    }

    // Sinon redirection
    $_SESSION['error'] = "Vous n'avez pas les droits pour accéder à cette page.";
    header("Location: $redirect");
    exit();
}
