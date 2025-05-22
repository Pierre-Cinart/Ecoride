<?php

require_once __DIR__ . '/../composants/phpMailer/src/sendMail.php';

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
    /**
 * Propose un nouveau trajet
 */
    public function proposeTrip(PDO $pdo, array $tripData): void {
        // Vérification du permis
        if ($this->getPermitStatus() !== 'approved') {
            throw new Exception("Votre permis n'est pas encore validé.");
        }

        // Vérification du solde
        if ($this->getCredits() < 2) {
            throw new Exception("Vous n'avez pas assez de crédits pour proposer un trajet (minimum 2 crédits requis).");
        }

        // Statut bloquant
        if (in_array($this->getStatus(), ['drive_blocked', 'all_blocked', 'banned'])) {
            throw new Exception("Votre statut actuel ne vous permet pas de proposer un trajet.");
        }

        // Vérification du véhicule
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

        // Déduction des crédits du conducteur
        $pdo->prepare("UPDATE users SET credits = credits - 2 WHERE id = :id")
            ->execute([':id' => $this->id]);

        // Transaction : frais de publication
        $pdo->prepare("
            INSERT INTO transactions (user_id, credits, type, description, created_at)
            VALUES (:uid, 2, 'fee', 'Frais de mise en ligne du trajet', NOW())
        ")->execute([':uid' => $this->id]);

        // Insertion du trajet
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

        // Message confirmation
        $_SESSION['success'] = "Votre trajet a été proposé avec succès (2 crédits ont été débités).";
    }


    /**
     * Annule un trajet proposé par le conducteur
     */
    public function cancelOwnTrip(PDO $pdo, int $tripId): void {
        // 1. Vérification que le trajet appartient bien au conducteur
        $stmt = $pdo->prepare("SELECT * FROM trips WHERE id = :id AND driver_id = :driver_id");
        $stmt->execute([':id' => $tripId, ':driver_id' => $this->id]);
        $trip = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$trip) {
            throw new Exception("Ce trajet ne vous appartient pas ou n'existe pas.");
        }

        // 2. Récupération des passagers à rembourser
        $stmt = $pdo->prepare("
            SELECT u.id, u.first_name, u.email, tp.credits_used, t.departure_city, t.arrival_city, t.departure_date, t.departure_time
            FROM trip_participants tp 
            JOIN users u ON u.id = tp.user_id 
            JOIN trips t ON t.id = tp.trip_id 
            WHERE tp.trip_id = :trip_id
        ");
        $stmt->execute([':trip_id' => $tripId]);
        $participants = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $hadParticipants = count($participants) > 0;

        foreach ($participants as $p) {
            $refundAmount = (int) $p['credits_used'];

            if ($refundAmount > 0) {
                $pdo->prepare("UPDATE users SET credits = credits + :refund WHERE id = :id")
                    ->execute([':refund' => $refundAmount, ':id' => $p['id']]);

                $pdo->prepare("
                    INSERT INTO transactions (user_id, credits, type, description, created_at)
                    VALUES (:uid, :credits, 'refund', :desc, NOW())
                ")->execute([
                    ':uid'     => $p['id'],
                    ':credits' => $refundAmount,
                    ':desc'    => 'Remboursement suite à annulation trajet ' . $tripId
                ]);
            }

            SendMail(
                $p['email'],
                htmlspecialchars($p['first_name']),
                "Annulation de votre réservation EcoRide",
                "Bonjour {$p['first_name']},<br><br>
                Le trajet de <strong>{$p['departure_city']}</strong> à <strong>{$p['arrival_city']}</strong>
                prévu le <strong>{$p['departure_date']}</strong> à <strong>{$p['departure_time']}</strong> a été annulé.<br>
                Un remboursement de <strong>{$refundAmount} crédits</strong> a été effectué.<br><br>
                L'équipe EcoRide."
            );
        }

        $penaltyApplied = false;
        $statusBlocked = false;
        $message = "Trajet annulé avec succès.";

        if ($hadParticipants) {
            $penaltyAmount = 2;

            $stmt = $pdo->prepare("SELECT credits, driver_warnings FROM users WHERE id = :id");
            $stmt->execute([':id' => $this->id]);
            $driverData = $stmt->fetch(PDO::FETCH_ASSOC);

            $currentCredits = (int) $driverData['credits'];
            $currentWarnings = (int) $driverData['driver_warnings'];

            $pdo->prepare("UPDATE users SET credits = credits - :penalty WHERE id = :id")
                ->execute([':penalty' => $penaltyAmount, ':id' => $this->id]);

            $pdo->prepare("
                INSERT INTO transactions (user_id, credits, type, description, created_at)
                VALUES (:uid, :credits, 'penalty', :desc, NOW())
            ")->execute([
                ':uid'     => $this->id,
                ':credits' => $penaltyAmount,
                ':desc'    => "Pénalité pour annulation de trajet avec participants (ID $tripId)"
            ]);

            $penaltyApplied = true;

            $stmt = $pdo->prepare("SELECT credits FROM users WHERE id = :id");
            $stmt->execute([':id' => $this->id]);
            $newCredits = (int) $stmt->fetchColumn();

            $modulo = $currentWarnings % 10;

            if ($modulo < 3) {
                $newWarnings = $currentWarnings + 1;
            } elseif ($currentWarnings === 3 || $currentWarnings === 13) {
                $newWarnings = $currentWarnings + 10;
            } elseif ($currentWarnings >= 23) {
                $newWarnings = $currentWarnings;
            } else {
                $newWarnings = $currentWarnings + 1;
            }

            $pdo->prepare("UPDATE users SET driver_warnings = :warnings WHERE id = :id")
                ->execute([':warnings' => $newWarnings, ':id' => $this->id]);

            if ($newWarnings >= 23) {
                $newStatus = 'banned';
                $statusBlocked = true;

                $message = "Trajet annulé. Vous avez été définitivement banni du service. Tous vos trajets à venir ont été annulés et vos passagers remboursés.";

                $stmt = $pdo->prepare("SELECT * FROM trips WHERE driver_id = :driver_id AND status = 'planned'");
                $stmt->execute([':driver_id' => $this->id]);
                $futureTrips = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($futureTrips as $tripRow) {
                    $tid = (int)$tripRow['id'];

                    $stmtP = $pdo->prepare("
                        SELECT u.id, u.first_name, u.email, tp.credits_used, t.departure_city, t.arrival_city, t.departure_date, t.departure_time
                        FROM trip_participants tp
                        JOIN users u ON u.id = tp.user_id
                        JOIN trips t ON t.id = tp.trip_id
                        WHERE tp.trip_id = :trip_id
                    ");
                    $stmtP->execute([':trip_id' => $tid]);
                    $passengers = $stmtP->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($passengers as $pass) {
                        $refund = (int) $pass['credits_used'];

                        if ($refund > 0) {
                            $pdo->prepare("UPDATE users SET credits = credits + :refund WHERE id = :id")
                                ->execute([':refund' => $refund, ':id' => $pass['id']]);

                            $pdo->prepare("
                                INSERT INTO transactions (user_id, credits, type, description, created_at)
                                VALUES (:uid, :credits, 'refund', :desc, NOW())
                            ")->execute([
                                ':uid'     => $pass['id'],
                                ':credits' => $refund,
                                ':desc'    => "Remboursement suite à bannissement conducteur, trajet $tid"
                            ]);
                        }

                        SendMail(
                            $pass['email'],
                            htmlspecialchars($pass['first_name']),
                            "Annulation de votre trajet EcoRide",
                            "Bonjour {$pass['first_name']},<br><br>
                            Nous sommes désolés, ce conducteur a été retiré de nos services suite à de nombreux signalements.<br>
                            Votre trajet prévu le <strong>{$pass['departure_date']}</strong> à <strong>{$pass['departure_time']}</strong> entre <strong>{$pass['departure_city']}</strong> et <strong>{$pass['arrival_city']}</strong> a été annulé.<br>
                            Un remboursement de <strong>{$refund} crédits</strong> a été effectué.<br><br>
                            Merci de votre compréhension.<br>L'équipe EcoRide."
                        );
                    }

                    $pdo->prepare("DELETE FROM trip_participants WHERE trip_id = :trip_id")
                        ->execute([':trip_id' => $tid]);

                    $pdo->prepare("DELETE FROM trips WHERE id = :trip_id")
                        ->execute([':trip_id' => $tid]);
                }
            } elseif ($newWarnings % 10 === 3 || $newCredits < 0) {
                $newStatus = 'drive_blocked';
                $statusBlocked = true;
                $message = ($newCredits < 0)
                    ? "Trajet annulé. Vous avez reçu une pénalité. Votre compte est débiteur et votre statut de conducteur est bloqué jusqu’à régularisation."
                    : "Trajet annulé. Vous avez reçu une pénalité et votre statut de conducteur est temporairement bloqué.";
            } else {
                $message = "Trajet annulé. Vous avez reçu une pénalité de 2 crédits.";
            }

            if (isset($newStatus)) {
                $pdo->prepare("UPDATE users SET status = :status WHERE id = :id")
                    ->execute([':status' => $newStatus, ':id' => $this->id]);
            }
        }

        $pdo->prepare("DELETE FROM trip_participants WHERE trip_id = :trip_id")
            ->execute([':trip_id' => $tripId]);

        $pdo->prepare("DELETE FROM trips WHERE id = :trip_id")
            ->execute([':trip_id' => $tripId]);

        // Affectation finale dans la bonne session
        if ($penaltyApplied || $statusBlocked) {
            $_SESSION['error'] = $message;
        } else {
            $_SESSION['success'] = $message;
        }
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
