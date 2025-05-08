<?php
// tokenManager.php - gestion des tokens de session avec PDO

// Fonction pour générer un token aléatoire de 128 caractères
function createToken() {
    return bin2hex(random_bytes(64));
}

// Fonction pour mettre à jour le token d'un utilisateur
function updateToken(PDO $pdo, string $token, int $userId) {
    // Vérifie que l'utilisateur existe
    $checkUser = $pdo->prepare("SELECT id FROM users WHERE id = :id");
    $checkUser->execute([':id' => $userId]);

    if ($checkUser->rowCount() > 0) {
        // Mise à jour du token et de la date d'expiration (+2 heures)
        $update = $pdo->prepare("UPDATE users SET jws_token = :token, jws_token_time = DATE_ADD(NOW(), INTERVAL 2 HOUR) WHERE id = :id");
        $update->execute([
            ':token' => $token,
            ':id' => $userId
        ]);
    } else {
        session_unset();
        $_SESSION['error'] = "Utilisateur introuvable.";
        header('Location: ../admin/index.php');
        exit();
    }
}

// Fonction pour vérifier la validité du token
function checkToken(PDO $pdo) {
    if (isset($_SESSION['id']) && isset($_SESSION['token'])) {
        $userId = (int)$_SESSION['id'];
        $token = $_SESSION['token'];

        // Récupère le token et sa date d'expiration en base
        $stmt = $pdo->prepare("SELECT jws_token, jws_token_time FROM users WHERE id = :id");
        $stmt->execute([':id' => $userId]);

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // Vérifie que le token correspond et qu'il n'a pas expiré
            if ($row['jws_token'] === $token && new DateTime() < new DateTime($row['jws_token_time'])) {
                // Génère un nouveau token et le met à jour
                $newToken = createToken();
                $_SESSION['token'] = $newToken;
                updateToken($pdo, $newToken, $userId);
            } else {
                session_unset();
                $_SESSION['error'] = "Session expirée, veuillez vous reconnecter.";
                header('Location: ../admin/index.php');
                exit();
            }
        } else {
            session_unset();
            $_SESSION['error'] = "Utilisateur introuvable.";
            header('Location: ../admin/index.php');
            exit();
        }
    } else {
        session_unset();
        $_SESSION['error'] = "Accès refusé. Veuillez vous connecter.";
        header('Location: ../admin/index.php');
        exit();
    }
}

