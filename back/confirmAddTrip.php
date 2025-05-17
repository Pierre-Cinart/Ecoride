<?php
// ======================================================
// confirmAddTrip.php — Back-end pour l'ajout d'un trajet
// ======================================================

require_once './composants/autoload.php'; // Connexion DB, sécurités, etc.

// Vérifie que seul un conducteur peut accéder à cette page
checkAccess(['Driver']);

// Vérifie que la requête est bien en POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Méthode non autorisée.";
    header('Location: ../front/user/account.php');
    exit();
}

// Nettoie les données reçues du formulaire
$_POST = sanitizeArray($_POST, '../front/driver/addTrip.php');

// Vérifie que l'utilisateur est bien un conducteur connecté
if (!isset($_SESSION['user']) || !($_SESSION['user'] instanceof Driver)) {
    $_SESSION['error'] = "Accès réservé aux conducteurs.";
    header('Location: ../front/user/login.php');
    exit();
}

$user = $_SESSION['user'];

// Récupère et valide les données du formulaire
$tripData = [
    'departure_city'     => $_POST['departure_city'] ?? null,
    'departure_address'  => $_POST['departure_address'] ?? null,
    'arrival_city'       => $_POST['arrival_city'] ?? null,
    'arrival_address'    => $_POST['arrival_address'] ?? null,
    'departure_date'     => $_POST['departure_date'] ?? null,
    'departure_time'     => $_POST['departure_time'] ?? null,
    'vehicle_id'         => getPostInt('vehicle_id'),
    'available_seats'    => getPostInt('available_seats'),
    'price'              => getPostInt('price'),
    'estimated_duration' => getPostInt('estimated_duration')
];

// Vérifie que tous les champs essentiels sont présents
foreach ($tripData as $key => $value) {
    if ($value === null || $value === '') {
        $_SESSION['error'] = "Champ manquant ou invalide : " . str_replace('_', ' ', $key);
        header('Location: ../front/driver/addTrip.php');
        exit();
    }
}

// Vérifie que le véhicule appartient bien au conducteur et qu’il est approuvé
try {
    $vehicle = new Vehicle($pdo, $tripData['vehicle_id']);
    if ($vehicle->getDocumentsStatus() !== 'approved' || $vehicle->getOwner() !== $user->getId()) {
        throw new Exception("Véhicule non autorisé.");
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Véhicule invalide ou non trouvé.";
    header('Location: ../front/driver/addTrip.php');
    exit();
}

// Vérifie que le nombre de places proposé est cohérent
$maxSeats = $vehicle->getSeats();
if ($tripData['available_seats'] < 1 || $tripData['available_seats'] >= $maxSeats) {
    $_SESSION['error'] = "Le nombre de places doit être compris entre 1 et " . ($maxSeats - 1) . ".";
    header('Location: ../front/driver/addTrip.php');
    exit();
}

// Vérifie que le prix est valide et pas excessif
if ($tripData['price'] < 0) {
    $_SESSION['error'] = "Le prix ne peut pas être négatif.";
    header('Location: ../front/driver/addTrip.php');
    exit();
}
if ($tripData['price'] > 1000000) {
    $_SESSION['error'] = "Le prix est beaucoup trop élevé.";
    header('Location: ../front/driver/addTrip.php');
    exit();
}

// Vérifie qu’aucun trajet existant ne chevauche ce créneau horaire
try {
    $start = new DateTime($tripData['departure_date'] . ' ' . $tripData['departure_time']);
    $end = clone $start;
    $end->modify("+{$tripData['estimated_duration']} seconds");

    $stmt = $pdo->prepare("
        SELECT id FROM trips
        WHERE driver_id = :driver_id
        AND departure_date = :date
        AND (
            (departure_time BETWEEN :start AND :end)
            OR
            (ADDTIME(departure_time, SEC_TO_TIME(estimated_duration)) BETWEEN :start AND :end)
        )
    ");
    $stmt->execute([
        ':driver_id' => $user->getId(),
        ':date'      => $tripData['departure_date'],
        ':start'     => $start->format('H:i:s'),
        ':end'       => $end->format('H:i:s'),
    ]);

    if ($stmt->fetch()) {
        $_SESSION['error'] = "Un trajet est déjà programmé pendant ce créneau horaire.";
        header('Location: ../front/driver/addTrip.php');
        exit();
    }

} catch (Exception $e) {
    $_SESSION['error'] = "Erreur lors de la vérification des trajets existants.";
    header('Location: ../front/driver/addTrip.php');
    exit();
}

// Si tout est bon, propose le trajet
try {
    $user->proposeTrip($pdo, $tripData);
    $user->updateUserSession($pdo);
    $_SESSION['user'] = $user;

    $_SESSION['success'] = "Votre trajet a été ajouté avec succès.";
    header('Location: ../front/user/account.php');
    exit();

} catch (Exception $e) {
    $_SESSION['error'] = "Erreur : " . $e->getMessage();
    header('Location: ../front/driver/addTrip.php');
    exit();
}
