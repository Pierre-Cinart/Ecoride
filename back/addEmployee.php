<?php
// Chargement des composants nécessaires
require_once './composants/autoload.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Méthode non autorisée.";
    header('Location: ../front/user/register.php');
    exit();
}

$_POST = sanitizeArray($_POST, '../front/user/register.php');

// Génération d’un pseudo unique pour l’employé
$username = 'EMP_' . date('Ymd_His') . '_' . substr(bin2hex(random_bytes(3)), 0, 6);

$firstName = $_POST['first-name'];
$lastName = $_POST['name'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$birthdate = $_POST['birthdate'] ?? null;
$gender = $_POST['gender'] ?? null;

// Vérifications basiques
if (empty($firstName) || empty($lastName) || empty($email) || empty($phone)) {
    $_SESSION['error'] = "Veuillez remplir tous les champs obligatoires.";
    header('Location: ../front/admin/addEmployee.php');
    exit();
}

// Validation date de naissance
if (!$birthdate || !strtotime($birthdate)) {
    $_SESSION['error'] = "La date de naissance est invalide ou manquante.";
    header('Location: ../front/admin/addEmployee.php');
    exit();
}

$today = new DateTime();
$dob = new DateTime($birthdate);
$age = $today->diff($dob)->y;

if ($dob > $today) {
    $_SESSION['error'] = "La date de naissance ne peut pas être dans le futur.";
    header('Location: ../front/admin/addEmployee.php');
    exit();
}

if ($age < 18) {
    $_SESSION['error'] = "L'utilisateur doit avoir au moins 18 ans.";
    header('Location: ../front/admin/addEmployee.php');
    exit();
}

// Vérification sexe
if ($gender !== 'male' && $gender !== 'female') {
    $_SESSION['error'] = "Le sexe doit être renseigné correctement.";
    header('Location: ../front/admin/addEmployee.php');
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
        header('Location: ../front/admin/addEmployee.php');
        exit();
    }
}

// Génération d’un mot de passe temporaire (remplacé plus tard)
$password = bin2hex(random_bytes(4));
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$emailToken = createToken();
$emailTokenExpiration = date('Y-m-d H:i:s', strtotime('+24 hours'));

try {
    $pdo->beginTransaction();

    // Insertion de l'employé avec rôle spécifique
    $insert = $pdo->prepare("
        INSERT INTO users (pseudo, first_name, last_name, email, password, phone_number, role, is_verified_email, email_verification_token, email_token_expires_at, birthdate, gender)
        VALUES (:username, :firstName, :lastName, :email, :password, :phone, 'employee', 0, :token, :tokenExp, :birthdate, :gender)
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
        ':birthdate' => $birthdate,
        ':gender' => $gender
    ]);

    $subject = "Bienvenue sur EcoRide – Créez votre mot de passe";
    $link = "$webAddress/back/createMDP.php?token=" . urlencode($emailToken);
    $message = "Bonjour $firstName,<br><br>Bienvenue parmi les membres du personnel d’EcoRide.<br><br>
    Pour sécuriser votre compte, veuillez cliquer sur le lien ci-dessous pour définir votre mot de passe :<br>
    <a href='$link'>$link</a><br><br>Ce lien est valable pendant 24 heures.";

    sendMail("no-reply_staff@ecoride.fr", $email, $subject, $message);

    $pdo->commit();

    $_SESSION['success'] = "$firstName $lastName a bien été ajouté au personnel. Un mail lui a été envoyé pour créer son mot de passe.";
    header('Location: ../front/admin/manage.php');
    exit();

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = "Erreur lors de l'inscription : " . $e->getMessage();
    header('Location: ../front/admin/manage.php');
    exit();
}
?>
