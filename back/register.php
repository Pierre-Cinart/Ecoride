<?php
// Chargement des composants nécessaires
require_once __DIR__ .'../composants/loadClasses.php'; // classes
require_once __DIR__ . '../composants/db_connect.php'; // connection bdd
require_once __DIR__ . '../composants/JWT.php'; //jwt
require_once __DIR__ . '../composants/checkAccess.php'; //control d accés
require_once __DIR__ . '../composants/sanitizeArray.php'; // nettoyage des données
require_once __DIR__ . '../composants/captcha.php'; // googleRecaptcha
require_once __DIR__ . '../composants/antiflood.php';  // protection brute force 
require_once __DIR__ . '../composants/phpMailer/src/sendMail.php'; // Pour envoyer les mails avec PHPMailer

session_start();

// Vérifie que le formulaire a bien été soumis en POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Méthode non autorisée.";
    header('Location: ../front/user/register.php');
    exit();
}

// Nettoyage des données postées (et sécurité contre les tableaux malicieux)
$_POST = sanitizeArray($_POST, '../front/user/register.php');

// Récupération des champs du formulaire
$username = $_POST['pseudo'];
$firstName = $_POST['first-name'];
$lastName = $_POST['name'];
$email = $_POST['email'];
$confirmEmail = $_POST['confirm-email'];
$phone = $_POST['phone'];
$password = $_POST['password'];
$confirmPassword = $_POST['confirm-password'];
$isDriver = isset($_POST['is-driver']) ? 1 : 0;

// Vérification que tous les champs obligatoires sont remplis
if (empty($username) || empty($firstName) || empty($lastName) || empty($email) || empty($phone) || empty($password)) {
    $_SESSION['error'] = "Veuillez remplir tous les champs obligatoires.";
    header('Location: ../front/user/register.php');
    exit();
}

// Vérification que les emails correspondent
if ($email !== $confirmEmail) {
    $_SESSION['error'] = "Les emails ne correspondent pas.";
    header('Location: ../front/user/register.php');
    exit();
}

// Vérification que les mots de passe correspondent
if ($password !== $confirmPassword) {
    $_SESSION['error'] = "Les mots de passe ne correspondent pas.";
    header('Location: ../front/user/register.php');
    exit();
}

// Vérifie que l'email, le pseudo ou le numéro de téléphone ne sont pas déjà utilisés
$stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE pseudo = :pseudo OR email = :email OR phone_number = :phone");
$stmt->execute([
    ':pseudo' => $username,
    ':email' => $email,
    ':phone' => $phone
]);
if ($stmt->fetchColumn() > 0) {
    $_SESSION['error'] = "Pseudo, email ou numéro déjà utilisé.";
    header('Location: ../front/user/register.php');
    exit();
}

// Si l'utilisateur est chauffeur, on vérifie qu'un permis est bien envoyé
if ($isDriver) {
    if (!isset($_FILES['permit']) || $_FILES['permit']['error'] !== 0) {
        $_SESSION['error'] = "Le permis de conduire est obligatoire pour être chauffeur.";
        header('Location: ../front/user/register.php');
        exit();
    }

    // On vérifie le format du fichier (doit être JPEG)
    $permitType = mime_content_type($_FILES['permit']['tmp_name']);
    if ($permitType !== 'image/jpeg') {
        $_SESSION['error'] = "Le permis doit être au format JPEG.";
        header('Location: ../front/user/register.php');
        exit();
    }
}

// On hash le mot de passe pour le stocker de façon sécurisée
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// On génère le token de vérification d'email
$emailToken = createToken();
$emailTokenExpiration = date('Y-m-d H:i:s', strtotime('+24 hours'));

try {
    // On démarre une transaction pour garantir que tout s'insère proprement
    $pdo->beginTransaction();

    // Insertion de l'utilisateur dans la base
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

    // Si l'utilisateur est chauffeur, on traite l'upload du permis
    if ($isDriver) {
        $userFolder = '../back/uploads/' . $username;
        if (!is_dir($userFolder)) {
            mkdir($userFolder, 0777, true);
            // On protège le dossier contre l'accès direct
            file_put_contents($userFolder . '/.htaccess', "Order Deny,Allow\nDeny from all");
        }

        $fileName = uniqid('permit_') . '.jpg';
        $filePath = $userFolder . '/' . $fileName;

        if (!move_uploaded_file($_FILES['permit']['tmp_name'], $filePath)) {
            throw new Exception("Erreur lors du téléchargement du permis.");
        }

        // Insertion du document dans la table documents
        $insertDoc = $pdo->prepare("INSERT INTO documents (user_id, type, file_path) VALUES (:userId, :type, :filePath)");
        $insertDoc->execute([
            ':userId' => $userId,
            ':type' => 'permit',
            ':filePath' => $filePath
        ]);
    }

    // Envoi du mail de confirmation avec le lien contenant le token
    $subject = "Confirmation de votre inscription - EcoRide";
    // adresse du lien a changer pour local non local
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