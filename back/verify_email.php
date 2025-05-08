<?php
session_start();
require_once '../back/composants/db_connect.php';

$token = $_GET['token'] ?? null;

if (!$token) {
    $_SESSION['error'] = "Lien de vérification invalide ou manquant.";
    header('Location: ../front/user/login.php');
    exit();
}

// 1. Rechercher l'utilisateur correspondant au token (même s'il est déjà vérifié)
$stmt = $pdo->prepare("SELECT id, is_verified_email, email_token_expires_at FROM users WHERE email_verification_token = :token");
$stmt->execute([':token' => $token]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// 2. Si aucun utilisateur trouvé avec ce token → lien invalide
if (!$user) {
    $_SESSION['error'] = "Ce lien de vérification est invalide.";
    header('Location: ../front/user/login.php');
    exit();
}

// 3. Si l'email est déjà vérifié → informer l'utilisateur
if ($user['is_verified_email']) {
    $_SESSION['success'] = "Votre email est déjà vérifié. Vous pouvez vous connecter.";
    header('Location: ../front/user/login.php');
    exit();
}

// 4. Vérifier l'expiration du token
$currentDate = new DateTime();
$expirationDate = new DateTime($user['email_token_expires_at']);

if ($currentDate > $expirationDate) {
    $_SESSION['error'] = "Ce lien a expiré. Vous pouvez demander un nouveau lien de confirmation.";
    $_SESSION['resend_token_id'] = $user['id']; // utile pour un bouton "renvoyer le lien"
    header('Location: ../front/user/resendToken.php'); // tu pourras créer cette page plus tard
    exit();
}

// 5. Le token est valide et non expiré → on valide l'email
$update = $pdo->prepare("UPDATE users SET is_verified_email = 1 WHERE id = :id");
$update->execute([':id' => $user['id']]);

$_SESSION['success'] = "Votre adresse email a bien été vérifiée. Vous pouvez maintenant vous connecter.";
header('Location: ../front/user/login.php');
exit();
