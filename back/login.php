<?php
// Import des dépendances
require_once './composants/db_connect.php';
require_once './composants/sanitizeArray.php';
require_once './composants/JWT.php';
require_once './classes/User.php';
require_once './classes/SimpleUser.php';
require_once './classes/Driver.php';
require_once './classes/Admin.php';
require_once './classes/Employee.php';

//demarage de session
session_start();

// Vérifie que le formulaire a été soumis en POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Méthode non autorisée.";
    header('Location: ../front/user/login.php');
    exit();
}

// 1. Sécurisation des données reçues
$_POST = sanitizeArray($_POST, '../front/user/login.php');
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

// Vérifie champs vides
if (empty($email) || empty($password)) {
    $_SESSION['error'] = "Veuillez remplir tous les champs.";
    header('Location: ../front/user/login.php');
    exit();
}

try {
    // 2. Récupération de l'utilisateur en base
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    // Utilisateur inconnu ou mauvais mot de passe
    if (!$data || !password_verify($password, $data['password'])) {
        $_SESSION['error'] = "Identifiants incorrects.";
        header('Location: ../front/user/login.php');
        exit();
    }

    // 3. Vérifie si l'email est validé
    if (!$data['is_verified_email']) {
        $_SESSION['error'] = "Votre adresse email n’a pas été confirmée. Vérifiez votre boîte mail.";
        $_SESSION['resend_link'] = true;
        $_SESSION['email_to_verify'] = $data['email'];
        header('Location: ../front/user/login.php');
        exit();
    }

    // 4. Création du token JWT valable 2h
    $jwtToken = createToken();
    $update = $pdo->prepare("UPDATE users SET jwt_token = :token, jwt_token_time = CURRENT_TIMESTAMP WHERE id = :id");
    $update->execute([
        ':token' => $jwtToken,
        ':id' => $data['id']
    ]);

    // 5. Instanciation de la bonne classe utilisateur
 // Définition des arguments communs
$args = [
    $data['id'],
    $data['pseudo'],
    $data['first_name'],
    $data['last_name'],
    $data['email'],
    $data['phone_number'],
    $data['role'],
    $data['credits']
];

switch ($data['role']) {
    case 'user' : 
        $user = new SimpleUser(...$args);
        break;
    case 'admin':
        $user = new Admin(...$args);
        break;
    case 'employee':
        $user = new Employee(...$args);
        break;
    case 'driver':
        $user = new Driver(...$args);
        break;
    default:
        $user = new User(...$args);
        break;
}

    //  6. Stockage dans la session
    $_SESSION['user'] = $user;         // Objet complet
    $_SESSION['jwt'] = $jwtToken;      // Token à part
    $_SESSION['success'] = "Connexion réussie. Bienvenue " . $user->getPseudo() . " !";
    // reservation offline et droits de reservation
    if (!($user instanceof SimpleUser || $user instanceof Driver)) {
    unset($_SESSION['tripPending']); 
    }
    header('Location: ../front/user/home.php');
    exit();

} catch (Exception $e) {
    $_SESSION['error'] = "Erreur interne lors de la connexion.";
    header('Location: ../front/user/login.php');
    exit();
}
?>
