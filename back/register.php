<?php
// Chargement des composants nécessaires
require_once __DIR__ . '/composants/loadClasses.php';
require_once __DIR__ . '/composants/db_connect.php';
require_once __DIR__ . '/composants/JWT.php';
require_once __DIR__ . '/composants/checkAccess.php';
require_once __DIR__ . '/composants/sanitizeArray.php';
require_once __DIR__ . '/composants/captcha.php';
require_once __DIR__ . '/composants/antiFlood.php';
require_once __DIR__ . '/composants/phpMailer/src/sendMail.php';
require_once __DIR__ . '/composants/uploader.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Méthode non autorisée.";
    header('Location: ../front/user/register.php');
    exit();
}

verifyCaptcha('register', '../front/user/register.php');

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
$birthdate = $_POST['birthdate'] ?? null;
$gender = $_POST['gender'] ?? null;

// Vérifications basiques
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

// Validation date de naissance
if (!$birthdate || !strtotime($birthdate)) {
    $_SESSION['error'] = "La date de naissance est invalide ou manquante.";
    header('Location: ../front/user/register.php');
    exit();
}

$today = new DateTime();
$dob = new DateTime($birthdate);
$age = $today->diff($dob)->y;

if ($dob > $today) {
    $_SESSION['error'] = "La date de naissance ne peut pas être dans le futur.";
    header('Location: ../front/user/register.php');
    exit();
}

if ($age < 18) {
    $_SESSION['error'] = "Vous devez avoir au moins 18 ans pour vous inscrire.";
    header('Location: ../front/user/register.php');
    exit();
}

// Vérification sexe
if ($gender !== 'male' && $gender !== 'female') {
    $_SESSION['error'] = "Le sexe doit être renseigné correctement.";
    header('Location: ../front/user/register.php');
    exit();
}

// Vérification unicité
$stmt = $pdo->prepare("SELECT pseudo, email, phone_number FROM users WHERE pseudo = :pseudo OR email = :email OR phone_number = :phone");
$stmt->execute([
    ':pseudo' => $username,
    ':email' => $email,
    ':phone' => $phone
]);
$existing = $stmt->fetch(PDO::FETCH_ASSOC);

$errors = [];
if ($existing) {
    if ($existing['pseudo'] === $username) $errors[] = "Ce pseudo est déjà utilisé.";
    if ($existing['email'] === $email) $errors[] = "Cette adresse email est déjà utilisée.";
    if ($existing['phone_number'] === $phone) $errors[] = "Ce numéro de téléphone est déjà utilisé.";

    if (!empty($errors)) {
        $_SESSION['error'] = implode('<br>', $errors);
        header('Location: ../front/user/register.php');
        exit();
    }
}

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$emailToken = createToken();
$emailTokenExpiration = date('Y-m-d H:i:s', strtotime('+24 hours'));
$permitStatus = $isDriver ? 'pending' : 'waiting';

try {
    $pdo->beginTransaction();

    // Création utilisateur avec role = user et status permis selon profil
    $insert = $pdo->prepare("
        INSERT INTO users (pseudo, first_name, last_name, email, password, phone_number, role, is_verified_email, email_verification_token, email_token_expires_at, permit_status, birthdate, gender)
        VALUES (:username, :firstName, :lastName, :email, :password, :phone, 'user', 0, :token, :tokenExp, :permitStatus, :birthdate, :gender)
    ");
    $insert->execute([
        ':username' => $username,
        ':firstName' => $firstName,
        ':lastName' => $lastName,
        ':email' => $email,
        ':password' => $hashedPassword,
        ':phone' => $phone,
        ':token' => $emailToken,
        ':tokenExp' => $emailTokenExpiration,
        ':permitStatus' => $permitStatus,
        ':birthdate' => $birthdate,
        ':gender' => $gender
    ]);

    $userId = $pdo->lastInsertId();

    // Si conducteur : upload du permis
    if ($isDriver) {
        uploadImage(
            $pdo,
            $userId,
            $_FILES['permit'],
            'permit',
            '../front/user/register.php'
        );
    }

    $subject = "Confirmation de votre inscription - EcoRide";
    $link = "$webAddress/back/verify_email.php?token=" . urlencode($emailToken);
    $message = "Bonjour $firstName,<br><br>Merci pour votre inscription sur EcoRide.<br><br>
    Veuillez cliquer sur le lien suivant pour valider votre adresse e-mail :<br>
    <a href='$link'>$link</a><br><br>Ce lien est valable pendant 24 heures.";

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
