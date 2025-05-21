<?php
// ===========================================
// Chargement des composants nécessaires
// ===========================================
require_once __DIR__ . '/composants/loadClasses.php';
require_once __DIR__ . '/composants/db_connect.php';
require_once __DIR__ . '/composants/JWT.php';
require_once __DIR__ . '/composants/checkAccess.php';
require_once __DIR__ . '/composants/sanitizeArray.php';
require_once __DIR__ . '/composants/captcha.php';
require_once __DIR__ . '/composants/antiflood.php';

session_start();

// ===========================================
// Sécurité : méthode POST obligatoire
// ===========================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Méthode non autorisée.";
    header('Location: ../front/user/login.php');
    exit();
}

// ===========================================
// Sécurité : CAPTCHA
// ===========================================
verifyCaptcha('login', '../front/user/login.php');

// ===========================================
// Sécurité : anti-flood (3 essais max par minute)
// ===========================================
if (!checkFlood('login', 3, 60, 3600)) {
    $_SESSION['error'] = "Trop de tentatives. Veuillez patienter une heure.";
    header('Location: ../front/user/login.php');
    exit();
}

// ===========================================
// Nettoyage des données POST
// ===========================================
$_POST = sanitizeArray($_POST, '../front/user/login.php');
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    $_SESSION['error'] = "Veuillez remplir tous les champs.";
    header('Location: ../front/user/login.php');
    exit();
}

try {
    // ===========================================
    // Recherche de l'utilisateur par email
    // ===========================================
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    // Vérification de l'existence et du mot de passe
    if (!$data || !password_verify($password, $data['password'])) {
        $_SESSION['error'] = "Identifiants incorrects.";
        header('Location: ../front/user/login.php');
        exit();
    }

    // Vérification email confirmé
    if (!$data['is_verified_email']) {
        $_SESSION['error'] = "Votre adresse email n’a pas été confirmée.";
        $_SESSION['email_to_verify'] = $data['email'];
        header('Location: ../front/user/resendToken.php');
        exit();
    }

    // ===========================================
    // Connexion acceptée → préparation de session
    // ===========================================
    clearFlood('login');
    $jwtToken = createToken();
    updateToken($pdo, $jwtToken, $data['id']);

    // Arguments communs à tous les types d'utilisateurs
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
        (int) $data['user_warnings'],
        $data['permit_status'],
        $data['birthdate'],
        $data['gender'],
        $data['profil_picture']
    ];

    // ===========================================
    // Création de l'objet utilisateur selon le rôle
    // ===========================================
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
            // 1. Préférences du conducteur
            $stmt = $pdo->prepare("SELECT allows_smoking, allows_pets, note_personnelle FROM driver_preferences WHERE driver_id = :id");
            $stmt->execute([':id' => $data['id']]);
            $preferences = $stmt->fetch(PDO::FETCH_ASSOC) ?: [
                'allows_smoking' => 0,
                'allows_pets' => 0,
                'note_personnelle' => ''
            ];

            // 2. Liste des véhicules du conducteur
            $stmt = $pdo->prepare("SELECT id FROM vehicles WHERE user_id = :id");
            $stmt->execute([':id' => $data['id']]);
            $vehicles = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'id');

            // 3. Calcul de la note moyenne reçue sur ses trajets
            $stmt = $pdo->prepare("
                SELECT AVG(rating) AS average
                FROM ratings
                WHERE trip_id IN (
                    SELECT id FROM trips WHERE driver_id = :id
                ) AND status = 'accepted'
            ");
            $stmt->execute([':id' => $data['id']]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $averageRating = isset($result['average']) ? (float)$result['average'] : 0.0;

            // 4. Avertissements conducteur
            $driverWarnings = isset($data['driver_warnings']) ? (int)$data['driver_warnings'] : 0;

            // 5. Création de l'objet Driver
            $user = new Driver(...array_merge($args, [
                $data['permit_picture'],  // image permis
                $preferences,             // préférences conducteur
                $vehicles,                // ID des véhicules
                $averageRating,           // note moyenne (float)
                $driverWarnings           // avertissements
            ]));
            break;

        default:
            throw new Exception("Rôle inconnu : " . $data['role']);
    }

    // ===========================================
    // Enregistrement de l'utilisateur en session
    // ===========================================
    updateToken($pdo, $jwtToken, $user->getId());
    $_SESSION['user'] = $user;
    $_SESSION['jwt'] = $jwtToken;
    if ($user instanceof Employee || $user instanceof Admin){
        $_SESSION['success'] = "Connexion réussie. Bienvenue " . $user->getFullName() . " !";
    } else {    $_SESSION['success'] = "Connexion réussie. Bienvenue " . $user->getPseudo() . " !"; }
    header('Location: ../front/user/home.php');
    exit();

} catch (Exception $e) {
    $_SESSION['error'] = "Erreur interne lors de la connexion.";
    header('Location: ../front/user/login.php');
    exit();
}
?>
