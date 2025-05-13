<?php
require_once 'SimpleUser.php';

/**
 * 
 * Hérite de SimpleUser et ajoute des propriétés spécifiques.
 */
class Driver extends SimpleUser {

    // === ATTRIBUTS SPÉCIFIQUES AU CONDUCTEUR ===

    /**
     * Préférences du conducteur (fumeur, animaux, note personnelle)
     * Format attendu : ['allows_smoking' => 0/1, 'allows_pets' => 0/1, 'note_personnelle' => string]
     */
    private array $preferences;

    /**
     * Liste des véhicules du conducteur
     * Format attendu : tableau de tableaux associatifs
     */
    private array $vehicles;

    /**
     * Note moyenne du conducteur calculée depuis les avis
     */
    private float $averageRating;

    /**
     * Constructeur du conducteur.
     * Reçoit toutes les données de base + ses données spécifiques.
     */
    public function __construct(
        int $id,
        string $pseudo,
        string $firstName,
        string $lastName,
        string $email,
        string $phoneNumber,
        string $role,
        int $credits,
        array $preferences = [],
        array $vehicles = [],
        float $averageRating = 0.0
    ) {
        // Appel du constructeur de SimpleUser pour les données de base
        parent::__construct($id, $pseudo, $firstName, $lastName, $email, $phoneNumber, $role, $credits);

        // Initialisation des données propres au conducteur
        $this->preferences = $preferences;
        $this->vehicles = $vehicles;
        $this->averageRating = $averageRating;
    }

    // === GETTERS SPÉCIFIQUES AU CONDUCTEUR ===

    /**
     * Retourne les préférences du conducteur
     */
    public function getPreferences(): array {
        return $this->preferences;
    }

    /**
     * Retourne les véhicules du conducteur
     */
    public function getVehicles(): array {
        return $this->vehicles;
    }

    /**
     * Retourne la note moyenne du conducteur
     */
    public function getAverageRating(): float {
        return $this->averageRating;
    }

     // === GETTERS SPÉCIFIQUES AU CONDUCTEUR ===
    /**
     * Met à jour les informations du conducteur et les recharge en session.
     */
    public function updateUserSession(PDO $pdo): void {
        try {
            // 1. Requête des données de base depuis la table `users`
            $stmt = $pdo->prepare("SELECT pseudo, first_name, last_name, email, phone_number, role, credits FROM users WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $this->id]);
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$userData) {
                throw new Exception("Utilisateur introuvable.");
            }

            // 2. Requête des préférences conducteur
            $stmt = $pdo->prepare("SELECT allows_smoking, allows_pets, note_personnelle FROM driver_preferences WHERE driver_id = :id");
            $stmt->execute([':id' => $this->id]);
            $preferences = $stmt->fetch(PDO::FETCH_ASSOC) ?: [
                'allows_smoking' => 0,
                'allows_pets' => 0,
                'note_personnelle' => ''
            ];

            // 3. Requête des IDs des véhicules du conducteur
            $stmt = $pdo->prepare("SELECT id FROM vehicles WHERE user_id = :id");
            $stmt->execute([':id' => $this->id]);
            $vehicles = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'id');

            // 4. Requête de la note moyenne
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

            // 5. Mise à jour des propriétés héritées
            $this->pseudo       = $userData['pseudo'];
            $this->firstName    = $userData['first_name'];
            $this->lastName     = $userData['last_name'];
            $this->email        = $userData['email'];
            $this->phoneNumber  = $userData['phone_number'];
            $this->role         = $userData['role'];
            $this->credits      = (int) $userData['credits'];

            // 6. Mise à jour des propriétés spécifiques au conducteur
            $this->preferences = $preferences;
            $this->vehicles = $vehicles;
            $this->averageRating = $averageRating;

            // 7. Réinjection dans la session
            $_SESSION['user'] = $this;

        } catch (Exception $e) {
            $_SESSION['error'] = "Erreur lors de la mise à jour du conducteur.";
        }
    }
}
