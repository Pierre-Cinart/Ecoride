<?php
require_once '../back/composants/db_connect.php';

try {
    $pdo->beginTransaction();

    /** 1. Création des comptes utilisateurs **/

    $stmt = $pdo->prepare("INSERT INTO users 
        (pseudo, first_name, last_name, email, password, phone_number, role, is_verified_email)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

    // Admin
    $stmt->execute(['admin', 'José', 'Admin', 'admin@ecoride.fr', password_hash('mot2passe', PASSWORD_DEFAULT), '0100000001', 'admin', 1]);

    // Employé
    $stmt->execute(['employe1', 'Employé', 'Test', 'employe@ecoride.fr', password_hash('mot2passe', PASSWORD_DEFAULT), '0100000002', 'employee', 1]);

    // Utilisateur simple
    $stmt->execute(['user1', 'User', 'Test', 'user@ecoride.fr', password_hash('mot2passe', PASSWORD_DEFAULT), '0100000003', 'user', 1]);
    $userId = $pdo->lastInsertId(); // nécessaire pour créer les avis plus tard

    // Conducteur validé
    $stmt->execute(['driver1', 'Conducteur', 'Valide', 'driver.valide@ecoride.fr', password_hash('mot2passe', PASSWORD_DEFAULT), '0100000004', 'driver', 1]);
    $driverId1 = $pdo->lastInsertId();

    // Conducteur en attente
    $stmt->execute(['driver2', 'Conducteur', 'Attente', 'driver.attente@ecoride.fr', password_hash('mot2passe', PASSWORD_DEFAULT), '0100000005', 'driver', 1]);
    $driverId2 = $pdo->lastInsertId();

    /** 2. Ajout des documents permis **/

    $stmtDoc = $pdo->prepare("INSERT INTO documents 
        (user_id, type, file_path, status, submitted_at) 
        VALUES (?, ?, ?, ?, ?)");

    $stmtDoc->execute([$driverId1, 'permit', '../back/uploads/test/test.jpg', 'approved', date('Y-m-d H:i:s')]);
    $stmtDoc->execute([$driverId2, 'permit', '../back/uploads/test/test.jpg', 'pending', date('Y-m-d H:i:s')]);

    /** 3. Création du véhicule pour driver1 **/

    $stmtVeh = $pdo->prepare("INSERT INTO vehicles 
        (user_id, brand, model, color, fuel_type, registration_number, first_registration_date, seats, picture) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmtVeh->execute([
        $driverId1,
        'Renault', 'ZOE', 'Blanc', 'electric',
        'AB-123-CD', '2023-05-15', 4,
        '../back/uploads/driver1/vehicule-test.jpg' // doit exister !
    ]);
    $vehicleId = $pdo->lastInsertId();

    /** 4. Ajout des voyages **/

    $stmtTrip = $pdo->prepare("INSERT INTO trips 
        (driver_id, vehicle_id, departure_city, departure_address, arrival_city, arrival_address, departure_date, departure_time, price, is_ecological, available_seats, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    // === Voyages PASSÉS (pour pouvoir créer des notes) ===
    $pastTrips = [
        ['Lille', 'Adresse Lille', 'Paris', 'Adresse Paris', '2025-04-24', '08:00:00'],
        ['Paris', 'Adresse Paris', 'Lyon', 'Adresse Lyon', '2025-04-29', '09:00:00'],
        ['Lyon', 'Adresse Lyon', 'Nice', 'Adresse Nice', '2025-05-04', '10:00:00'],
    ];

    $tripIds = []; // pour stocker les ID des voyages passés
    foreach ($pastTrips as $trip) {
        $stmtTrip->execute([
            $driverId1, $vehicleId,
            $trip[0], $trip[1],
            $trip[2], $trip[3],
            $trip[4], $trip[5],
            20, 1, 3, 'completed'
        ]);
        $tripIds[] = $pdo->lastInsertId(); // à utiliser pour les notes
    }

    // === Voyages FUTURS (pour test dans la recherche) ===
    $futureTrips = [
        ['Toulouse', 'Adresse Toulouse', 'Bordeaux', 'Adresse Bordeaux', '2025-05-16', '14:00:00'],
        ['Bordeaux', 'Adresse Bordeaux', 'Nantes', 'Adresse Nantes', '2025-05-18', '10:30:00'],
        ['Nantes', 'Adresse Nantes', 'Rennes', 'Adresse Rennes', '2025-05-20', '12:15:00'],
    ];

    foreach ($futureTrips as $trip) {
        $stmtTrip->execute([
            $driverId1, $vehicleId,
            $trip[0], $trip[1],
            $trip[2], $trip[3],
            $trip[4], $trip[5],
            18, 1, 2, 'planned'
        ]);
    }

    /** 5. Ajout de 3 avis/notes sur les voyages passés **/

    $stmtNote = $pdo->prepare("INSERT INTO ratings 
        (author_id, trip_id, rating, comment, status, created_at) 
        VALUES (?, ?, ?, ?, ?, ?)");

    $notes = [
        [4.5, 'Super trajet, conducteur au top !'],
        [3.5, 'Ponctuel et prudent.'],
        [5.0, 'Parfait, rien à dire.'],
    ];

    foreach ($tripIds as $i => $tripId) {
        $stmtNote->execute([
            $userId, $tripId,
            $notes[$i][0], $notes[$i][1], 'accepted', date('Y-m-d H:i:s')
        ]);
    }

    $pdo->commit();
    echo "✅ Données de test injectées avec succès.";

} catch (Exception $e) {
    $pdo->rollBack();
    echo "❌ Erreur : " . $e->getMessage();
}
?>
