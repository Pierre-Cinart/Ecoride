<?php
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

// verification googleCaptcha
verifyCaptcha('login', '../front/user/login.php'); // ← action + redirection

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
        $_SESSION['email_to_verify'] = $data['email'];
        header('Location: ../front/user/resendToken.php');
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
        case 'driver': 
            // Charger les préférences du conducteur
            $stmt = $pdo->prepare("
                SELECT allows_smoking, allows_pets, note_personnelle
                FROM driver_preferences
                WHERE driver_id = :id
            ");
            $stmt->execute([':id' => $data['id']]);
            $preferences = $stmt->fetch(PDO::FETCH_ASSOC) ?: [
                'allows_smoking' => 0,
                'allows_pets' => 0,
                'note_personnelle' => ''
            ];

            // Charger les véhicules enregistrés par le conducteur
            $stmt = $pdo->prepare("SELECT id FROM vehicles WHERE user_id = :id");
            $stmt->execute([':id' => $data['id']]);
            $vehicles = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'id');

            // Calculer la note moyenne du conducteur (avis acceptés)
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

            // Ajouter les données supplémentaires au constructeur
            $user = new Driver(...array_merge($args, [$preferences, $vehicles, $averageRating]));

        break;

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
