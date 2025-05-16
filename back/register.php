<?php
// Chargement des composants nécessaires
require_once __DIR__ . '/composants/loadClasses.php'; // classes
require_once __DIR__ . '/composants/db_connect.php'; // connexion bdd
require_once __DIR__ . '/composants/JWT.php'; // JWT
require_once __DIR__ . '/composants/checkAccess.php'; // contrôle d'accès
require_once __DIR__ . '/composants/sanitizeArray.php'; // nettoyage des données
require_once __DIR__ . '/composants/captcha.php'; // Google Recaptcha
require_once __DIR__ . '/composants/antiflood.php';  // protection brute force 
require_once __DIR__ . '/composants/phpMailer/src/sendMail.php'; // envoie de mails


session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Méthode non autorisée.";
    header('Location: ../front/user/register.php');
    exit();
}

$_POST = sanitizeArray($_POST, '../front/user/register.php');

$username = $_POST['pseudo'];
$firstName = $_POST['first-name'];
$lastName = $_POST['name'];
$email = $_POST['email'];
$confirmEmail = $_POST['confirm-email'];
$phone = $_POST['phone'];
$password = $_POST['password'];
$confirmPassword = $_POST['confirm-password'];
$isDriver = isset($_POST['is-driver']) ? 1 : 0;

if (empty($username) || empty($firstName) || empty($lastName) || empty($email) || empty($phone) || empty($password)) {
    $_SESSION['error'] = "Veuillez remplir tous les champs obligatoires.";
    header('Location: ../front/user/register.php');
    exit();
}

if ($email !== $confirmEmail) {
    $_SESSION['error'] = "Les emails ne correspondent pas.";
    header('Location: ../front/user/register.php');
    exit();
}

if ($password !== $confirmPassword) {
    $_SESSION['error'] = "Les mots de passe ne correspondent pas.";
    header('Location: ../front/user/register.php');
    exit();
}

$stmt = $pdo->prepare("SELECT pseudo, email, phone_number FROM users WHERE pseudo = :pseudo OR email = :email OR phone_number = :phone");
$stmt->execute([
    ':pseudo' => $username,
    ':email' => $email,
    ':phone' => $phone
]);
$existing = $stmt->fetch(PDO::FETCH_ASSOC);

$errors = [];

if ($existing) {
    if ($existing['pseudo'] === $username) {
        $errors[] = "Ce pseudo est déjà utilisé.";
    }
    if ($existing['email'] === $email) {
        $errors[] = "Cette adresse email est déjà utilisée.";
    }
    if ($existing['phone_number'] === $phone) {
        $errors[] = "Ce numéro de téléphone est déjà utilisé.";
    }
    if (!empty($errors)) {
        $_SESSION['error'] = implode('<br>', $errors);
        header('Location: ../front/user/register.php');
        exit();
    }
}

if ($isDriver) {
    if (!isset($_FILES['permit']) || $_FILES['permit']['error'] !== 0) {
        $_SESSION['error'] = "Le permis de conduire est obligatoire pour être chauffeur.";
        header('Location: ../front/user/register.php');
        exit();
    }

    $permitType = mime_content_type($_FILES['permit']['tmp_name']);
    if ($permitType !== 'image/jpeg') {
        $_SESSION['error'] = "Le permis doit être au format JPEG.";
        header('Location: ../front/user/register.php');
        exit();
    }
}

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$emailToken = createToken();
$emailTokenExpiration = date('Y-m-d H:i:s', strtotime('+24 hours'));

try {
    $pdo->beginTransaction();

    $insert = $pdo->prepare("INSERT INTO users (pseudo, first_name, last_name, email, password, phone_number, role, is_verified_email, email_verification_token, email_token_expires_at)
    VALUES (:username, :firstName, :lastName, :email, :password, :phone, :role, 0, :token, :tokenExp)");
    $insert->execute([
        ':username' => $username,
        ':firstName' => $firstName,
        ':lastName' => $lastName,
        ':email' => $email,
        ':password' => $hashedPassword,
        ':phone' => $phone,
        ':role' => $isDriver ? 'driver' : 'user',
        ':token' => $emailToken,
        ':tokenExp' => $emailTokenExpiration
    ]);

    $userId = $pdo->lastInsertId();

    if ($isDriver) {
        $userFolder = '../back/uploads/' . $username;
        if (!is_dir($userFolder)) {
            mkdir($userFolder, 0777, true);
            file_put_contents($userFolder . '/.htaccess', "Order Deny,Allow\nDeny from all");
        }

        $fileName = uniqid('permit_') . '.jpg';
        $filePath = $userFolder . '/' . $fileName;

        if (!move_uploaded_file($_FILES['permit']['tmp_name'], $filePath)) {
            throw new Exception("Erreur lors du téléchargement du permis.");
        }

        $insertDoc = $pdo->prepare("INSERT INTO documents (user_id, type, file_path) VALUES (:userId, :type, :filePath)");
        $insertDoc->execute([
            ':userId' => $userId,
            ':type' => 'permit',
            ':filePath' => $filePath
        ]);
    }

    $subject = "Confirmation de votre inscription - EcoRide";
    $link = "$webAddress/back/verify_email.php?token=" . urlencode($emailToken);
    $message = "Bonjour $firstName,<br><br>Merci pour votre inscription sur EcoRide.<br><br>Veuillez cliquer sur le lien suivant pour valider votre adresse e-mail :<br><a href='$link'>$link</a><br><br>Ce lien est valable pendant 24 heures.";

    sendMail("no-reply@ecoride.fr", $email, $subject, $message);

    $pdo->commit();

    $_SESSION['success'] = "Nous vous avons envoyé un mail de vérification. Veuillez consulter votre messagerie et cliquer sur le lien de validation.";
    header('Location: ../front/user/login.php');
    exit();

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = "Erreur lors de l'inscription : " . $e->getMessage();
    header('Location: ../front/user/register.php');
    exit();
}
?>
