<?php
session_start();
require_once './composants/db_connect.php'; //  connexion à ta BDD 
require_once './composants/sanitizeArray.php'; //  pour échapper les données


// Vérifie que le formulaire a bien été soumis en POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Méthode non autorisée.";
    header('Location: ../user/login.php');
    exit();
}

// 1.Sécurisation des entrées 
$_POST = sanitizeArray($_POST ,'../front/user/register.php');

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    $_SESSION['error'] = "Veuillez remplir tous les champs.";
    header('Location: ../front/user/login.php');
    exit();
}

// 2. Vérification en base
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $_SESSION['error'] = "Identifiants incorrects.";
        header('Location: ../front/user/login.php');
        exit();
    }

    // 3. Vérification du mot de passe
    if (!password_verify($password, $user['password'])) {
        $_SESSION['error'] = "Identifiants incorrects.";
        header('Location: ../front/user/login.php');
        exit();
    }

    // 4. Connexion réussie => création des variables de session
    $_SESSION['id'] = $user['id'];
    $_SESSION['pseudo'] = $user['pseudo'];
    $_SESSION['typeOfUser'] = $user['role'];
    $_SESSION['credits'] = $user['credits'];
    $_SESSION['is_verified'] = $user['is_verified'];

    $_SESSION['success'] = "Connexion réussie, bienvenue {$user['pseudo']} !";

    header('Location: ../front/user/home.php');
          
} catch (Exception $e) {
    $_SESSION['error'] = "Erreur lors de la connexion.";
    header('Location: ../front/user/login.php');
    exit();
}

?>
