<?php
require_once 'SimpleUser.php';

/**
 * Classe Driver
 * Représente un conducteur, héritant d’un utilisateur simple.
 */
class Driver extends SimpleUser {

    private array $preferences;
    private array $vehicles;
    private float $averageRating;
    private int $driverWarnings;

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
    array $preferences,
    array $vehicles,
    float $averageRating,
    int $driverWarnings
) {
    parent::__construct($id, $pseudo, $firstName, $lastName, $email, $phoneNumber, $role, $credits, $status, $userWarnings);

    $this->preferences = $preferences;
    $this->vehicles = $vehicles;
    $this->averageRating = $averageRating;
    $this->driverWarnings = $driverWarnings;
}



    // === GETTERS ===

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
     * Supprime un véhicule s’il appartient bien au conducteur, puis met à jour la session.
     *
     * @param PDO $pdo Connexion PDO à la base de données
     * @param int $vehicleId ID du véhicule à supprimer
     * @throws Exception si le véhicule n'existe pas ou n’appartient pas au conducteur
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

        // Suppression du véhicule
        $deleteStmt = $pdo->prepare("DELETE FROM vehicles WHERE id = :id");
        if (!$deleteStmt->execute([':id' => $vehicleId])) {
            throw new Exception("Échec de la suppression du véhicule.");
        }

        // Mise à jour de la session après suppression
        $_SESSION['sucess'] = 'votre véhicule à bien était retiré de votre liste';
        $this->updateUserSession($pdo);
    }

    /**
     * Met à jour les données du conducteur et rafraîchit la session.
     * Un rechargement de la page est déclenché automatiquement.
     *
     * @param PDO $pdo Connexion PDO
     */
    public function updateUserSession(PDO $pdo): void {
        try {
            // 1. Données utilisateur
            $stmt = $pdo->prepare("SELECT pseudo, first_name, last_name, email, phone_number, role, credits FROM users WHERE id = :id");
            $stmt->execute([':id' => $this->id]);
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$userData) {
                throw new Exception("Utilisateur introuvable.");
            }

            // 2. Préférences
            $stmt = $pdo->prepare("SELECT allows_smoking, allows_pets, note_personnelle FROM driver_preferences WHERE driver_id = :id");
            $stmt->execute([':id' => $this->id]);
            $preferences = $stmt->fetch(PDO::FETCH_ASSOC) ?: [
                'allows_smoking' => 0,
                'allows_pets' => 0,
                'note_personnelle' => ''
            ];

            // 3. Véhicules
            $stmt = $pdo->prepare("SELECT id FROM vehicles WHERE user_id = :id");
            $stmt->execute([':id' => $this->id]);
            $vehicles = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'id');

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
            $averageRating = isset($result['average']) ? (float) $result['average'] : 0.0;

            // 5. Mise à jour des attributs
            $this->pseudo = $userData['pseudo'];
            $this->firstName = $userData['first_name'];
            $this->lastName = $userData['last_name'];
            $this->email = $userData['email'];
            $this->phoneNumber = $userData['phone_number'];
            $this->role = $userData['role'];
            $this->credits = (int) $userData['credits'];
            $this->preferences = $preferences;
            $this->vehicles = $vehicles;
            $this->averageRating = $averageRating;

            // 6. Injection en session
            $_SESSION['user'] = $this;

         

        } catch (Exception $e) {
            $_SESSION['error'] = "Erreur lors de la mise à jour du conducteur.";
        }
    }

    /**
     * Annule un voyage appartenant à ce conducteur.
     * Gère les remboursements des passagers, les emails d'information et les avertissements.
     *
     * @param PDO $pdo Connexion à la base de données
     * @param int $tripId ID du voyage à annuler
     * @throws Exception Si le voyage ne lui appartient pas ou n'existe pas
     */
    public function cancelOwnTrip(PDO $pdo, int $tripId): void {
        // 1. Vérifie que le voyage appartient bien au conducteur
        $stmt = $pdo->prepare("SELECT id, date_depart FROM trips WHERE id = :trip_id AND driver_id = :driver_id");
        $stmt->execute([
            ':trip_id' => $tripId,
            ':driver_id' => $this->id
        ]);

        $trip = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$trip) {
            throw new Exception("Ce voyage n'existe pas ou ne vous appartient pas.");
        }

        // 2. Vérifie les passagers inscrits à ce voyage
        $stmt = $pdo->prepare("
            SELECT users.id AS user_id, users.email, users.pseudo, participants.credits_used
            FROM trip_participants AS participants
            JOIN users ON participants.user_id = users.id
            WHERE participants.trip_id = :trip_id AND participants.status = 'accepted'
        ");
        $stmt->execute([':trip_id' => $tripId]);
        $passengers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 3. Pour chaque passager, rembourser + notifier
        foreach ($passengers as $passenger) {
            $userId = $passenger['user_id'];
            $email = $passenger['email'];
            $pseudo = $passenger['pseudo'];
            $creditsUsed = (int)$passenger['credits_used'];

            // Remboursement
            $refund = $pdo->prepare("UPDATE users SET credits = credits + :credits WHERE id = :id");
            $refund->execute([
                ':credits' => $creditsUsed,
                ':id' => $userId
            ]);

            // Envoi du mail de notification
            SendMail(
                $email,
                "Votre voyage a été annulé",
                "Bonjour $pseudo,<br><br>
                Le voyage auquel vous étiez inscrit a été annulé par le conducteur.<br>
                Vous avez été remboursé de $creditsUsed crédits.<br><br>
                Nous vous prions de nous excuser pour ce désagrément.<br><br>
                L'équipe EcoRide"
            );
        }

        // 4. Si au moins un passager impacté, notifier le conducteur
        if (!empty($passengers)) {
            // Incrémenter avertissements du conducteur
            $warn = $pdo->prepare("UPDATE users SET warnings = warnings + 1 WHERE id = :id");
            $warn->execute([':id' => $this->id]);

            // Notifier le conducteur
            SendMail(
                $this->email,
                "Annulation avec impact passagers",
                "Bonjour {$this->getFirstName()},<br><br>
                Vous avez annulé un voyage avec des passagers inscrits.<br>
                Cela a engendré des remboursements et a généré un avertissement.<br>
                Merci d'éviter les annulations de dernière minute à l'avenir.<br><br>
                L'équipe EcoRide"
            );
        }

        // 5. Supprimer les participants liés
        $delPart = $pdo->prepare("DELETE FROM trip_participants WHERE trip_id = :trip_id");
        $delPart->execute([':trip_id' => $tripId]);

        // 6. Supprimer le voyage lui-même
        $delTrip = $pdo->prepare("DELETE FROM trips WHERE id = :trip_id");
        $delTrip->execute([':trip_id' => $tripId]);

        // (Optionnel : ajout log MongoDB ou fichier texte)
    }

}
