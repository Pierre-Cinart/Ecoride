<?php

require_once __DIR__ . '/../composants/phpMailer/src/SendMail.php';

/**
 * Classe Driver
 * Représente un conducteur (hérite de SimpleUser)
 */
class Driver extends SimpleUser {

    private array $preferences;
    private array $vehicles;
    private float $averageRating;
    private int $driverWarnings;
    private ?string $permitPicture;

    public function __construct(
        int $id,
        string $pseudo,
        string $firstName,
        string $lastName,
        string $email,
        string $phoneNumber,
        string $role,
        int $credits,
        string $status,
        int $userWarnings,
        string $permitStatus,
        string $birthdate,
        string $gender,
        ?string $profilPicture,
        ?string $permitPicture,
        array $preferences,
        array $vehicles,
        float $averageRating,
        int $driverWarnings
    ) {
        parent::__construct(
            $id,
            $pseudo,
            $firstName,
            $lastName,
            $email,
            $phoneNumber,
            $role,
            $credits,
            $status,
            $userWarnings,
            $permitStatus,
            $birthdate,
            $gender,
            $profilPicture
        );

        $this->permitPicture = $permitPicture;
        $this->preferences = $preferences;
        $this->vehicles = $vehicles;
        $this->averageRating = $averageRating;
        $this->driverWarnings = $driverWarnings;
    }

    // === GETTERS ===
    public function getPermitPicture(): ?string { return $this->permitPicture; }
    public function getPreferences(): array { return $this->preferences; }
    public function getVehicles(): array { return $this->vehicles; }
    public function getAverageRating(): float { return $this->averageRating; }
    public function getDriverWarnings(): int { return $this->driverWarnings; }

    /**
     * Supprime un véhicule appartenant au conducteur via l'objet Vehicle
     */
    public function deleteVehicle(PDO $pdo, int $vehicleId): void {
        $vehicle = new Vehicle($pdo, $vehicleId);
        $vehicle->deleteSelf($pdo, $this->id);
    }

    /**
     * Met à jour les données du conducteur et rafraîchit la session.
     */
    public function updateUserSession(PDO $pdo): void {
        try {
            parent::updateUserSession($pdo);

            $stmt = $pdo->prepare("SELECT allows_smoking, allows_pets, note_personnelle FROM driver_preferences WHERE driver_id = :id");
            $stmt->execute([':id' => $this->id]);
            $this->preferences = $stmt->fetch(PDO::FETCH_ASSOC) ?: [
                'allows_smoking' => 0,
                'allows_pets' => 0,
                'note_personnelle' => ''
            ];

            $stmt = $pdo->prepare("SELECT id FROM vehicles WHERE user_id = :id");
            $stmt->execute([':id' => $this->id]);
            $this->vehicles = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'id');

            $stmt = $pdo->prepare("SELECT AVG(rating) AS average FROM ratings WHERE trip_id IN (SELECT id FROM trips WHERE driver_id = :id) AND status = 'accepted'");
            $stmt->execute([':id' => $this->id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->averageRating = isset($result['average']) ? (float)$result['average'] : 0.0;

            $stmt = $pdo->prepare("SELECT permit_picture FROM users WHERE id = :id");
            $stmt->execute([':id' => $this->id]);
            $this->permitPicture = $stmt->fetchColumn();

            $_SESSION['user'] = $this;
        } catch (Exception $e) {
            $_SESSION['error'] = "Erreur lors de la mise à jour du conducteur.";
        }
    }

    /**
     * Propose un nouveau trajet
     */
    public function proposeTrip(PDO $pdo, array $tripData): void {
        if ($this->getPermitStatus() !== 'approved') {
            throw new Exception("Votre permis n'est pas encore validé.");
        }

        if ($this->getCredits() < 2) {
            throw new Exception("Vous n'avez pas assez de crédits.");
        }

        if (in_array($this->getStatus(), ['drive_blocked', 'all_blocked', 'banned'])) {
            throw new Exception("Votre statut actuel ne vous permet pas de proposer un trajet.");
        }

        $stmt = $pdo->prepare("SELECT id FROM vehicles WHERE id = :vehicle_id AND user_id = :user_id");
        $stmt->execute([':vehicle_id' => $tripData['vehicle_id'], ':user_id' => $this->id]);
        if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
            throw new Exception("Ce véhicule ne vous appartient pas.");
        }

        $vehicle = new Vehicle($pdo, $tripData['vehicle_id']);
        if ($vehicle->getDocumentsStatus() !== 'approved') {
            throw new Exception("Les documents du véhicule ne sont pas validés.");
        }

        if ($tripData['available_seats'] >= $vehicle->getSeats()) {
            throw new Exception("Le nombre de places disponibles est incorrect.");
        }

        $stmt = $pdo->prepare("INSERT INTO trips (driver_id, vehicle_id, departure_city, departure_address, arrival_city, arrival_address, departure_date, departure_time, price, is_ecological, available_seats, status, estimated_duration) VALUES (:driver_id, :vehicle_id, :departure_city, :departure_address, :arrival_city, :arrival_address, :departure_date, :departure_time, :price, :is_ecological, :available_seats, 'planned', :estimated_duration)");
        $stmt->execute([
            ':driver_id' => $this->id,
            ':vehicle_id' => $tripData['vehicle_id'],
            ':departure_city' => $tripData['departure_city'],
            ':departure_address' => $tripData['departure_address'],
            ':arrival_city' => $tripData['arrival_city'],
            ':arrival_address' => $tripData['arrival_address'],
            ':departure_date' => $tripData['departure_date'],
            ':departure_time' => $tripData['departure_time'],
            ':price' => $tripData['price'],
            ':is_ecological' => $vehicle->isEcological() ? 1 : 0,
            ':available_seats' => $tripData['available_seats'],
            ':estimated_duration' => $tripData['estimated_duration'] ?? null
        ]);

        $_SESSION['sucess'] = "Votre trajet a été proposé avec succès.";
    }

    /**
     * Annule un trajet proposé par le conducteur
     */
    public function cancelOwnTrip(PDO $pdo, int $tripId): void {
        $stmt = $pdo->prepare("SELECT * FROM trips WHERE id = :id AND driver_id = :driver_id");
        $stmt->execute([':id' => $tripId, ':driver_id' => $this->id]);
        $trip = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$trip) {
            throw new Exception("Ce trajet ne vous appartient pas ou n'existe pas.");
        }

        $stmt = $pdo->prepare("SELECT u.id, u.first_name, u.email, tp.credits_used, t.departure_city, t.arrival_city, t.departure_date FROM trip_participants tp JOIN users u ON u.id = tp.user_id JOIN trips t ON t.id = tp.trip_id WHERE tp.trip_id = :trip_id");
        $stmt->execute([':trip_id' => $tripId]);
        $participants = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($participants as $p) {
            $refundAmount = (int) $p['credits_used'];
            if ($refundAmount > 0) {
                $pdo->prepare("UPDATE users SET credits = credits + :refund WHERE id = :id")
                    ->execute([':refund' => $refundAmount, ':id' => $p['id']]);
            }

            SendMail(
                $p['email'],
                htmlspecialchars($p['first_name']),
                "Annulation de votre réservation EcoRide",
                "Bonjour {$p['first_name']},<br><br>Le trajet de <strong>{$p['departure_city']}</strong> à <strong>{$p['arrival_city']}</strong> prévu le <strong>{$p['departure_date']}</strong> a été annulé.<br>Un remboursement de <strong>{$refundAmount} crédits</strong> a été effectué.<br><br>L'équipe EcoRide."
            );
        }

        $pdo->prepare("DELETE FROM trip_participants WHERE trip_id = :trip_id")
            ->execute([':trip_id' => $tripId]);

        $pdo->prepare("DELETE FROM trips WHERE id = :trip_id")
            ->execute([':trip_id' => $tripId]);
    }
    /**
     * Ajoute un véhicule à la base de données pour ce conducteur.
     * Ne traite pas les documents dans l’insertion : ils seront traités après.
     *
     * @param PDO $pdo Connexion PDO
     * @param array $post Données POST nettoyées (via sanitizeArray par exemple)
     * @param array $files Données FILES du formulaire (photo, carte grise, assurance)
     * @return void
     * @throws Exception En cas d’erreur SQL ou logique
     */
    public function addVehicle(PDO $pdo, array $post, array $files): void {
        // 1. Extraire les champs nécessaires
        $brand     = $post['brand'];
        $model     = $post['model'];
        $fuelType  = $post['fuel_type'];
        $seats     = (int)$post['seats'];
        $color     = $post['color'] ?? 'non précisée';
        $registrationNumber = $post['registration_number'] ?? 'non renseignée';
        $firstRegistrationDate = $post['first_registration_date'] ?? date('Y-m-d');

        // 2. Insertion du véhicule (documents et photo seront traités ensuite)
        $stmt = $pdo->prepare("
            INSERT INTO vehicles 
            (user_id, brand, model, color, fuel_type, registration_number, first_registration_date, seats, documents_status) 
            VALUES 
            (:user_id, :brand, :model, :color, :fuel_type, :registration_number, :first_date, :seats, 'pending')
        ");

        $stmt->execute([
            ':user_id' => $this->getId(),
            ':brand'   => $brand,
            ':model'   => $model,
            ':color'   => $color,
            ':fuel_type' => $fuelType,
            ':registration_number' => $registrationNumber,
            ':first_date' => $firstRegistrationDate,
            ':seats' => $seats
        ]);

        // 3. Récupération de l’ID nouvellement créé
        $vehicleId = (int)$pdo->lastInsertId();

        // 4. Instancier l'objet Vehicle
        $vehicle = new Vehicle($pdo, $vehicleId);

        // 5. Upload des documents si fournis
        if (!empty($files['registration_document']['tmp_name'])) {
            $vehicle->uploadDocument('registration', $files['registration_document']);
        }

        if (!empty($files['insurance_document']['tmp_name'])) {
            $vehicle->uploadDocument('insurance', $files['insurance_document']);
        }

        if (!empty($files['photo']['tmp_name'])) {
            $vehicle->uploadDocument('picture', $files['photo']);
        }

    }

}
