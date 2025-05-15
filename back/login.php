<?php
// Chargement des composants nécessaires
// Chargement des composants nécessaires
require_once __DIR__ .'../composants/loadClasses.php'; // classes
require_once __DIR__ . '../composants/db_connect.php'; // connection bdd
require_once __DIR__ . '../composants/JWT.php'; //jwt
require_once __DIR__ . '../composants/checkAccess.php'; //control d accés
require_once __DIR__ . '../composants/sanitizeArray.php'; // nettoyage des données
require_once __DIR__ . '../composants/captcha.php'; // googleRecaptcha
require_once __DIR__ . '../composants/antiflood.php';  // protection brute force 
session_start();

// Vérifie que le formulaire est soumis par POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Méthode non autorisée.";
    header('Location: ../front/user/login.php');
    exit();
}

// Vérification Google Captcha
verifyCaptcha('login', '../front/user/login.php');

// Anti-flood : 3 tentatives max en 60 secondes, sinon blocage pendant 1h
if (!checkFlood('login', 3, 60, 3600)) {
    $_SESSION['error'] = "Trop de tentatives. Veuillez patienter une heure.";
    header('Location: ../front/user/login.php');
    exit();
}

// Nettoyage des données entrantes
$_POST = sanitizeArray($_POST, '../front/user/login.php');
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

// Vérifie que tous les champs sont remplis
if (empty($email) || empty($password)) {
    $_SESSION['error'] = "Veuillez remplir tous les champs.";
    header('Location: ../front/user/login.php');
    exit();
}

try {
    // Récupère l’utilisateur
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    // Vérifie l’existence et le mot de passe
    if (!$data || !password_verify($password, $data['password'])) {
        $_SESSION['error'] = "Identifiants incorrects.";
        header('Location: ../front/user/login.php');
        exit();
    }

    // Vérifie si l'email est confirmé
    if (!$data['is_verified_email']) {
        $_SESSION['error'] = "Votre adresse email n’a pas été confirmée.";
        $_SESSION['email_to_verify'] = $data['email'];
        header('Location: ../front/user/resendToken.php');
        exit();
    }

    // Connexion validée : reset anti-flood
    clearFlood('login');

    // Création et enregistrement du token JWT
    $jwtToken = createToken();
    updateToken($pdo, $jwtToken, $data['id']);

    // Préparation des arguments communs pour toutes les classes
    $args = [
        $data['id'],
        $data['pseudo'],
        $data['first_name'],
        $data['last_name'],
        $data['email'],
        $data['phone_number'],
        $data['role'],
        (int) $data['credits'],
        $data['status'],
        (int) $data['user_warnings']
    ];

    // Création de l’objet utilisateur selon le rôle
    switch ($data['role']) {
        case 'user':
            $user = new SimpleUser(...$args);
            break;

        case 'admin':
            $user = new Admin(...$args);
            break;

        case 'employee':
            $user = new Employee(...$args);
            break;

        case 'driver':
            // Préférences
            $stmt = $pdo->prepare("SELECT allows_smoking, allows_pets, note_personnelle FROM driver_preferences WHERE driver_id = :id");
            $stmt->execute([':id' => $data['id']]);
            $preferences = $stmt->fetch(PDO::FETCH_ASSOC) ?: [
                'allows_smoking' => 0,
                'allows_pets' => 0,
                'note_personnelle' => ''
            ];

            // Véhicules
            $stmt = $pdo->prepare("SELECT id FROM vehicles WHERE user_id = :id");
            $stmt->execute([':id' => $data['id']]);
            $vehicles = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'id');

            // Moyenne des notes
            $stmt = $pdo->prepare("
                SELECT AVG(rating) AS average
                FROM ratings
                WHERE trip_id IN (
                    SELECT id FROM trips WHERE driver_id = :id
                ) AND status = 'accepted'
            ");
            $stmt->execute([':id' => $data['id']]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $averageRating = isset($result['average']) ? (float) $result['average'] : 0.0;

            // Avertissements conducteur
            $driverWarnings = isset($data['driver_warnings']) ? (int) $data['driver_warnings'] : 0;

            // Création de l'objet Driver
            $user = new Driver(...array_merge($args, [
                $preferences,
                $vehicles,
                $averageRating,
                $driverWarnings
            ]));
            break;

        default:
            throw new Exception("Rôle inconnu : " . $data['role']);
    }

    // Injection de l’objet utilisateur et du token en session
    $_SESSION['user'] = $user;
    $_SESSION['jwt'] = $jwtToken;
    $_SESSION['success'] = "Connexion réussie. Bienvenue " . $user->getPseudo() . " !";

    header('Location: ../front/user/home.php');
    exit();

} catch (Exception $e) {
    $_SESSION['error'] = "Erreur interne lors de la connexion.";
    header('Location: ../front/user/login.php');
    exit();
}
?>
