<?php
require_once 'SimpleUser.php';

/**
 * Classe représentant un conducteur sur EcoRide.
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
}
