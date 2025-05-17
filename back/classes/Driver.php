<?php
require_once 'SimpleUser.php';
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
     * Supprime un véhicule appartenant au conducteur via l'objet Vehicle
     *
     * @param PDO $pdo Connexion à la base
     * @param int $vehicleId ID du véhicule à supprimer
     * @throws Exception si le véhicule n'existe pas ou ne lui appartient pas
     */
    public function deleteVehicle(PDO $pdo, int $vehicleId): void {
        // Instanciation du véhicule (lève une exception si l'ID est invalide)
        $vehicle = new Vehicle($pdo, $vehicleId);

        // Appel à sa méthode deleteSelf avec l'ID du driver courant
        $vehicle->deleteSelf($pdo, $this->id);
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
     * Annule un trajet proposé par le conducteur :
     * - Rembourse les passagers
     * - Envoie un email de notification
     * - Supprime les participations et le trajet
     *
     * @param PDO $pdo Connexion PDO
     * @param int $tripId ID du trajet à annuler
     * @throws Exception Si le trajet ne lui appartient pas ou autre erreur SQL
     */
    public function cancelOwnTrip(PDO $pdo, int $tripId): void {
        // 1. Vérifie que le trajet appartient bien au conducteur
        $stmt = $pdo->prepare("SELECT * FROM trips WHERE id = :id AND driver_id = :driver_id");
        $stmt->execute([
            ':id' => $tripId,
            ':driver_id' => $this->id
        ]);
        $trip = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$trip) {
            throw new Exception("Ce trajet ne vous appartient pas ou n'existe pas.");
        }

        // 2. Récupère les participants avec les infos nécessaires
        $stmt = $pdo->prepare("
            SELECT u.id, u.first_name, u.email, tp.credits_used, t.departure_city, t.arrival_city, t.departure_date
            FROM trip_participants tp
            JOIN users u ON u.id = tp.user_id
            JOIN trips t ON t.id = tp.trip_id
            WHERE tp.trip_id = :trip_id
        ");
        $stmt->execute([':trip_id' => $tripId]);
        $participants = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 3. Rembourse chaque passager et envoie un mail
        foreach ($participants as $p) {
            $passengerId = (int) $p['id'];
            $refundAmount = (int) $p['credits_used'];
            $email = $p['email'];
            $firstName = htmlspecialchars($p['first_name']);

            if ($refundAmount > 0) {
                $refundStmt = $pdo->prepare("UPDATE users SET credits = credits + :refund WHERE id = :id");
                $refundStmt->execute([
                    ':refund' => $refundAmount,
                    ':id' => $passengerId
                ]);
            }

            // Envoie l’email (✔️ avec les bons paramètres)
            $subject = "Annulation de votre réservation EcoRide";
            $body = "Bonjour $firstName,<br><br>"
                . "Le trajet que vous aviez réservé de <strong>{$p['departure_city']}</strong> à <strong>{$p['arrival_city']}</strong> "
                . "prévu le <strong>{$p['departure_date']}</strong> a été annulé.<br><br>"
                . "Vous avez été automatiquement remboursé(e) de <strong>{$refundAmount} crédits</strong> sur votre compte.<br><br>"
                . "Nous vous prions de nous excuser pour ce désagrément.<br><br>"
                . "L'équipe EcoRide.";

            // ✅ Appel corrigé de SendMail (avec le prénom en 2e paramètre)
            SendMail($email, $firstName, $subject, $body);
        }

        // 4. Supprime les participations au trajet
        $stmt = $pdo->prepare("DELETE FROM trip_participants WHERE trip_id = :trip_id");
        $stmt->execute([':trip_id' => $tripId]);

        // 5. Supprime le trajet lui-même
        $stmt = $pdo->prepare("DELETE FROM trips WHERE id = :trip_id");
        $stmt->execute([':trip_id' => $tripId]);
    }




}
