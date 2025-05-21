<?php
require_once './composants/autoload.php'; // charge les classes, PDO, checkToken, etc.

checkAccess(['SimpleUser', 'Driver']); // sécurise l'accès

// Vérifie que la requête est bien en POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Méthode non autorisée.";
    header('Location: ../front/user/addCredits.php');
    exit();
}

// Vérifie le reCAPTCHA pour éviter les abus
verifyCaptcha('addCredits', '../front/user/addCredits.php');

// Nettoyage des données
$_POST = sanitizeArray($_POST, '../front/user/addCredits.php');

// Récupère les données du formulaire
$creditAmount = getPostInt('creditAmount'); // champ "creditAmount" (et non "trip_id")
$paymentMethod = $_POST['fakePayment'] ?? 'cb'; // méthode de paiement simulée

if ($creditAmount <= 0) {
    $_SESSION['error'] = "Montant de crédits invalide.";
    header('Location: ../front/user/addCredits.php');
    exit();
}

// Récupération de l'utilisateur connecté
$user = $_SESSION['user'] ?? null;

if (!$user || !($user instanceof SimpleUser || $user instanceof Driver)) {
    $_SESSION['error'] = "Accès non autorisé.";
    header('Location: ../front/user/addCredits.php');
    exit();
}

try {
    // Commence une transaction SQL
    $pdo->beginTransaction();

    // Ajoute les crédits à l'utilisateur
    $user->updateCredits($pdo, $creditAmount);

    // Ajoute une ligne dans la table transactions
    $stmt = $pdo->prepare("
        INSERT INTO transactions (user_id, type, credits, description)
        VALUES (:user_id, 'add', :credits, :description)
    ");
    $stmt->execute([
        'user_id' => $user->getId(),
        'credits' => $creditAmount,
        'description' => "Achat simulé via $paymentMethod"
    ]);

    // Commit de la transaction
    $pdo->commit();

    $_SESSION['success'] = "$creditAmount crédits ont été ajoutés à votre compte.";
    header('Location: ../front/user/home.php');
    exit();

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = "Une erreur est survenue : " . $e->getMessage();
    header('Location: ../front/user/addCredits.php');
    exit();
}
