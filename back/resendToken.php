<?php
session_start();
require_once './composants/db_connect.php';
require_once './composants/JWT.php'; // pour créer le token
require_once './composants/phpMailer/src/sendMail.php'; // pour envoyer le mail

// 1. Vérifie si la requête est bien une requête POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Méthode non autorisée.";
    header('Location: ../front/user/resendEmail.php');
    exit();
}

// 2. Vérifie si l’email a bien été soumis
$email = trim($_POST['email'] ?? '');

if (empty($email)) {
    $_SESSION['error'] = "Veuillez renseigner une adresse email.";
    header('Location: ../front/user/resendEmail.php');
    exit();
}

// 3. Recherche de l’utilisateur correspondant à cet email
$stmt = $pdo->prepare("SELECT id, pseudo, first_name, is_verified_email FROM users WHERE email = :email LIMIT 1");
$stmt->execute([':email' => $email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// 4. Si aucun utilisateur trouvé
if (!$user) {
    $_SESSION['error'] = "Aucun compte enregistré avec cette adresse email. Veuillez créer un compte.";
    header('Location: ../front/user/register.php');
    exit();
}

// 5. Si l’email est déjà vérifié
if ($user['is_verified_email']) {
    $_SESSION['error'] = "Cet email est déjà validé. Vous pouvez vous connecter.";
    header('Location: ../front/user/login.php');
    exit();
}

// 6. Génération du nouveau token et date d’expiration
$newToken = createToken();
$expiration = date('Y-m-d H:i:s', strtotime('+24 hours'));

// 7. Mise à jour en base de données
$update = $pdo->prepare("UPDATE users SET email_verification_token = :token, email_token_expires_at = :expires WHERE id = :id");
$update->execute([
    ':token' => $newToken,
    ':expires' => $expiration,
    ':id' => $user['id']
]);

// 8. Préparation du message email
$firstName = $user['first_name'];
$subject = "Confirmation de votre inscription - EcoRide";

// ⚠️ Pense à adapter ce lien pour mise en ligne
$link = "http://localhost/ecoride/back/verify_email.php?token=" . urlencode($newToken);

$message = "
    Bonjour <strong>$firstName</strong>,<br><br>
    Vous nous avez demandé un nouveau lien de confirmation.<br><br>
    Veuillez cliquer sur le lien suivant pour valider votre adresse e-mail :<br>
    <a href='$link'>$link</a><br><br>
    Ce lien est valable pendant 24 heures.";

// 9. Envoi du mail
sendMail($email, $firstName, $subject, $message);

// 10. Message de succès
$_SESSION['success'] = "Nous vous avons envoyé un mail de vérification. Veuillez consulter votre messagerie et cliquer sur le lien de validation.";
header('Location: ../front/user/login.php');
exit();
