<?php
/**
 * Vérifie que l'utilisateur a accès à la page demandée selon son type et son statut.
 * 
 * Usage :
 *   require_once '../../back/composants/checkAccess.php';
 *   checkAccess(['SimpleUser', 'Driver']);
 */

function checkAccess(array $allowedClasses): void {
    // === Démarrage de session si nécessaire ===
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    // === Vérification : utilisateur connecté ? ===
    if (!isset($_SESSION['user']) || !$_SESSION['user'] instanceOf User ) {
        $_SESSION['error'] = "Accès interdit. Veuillez vous connecter.";
        redirectToHome();
    }

    $user = $_SESSION['user'];
    // verification de bannissement
    if ( $user->getStatus() === 'banned'){
        session_unset(); // Supprime toutes les variables de session
        session_destroy(); // Détruit complètement la session

        session_start(); // Redémarre une session propre pour définir le message
        $_SESSION['error'] = "Ce compte à était bannit de nos services.Pour toute informations supplémentaires contactez l ' équipe via le formulaire de la page contact";
        header('Location: ../user/login.php');
        exit;
    }

    // === Vérification : utilisateur banni ? ===
    if (method_exists($user, 'getStatus') && $user->getStatus() === 'banned') {
        $_SESSION['error'] = "Votre compte a été banni. Veuillez contacter l’équipe EcoRide.";
        session_destroy(); // Déconnecte complètement
        redirectToHome();
    }

    // === Vérification : classe utilisateur autorisée ? ===
    foreach ($allowedClasses as $class) {
        if ($user instanceof $class) {
            return; // Accès autorisé
        }
    }

    // Sinon : accès refusé
    $_SESSION['error'] = "Vous n’avez pas les droits pour accéder à cette page.";
    redirectToHome();
}

/**
 * Redirige automatiquement vers la bonne page Home selon l’origine de l’appel (back ou front).
 */
function redirectToHome(): void {
    $dir = $_SERVER['SCRIPT_FILENAME']; // Fichier actuellement exécuté

    if (strpos($dir, 'back') !== false) {
        // Si exécuté depuis le dossier back, on redirige vers la Home du front
        $redirectURL = '../../front/user/home.php';
    } elseif (strpos($dir, 'front') !== false) {
        // Si exécuté depuis un dossier du front
        $redirectURL = '../user/home.php';
    } else {
        // Par défaut, sécurité (exécute depuis racine projet)
        $redirectURL = '/front/user/home.php';
    }

    header("Location: $redirectURL");
    exit();
}
