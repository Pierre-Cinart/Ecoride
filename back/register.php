<?php
session_start();
require_once './composants/db_connect.php'; //  connexion à ta BDD 
require_once './composants/sanitizeArray.php'; //  pour échapper les données


// Vérifie que le formulaire a bien été soumis en POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Méthode non autorisée.";
    header('Location: ../user/register.php');
    exit();
}

// 1. Sécurisation des entrées 
$_POST = sanitizeArray($_POST ,'../front/user/register.php');

// récupération des données 
$pseudo = $_POST['pseudo'];
$firstName = $_POST['first-name'];
$lastName = $_POST['name'];
$email = $_POST['email'];
$confirmEmail = $_POST['confirm-email'];
$phone = $_POST['phone'];
$password = $_POST['password'];
$confirmPassword = $_POST['confirm-password'];
$isDriver = isset($_POST['is-driver']) ? 1 : 0;

// Vérifications basiques
if (empty($pseudo) || empty($firstName) || empty($lastName) || empty($email) || empty($phone) || empty($password)) {
    $_SESSION['error'] = "Veuillez remplir tous les champs obligatoires.";
    header('Location: ../front/user/register.php');
    exit();
}

// Emails identiques
if ($email !== $confirmEmail) {
    $_SESSION['error'] = "Les emails ne correspondent pas.";
    header('Location: ../front/user/register.php');
    exit();
}

// Passwords identiques
if ($password !== $confirmPassword) {
    $_SESSION['error'] = "Les mots de passe ne correspondent pas.";
    header('Location: ../front/user/register.php');
    exit();
}

// 2. Vérifier unicité du pseudo, email, téléphone
$stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE pseudo = :pseudo OR email = :email OR phone_number = :phone");
$stmt->execute([
    ':pseudo' => $pseudo,
    ':email' => $email,
    ':phone' => $phone
]);
if ($stmt->fetchColumn() > 0) {
    $_SESSION['error'] = "Pseudo, email ou numéro déjà utilisé.";
    header('Location: ../front/user/register.php');
    exit();
}

// 3. Si chauffeur => vérifier upload du permis
if ($isDriver) {
    if (!isset($_FILES['permit']) || $_FILES['permit']['error'] !== 0) {
        $_SESSION['error'] = "Le permis de conduire est obligatoire pour être chauffeur.";
        header('Location: ../front/user/register.php');
        exit();
    }

    // Vérification du format JPEG
    $permitType = mime_content_type($_FILES['permit']['tmp_name']);
    if ($permitType !== 'image/jpeg') {
        $_SESSION['error'] = "Le permis doit être au format JPEG.";
        header('Location: ../front/user/register.php');
        exit();
    }
}

// 4. Hachage du mot de passe
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// 5. Création du compte utilisateur
try {
    $pdo->beginTransaction();

    $insertUser = $pdo->prepare("INSERT INTO users (pseudo, first_name, last_name, email, password, phone_number, role) VALUES (:pseudo, :first_name, :last_name, :email, :password, :phone_number, :role)");
    $insertUser->execute([
        ':pseudo' => $pseudo,
        ':first_name' => $firstName,
        ':last_name' => $lastName,
        ':email' => $email,
        ':password' => $hashedPassword,
        ':phone_number' => $phone,
        ':role' => $isDriver ? 'driver' : 'user'
    ]);

    $userId = $pdo->lastInsertId();

    // 6. Gestion de l'upload du permis
    if ($isDriver) {
        $userFolder = '../back/uploads/' . $pseudo;
        if (!is_dir($userFolder)) {
            mkdir($userFolder, 0777, true); // Création du dossier
            // Protection du dossier via .htaccess
            file_put_contents($userFolder . '/.htaccess', "Order Deny,Allow\nDeny from all");
        }

        $fileName = uniqid('permit_') . '.jpg';
        $filePath = $userFolder . '/' . $fileName;

        if (!move_uploaded_file($_FILES['permit']['tmp_name'], $filePath)) {
            throw new Exception("Erreur lors du téléchargement du permis.");
        }

        // Insertion en base dans documents
        $insertDoc = $pdo->prepare("INSERT INTO documents (user_id, type, file_path) VALUES (:user_id, :type, :file_path)");
        $insertDoc->execute([
            ':user_id' => $userId,
            ':type' => 'permit',
            ':file_path' => $filePath
        ]);
    }

    $pdo->commit();
    $_SESSION['success'] = "Inscription réussie, vous pouvez vous connecter !";
    header('Location: ../front/user/login.php');
    exit();

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = "Erreur lors de l'inscription : " . $e->getMessage();
    header('Location: ../front/user/register.php');
    exit();
}
?>
