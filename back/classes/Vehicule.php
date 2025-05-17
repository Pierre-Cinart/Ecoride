<?php

class Vehicle {
    private int $id;
    private int $owner;
    private string $brand;
    private string $model;
    private ?string $registrationDocument;
    private ?string $insuranceDocument;
    private string $documentsStatus;
    private string $color;
    private string $fuelType;
    private string $registrationNumber;
    private string $firstRegistrationDate;
    private int $seats;

    /**
     * Constructeur : charge les données du véhicule depuis la base à partir de son ID
     */
    public function __construct(PDO $pdo, int $id) {
        $stmt = $pdo->prepare("SELECT * FROM vehicles WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            throw new Exception("Véhicule introuvable.");
        }

        $this->id                     = (int) $data['id'];
        $this->owner                 = (int) $data['user_id'];
        $this->brand                 = $data['brand'];
        $this->model                 = $data['model'];
        $this->color                 = $data['color'];
        $this->fuelType              = $data['fuel_type'];
        $this->registrationNumber    = $data['registration_number'];
        $this->firstRegistrationDate = $data['first_registration_date'];
        $this->seats                 = (int) $data['seats'];
        $this->registrationDocument  = $data['registration_document'];
        $this->insuranceDocument     = $data['insurance_document'];
        $this->documentsStatus       = $data['documents_status'];
    }

    ///////////// GETTERS /////////////

    /**
     * Retourne l’ID du véhicule
     */
    public function getId(): int {
        return $this->id;
    }
    /**
     * Retourne l’ID du véhicule
     */
    public function getOwner(): int {
        return $this->owner;
    }
    /**
     * Retourne la marque (ex: Renault)
     */
    public function getBrand(): string {
        return $this->brand;
    }

    /**
     * Retourne le modèle (ex: Clio)
     */
    public function getModel(): string {
        return $this->model;
    }

    /**
     * Retourne le nombre de sièges
     */
    public function getSeats(): int {
        return $this->seats;
    }

    /**
     * Retourne le statut de validation des documents
     */
    public function getDocumentsStatus(): string {
        return $this->documentsStatus;
    }

    /**
     * Retourne le chemin de la carte grise (ou null)
     */
    public function getRegistrationDocumentPath(): ?string {
        return $this->registrationDocument;
    }

    /**
     * Retourne le chemin de l’assurance (ou null)
     */
    public function getInsuranceDocumentPath(): ?string {
        return $this->insuranceDocument;
    }

    /**
     * Retourne le numéro d’immatriculation (ex: AB-123-CD)
     */
    public function getRegistrationNumber(): string {
        return $this->registrationNumber;
    }

    /**
     * Retourne le type de carburant (electric, diesel, etc.)
     */
    public function getFuelType(): string {
        return $this->fuelType;
    }

    /**
     * Retourne la date de première immatriculation
     */
    public function getFirstRegistrationDate(): string {
        return $this->firstRegistrationDate;
    }

    /**
     * Retourne la couleur du véhicule
     */
    public function getColor(): string {
        return $this->color;
    }
    /**
     * Retourne un libellé formaté du véhicule (ex: "Renault ZOE - 4 places")
     */
    public function getDisplayName(): string {
        return "{$this->brand} {$this->model} - {$this->seats} places";
    }
    /**
     * Retourne true si le vehicule est écologique
     */
    public function isEcological(): bool {
    return in_array(strtolower($this->fuelType), ['electric', 'hybrid']);
}


}
