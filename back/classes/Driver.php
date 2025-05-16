<?php
require_once 'SimpleUser.php';

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

    public function getPermitPicture(): ?string {
        return $this->permitPicture;
    }

    public function getPreferences(): array {
        return $this->preferences;
    }

    public function getVehicles(): array {
        return $this->vehicles;
    }

    public function getAverageRating(): float {
        return $this->averageRating;
    }

    public function getDriverWarnings(): int {
        return $this->driverWarnings;
    }

    /**
     * Supprime un véhicule appartenant au conducteur et met à jour la session.
     */
    public function deleteVehicule(PDO $pdo, int $vehicleId): void {
        $stmt = $pdo->prepare("SELECT id FROM vehicles WHERE id = :vehicle_id AND user_id = :user_id");
        $stmt->execute([
            ':vehicle_id' => $vehicleId,
            ':user_id' => $this->id
        ]);

        if ($stmt->rowCount() === 0) {
            throw new Exception("Ce véhicule n'existe pas ou ne vous appartient pas.");
        }

        $deleteStmt = $pdo->prepare("DELETE FROM vehicles WHERE id = :id");
        if (!$deleteStmt->execute([':id' => $vehicleId])) {
            throw new Exception("Échec de la suppression du véhicule.");
        }

        $_SESSION['sucess'] = 'Votre véhicule a bien été retiré de votre liste.';
        $this->updateUserSession($pdo);
    }

    /**
     * Met à jour les données du conducteur et rafraîchit la session.
     */
   public function updateUserSession(PDO $pdo): void {
    try {
        // 1. Mise à jour des champs de la classe mère
        parent::updateUserSession($pdo);

        // 2. Préférences
        $stmt = $pdo->prepare("SELECT allows_smoking, allows_pets, note_personnelle FROM driver_preferences WHERE driver_id = :id");
        $stmt->execute([':id' => $this->id]);
        $this->preferences = $stmt->fetch(PDO::FETCH_ASSOC) ?: [
            'allows_smoking' => 0,
            'allows_pets' => 0,
            'note_personnelle' => ''
        ];

        // 3. Véhicules
        $stmt = $pdo->prepare("SELECT id FROM vehicles WHERE user_id = :id");
        $stmt->execute([':id' => $this->id]);
        $this->vehicles = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'id');

        // 4. Moyenne des avis
        $stmt = $pdo->prepare("
            SELECT AVG(rating) AS average
            FROM ratings
            WHERE trip_id IN (
                SELECT id FROM trips WHERE driver_id = :id
            ) AND status = 'accepted'
        ");
        $stmt->execute([':id' => $this->id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->averageRating = isset($result['average']) ? (float)$result['average'] : 0.0;

        // 5. Image du permis
        $stmt = $pdo->prepare("SELECT permit_picture FROM users WHERE id = :id");
        $stmt->execute([':id' => $this->id]);
        $this->permitPicture = $stmt->fetchColumn();

        // 6. Injection dans la session
        $_SESSION['user'] = $this;

    } catch (Exception $e) {
        $_SESSION['error'] = "Erreur lors de la mise à jour du conducteur.";
    }
}


    /**
     * Annule un voyage appartenant au conducteur (avec gestion des passagers et avertissements).
     */
    public function cancelOwnTrip(PDO $pdo, int $tripId): void {
        // Vérification de la propriété du voyage
        $stmt = $pdo->prepare("SELECT id FROM trips WHERE id = :trip_id AND driver_id = :driver_id");
        $stmt->execute([
            ':trip_id' => $tripId,
            ':driver_id' => $this->id
        ]);
        $trip = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$trip) {
            throw new Exception("Ce voyage n'existe pas ou ne vous appartient pas.");
        }

        // Récupération des passagers
        $stmt = $pdo->prepare("
            SELECT users.id AS user_id, users.email, users.pseudo, participants.credits_used
            FROM trip_participants AS participants
            JOIN users ON participants.user_id = users.id
            WHERE participants.trip_id = :trip_id AND participants.confirmed = 1
        ");
        $stmt->execute([':trip_id' => $tripId]);
        $passengers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($passengers as $passenger) {
            // Remboursement
            $pdo->prepare("UPDATE users SET credits = credits + :credits WHERE id = :id")
                ->execute([
                    ':credits' => (int)$passenger['credits_used'],
                    ':id' => $passenger['user_id']
                ]);

            // Mail passager
            SendMail(
                $passenger['email'],
                "Votre voyage a été annulé",
                "Bonjour {$passenger['pseudo']},<br><br>
                Le voyage auquel vous étiez inscrit a été annulé.<br>
                Vous avez été remboursé de {$passenger['credits_used']} crédits.<br><br>
                Merci pour votre compréhension.<br><br>
                L'équipe EcoRide"
            );
        }

        if (!empty($passengers)) {
            // Avertissement conducteur
            $pdo->prepare("UPDATE users SET driver_warnings = driver_warnings + 1 WHERE id = :id")
                ->execute([':id' => $this->id]);

            SendMail(
                $this->email,
                "Annulation avec impact",
                "Bonjour {$this->getFirstName()},<br><br>
                Vous avez annulé un voyage comportant des passagers confirmés.<br>
                Cela vous a généré un avertissement.<br><br>
                Merci d'éviter les annulations de dernière minute.<br><br>
                L'équipe EcoRide"
            );
        }

        // Suppression des participations et du voyage
        $pdo->prepare("DELETE FROM trip_participants WHERE trip_id = :trip_id")
            ->execute([':trip_id' => $tripId]);
        $pdo->prepare("DELETE FROM trips WHERE id = :trip_id")
            ->execute([':trip_id' => $tripId]);
    }

    /**
     * Permet au conducteur de proposer un nouveau trajet.
     */
    public function proposeTrip(PDO $pdo, array $tripData): void {
        // Vérification : permis validé
        if ($this->getPermitStatus() !== 'approved') {
            throw new Exception("Votre permis n'est pas encore validé.");
        }

        // Vérification : statut autorisé
        if (in_array($this->getStatus(), ['drive_blocked', 'all_blocked', 'banned'])) {
            throw new Exception("Votre statut actuel ne vous permet pas de proposer un trajet.");
        }

        // Vérification : véhicule appartenant au conducteur
        $stmt = $pdo->prepare("SELECT fuel_type, seats, documents_status FROM vehicles WHERE id = :vehicle_id AND user_id = :user_id");
        $stmt->execute([
            ':vehicle_id' => $tripData['vehicle_id'],
            ':user_id' => $this->id
        ]);
        $vehicle = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$vehicle) {
            throw new Exception("Ce véhicule ne vous appartient pas.");
        }

        if ($vehicle['documents_status'] !== 'approved') {
            throw new Exception("Les documents du véhicule ne sont pas validés.");
        }

        // Déterminer si le trajet est écologique
        $isEcological = in_array($vehicle['fuel_type'], ['electric', 'hybrid']) ? 1 : 0;

        // Vérification de la cohérence des places
        if ($tripData['available_seats'] >= $vehicle['seats']) {
            throw new Exception("Le nombre de places disponibles ne peut pas être supérieur au nombre de sièges du véhicule.");
        }

        // Insertion du trajet
        $stmt = $pdo->prepare("
            INSERT INTO trips (
                driver_id, vehicle_id, departure_city, departure_address,
                arrival_city, arrival_address, departure_date, departure_time,
                price, is_ecological, available_seats, status, estimated_duration
            ) VALUES (
                :driver_id, :vehicle_id, :departure_city, :departure_address,
                :arrival_city, :arrival_address, :departure_date, :departure_time,
                :price, :is_ecological, :available_seats, 'planned', :estimated_duration
            )
        ");

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
            ':is_ecological' => $isEcological,
            ':available_seats' => $tripData['available_seats'],
            ':estimated_duration' => $tripData['estimated_duration'] ?? null
        ]);

        $_SESSION['sucess'] = "Votre trajet a été proposé avec succès.";
    }

}
