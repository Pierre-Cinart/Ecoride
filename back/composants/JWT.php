<?php
// JWT.php — Gestion des tokens JWT-like pour les sessions sécurisées

// ===== CHEMIN DE REDIRECTION SELON LE CONTEXTE (back ou front) =====
$dir = $_SERVER['SCRIPT_FILENAME']; // Fichier actuellement exécuté

if (strpos($dir, 'back') !== false) {
    // ➤ Si le fichier est exécuté depuis le dossier 'back' (ex: back/composants/...), 
    // il faut remonter jusqu’au front pour accéder à la page login :
    $redirectURL = '../../front/user/login.php';
} elseif (strpos($dir, 'front') !== false) {
    // ➤ Si le fichier est exécuté depuis n’importe quel sous-dossier du front 
    // (ex: front/user/, front/driver/, front/admin/...), 
    // on remonte d’un dossier puis on accède à user/login.php :
    $redirectURL = '../user/login.php';
} else {
    // ➤ Sécurité fallback si l’on ne peut pas déterminer l’origine :
    $redirectURL = '/front/user/login.php';
}

/**
 * Génère un token sécurisé de 128 caractères (256 bits en hexadécimal)
 * @return string
 */
function createToken() {
    return bin2hex(random_bytes(64));
}

/**
 * Met à jour le token et sa date d'expiration pour un utilisateur
 *
 * @param PDO $pdo Connexion PDO à la base de données
 * @param string $token Le nouveau token généré
 * @param int $userId L'ID de l'utilisateur concerné
 */
function updateToken(PDO $pdo, string $token, int $userId) {
    global $redirectURL;

    $checkUser = $pdo->prepare("SELECT id FROM users WHERE id = :id");
    $checkUser->execute([':id' => $userId]);

    if ($checkUser->rowCount() > 0) {
        $update = $pdo->prepare("
            UPDATE users 
            SET jwt_token = :token, 
                jwt_token_time = DATE_ADD(NOW(), INTERVAL 2 HOUR)
            WHERE id = :id
        ");
        $update->execute([
            ':token' => $token,
            ':id' => $userId
        ]);
    } else {
        session_unset();
        $_SESSION['error'] = "Utilisateur introuvable. Veuillez vous connecter.";
        header('Location: ' . $redirectURL);
        exit();
    }
}

/**
 * Vérifie que le token de session est valide (correspondance + non expiré)
 * Si c'est le cas, génère un nouveau token et le met à jour automatiquement
 *
 * @param PDO $pdo Connexion PDO à la base de données
 * @return bool true si la session est valide, redirection sinon
 */
function checkToken(PDO $pdo) {
    global $redirectURL;

    if (isset($_SESSION['user']) && isset($_SESSION['jwt'])) {
        $user = $_SESSION['user'];
        $userId = $user->getId();
        $token = $_SESSION['jwt'];

        $stmt = $pdo->prepare("SELECT jwt_token, jwt_token_time FROM users WHERE id = :id");
        $stmt->execute([':id' => $userId]);

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $now = new DateTime();
            $expiresAt = new DateTime($row['jwt_token_time']);

            if ($row['jwt_token'] === $token && $now < $expiresAt) {
                // ✅ Authentification réussie → on renouvelle le token
                $newToken = createToken();
                $_SESSION['jwt'] = $newToken;
                updateToken($pdo, $newToken, $userId);
                return true;
            } else {
                // ❌ Token incorrect ou expiré
                session_unset();
                $_SESSION['error'] = "Session expirée, veuillez vous reconnecter.";
                header('Location: ' . $redirectURL);
                exit();
            }
        } else {
            // ❌ Utilisateur inexistant en base
            session_unset();
            $_SESSION['error'] = "Utilisateur introuvable.";
            header('Location: ' . $redirectURL);
            exit();
        }
    } else {
        // ❌ Aucun token en session
        session_unset();
        $_SESSION['error'] = "Accès refusé. Veuillez vous connecter.";
        header('Location: ' . $redirectURL);
        exit();
    }
}
