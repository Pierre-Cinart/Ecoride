<?php
// Chargement des composants nécessaires
require_once './composants/db_connect.php';        // Connexion à la base de données
require_once './composants/sanitizeArray.php';     // Nettoyage des entrées utilisateur
require_once './composants/antiflood.php';         // Protection anti-flood
require_once './composants/loadClasses.php';          // Chargement des classes 
require_once './composants/JWT.php';                 // creation du jwt

// Vérifie que le formulaire est soumis par POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Méthode non autorisée.";
    header('Location: ../front/user/login.php');
    exit();
}

// Anti-flood : 3 tentatives max en 60 secondes, sinon blocage pendant 1h (3600s)
if (!checkFlood('login', 3, 60, 3600)) {
    $_SESSION['error'] = "Trop de tentatives. Veuillez patienter une heure.";
    header('Location: ../front/user/login.php');
    exit();
}

// 1. Nettoyage des données entrantes
$_POST = sanitizeArray($_POST, '../front/user/login.php');
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

// 2. Vérifie que tous les champs sont remplis
if (empty($email) || empty($password)) {
    $_SESSION['error'] = "Veuillez remplir tous les champs.";
    header('Location: ../front/user/login.php');
    exit();
}

try {
    // 3. Récupère les infos de l’utilisateur depuis la base
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    // 4. Vérifie que l'utilisateur existe et que le mot de passe est correct
    if (!$data || !password_verify($password, $data['password'])) {
        $_SESSION['error'] = "Identifiants incorrects.";
        header('Location: ../front/user/login.php');
        exit();
    }

    // 5. Vérifie que l'adresse mail est confirmée
    if (!$data['is_verified_email']) {
        $_SESSION['error'] = "Votre adresse email n’a pas été confirmée.";
        $_SESSION['resend_link'] = true;
        $_SESSION['email_to_verify'] = $data['email'];
        header('Location: ../front/user/login.php');
        exit();
    }

    // 6. Connexion réussie → suppression des traces antiflood
    clearFlood('login');

    // 7. Création d’un token JWT de session
    $jwtToken = createToken();
    updateToken( $pdo,  $jwtToken,  $data['id']);

    // 8. Instanciation de la classe correspondant à son rôle
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
        case 'user':    $user = new SimpleUser(...$args); break;
        case 'admin':   $user = new Admin(...$args); break;
        case 'employee':$user = new Employee(...$args); break;
        case 'driver':  $user = new Driver(...$args); break;
        default:        $user = new User(...$args); break;
    }

    // 9. Enregistre en session et redirige
    $_SESSION['user'] = $user;
    $_SESSION['jwt'] = $jwtToken;
    $_SESSION['success'] = "Connexion réussie. Bienvenue " . $user->getPseudo() . " !";

    // Nettoyage éventuel des réservations hors session
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
