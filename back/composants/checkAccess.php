<?php
/**
 * Composant d'autorisation d'accès avec vérification + renouvellement du JWT
 * 
 * Utilisation :
 *   include_once '../../back/composants/checkAccess.php';
 *   checkAccess(['SimpleUser', 'Driver']);
 * 
 * Nécessite que autoload.php ait déjà démarré la session.
 */

require_once __DIR__ . '/JWT.php';

function checkAccess(array $allowedClasses, string $redirect = 'login.php') {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    // Aucun utilisateur connecté
    if (!isset($_SESSION['user']) || !isset($_SESSION['jwt'])) {
        $_SESSION['error'] = "Accès interdit. Veuillez vous connecter.";
        header("Location: $redirect");
        exit();
    }

    // Vérifie et renouvelle le token
    if (!checkToken($GLOBALS['pdo'])) {
        $_SESSION['error'] = "Session expirée. Veuillez vous reconnecter.";
        header("Location: $redirect");
        exit();
    }

    // Renouvellement du token
    $newToken = createToken();
    $_SESSION['jwt'] = $newToken;
    updateToken($GLOBALS['pdo'], $newToken, $_SESSION['user']->getId());

    // Vérifie le rôle (via instanceof)
    foreach ($allowedClasses as $class) {
        if ($_SESSION['user'] instanceof $class) {
            return; // OK
        }
    }

    // Sinon, accès interdit
    $_SESSION['error'] = "Vous n'avez pas les droits pour accéder à cette page.";
    header("Location: $redirect");
    exit();
}
