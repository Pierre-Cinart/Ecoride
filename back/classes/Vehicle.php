<?php
// Composant pour convertir et enregistrer les images
require_once __DIR__ . '/../composants/uploader.php';

class Vehicle {
    private int $id;
    private int $owner;
    private string $brand;
    private string $model;
    private ?string $registrationDocument;
    private ?string $insuranceDocument;
    private ?string $picture;
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
        $this->owner                  = (int) $data['user_id'];
        $this->brand                  = $data['brand'];
        $this->model                  = $data['model'];
        $this->color                  = $data['color'];
        $this->fuelType               = $data['fuel_type'];
        $this->registrationNumber     = $data['registration_number'];
        $this->firstRegistrationDate  = $data['first_registration_date'];
        $this->seats                  = (int) $data['seats'];
        $this->registrationDocument   = $data['registration_document'];
        $this->insuranceDocument      = $data['insurance_document'];
        $this->picture                = $data['picture'] ?? null;
        $this->documentsStatus        = $data['documents_status'];
    }

    // === GETTERS ===
    public function getId(): int { return $this->id; }
    public function getOwner(): int { return $this->owner; }
    public function getBrand(): string { return $this->brand; }
    public function getModel(): string { return $this->model; }
    public function getSeats(): int { return $this->seats; }
    public function getDocumentsStatus(): string { return $this->documentsStatus; }
    public function getRegistrationDocumentPath(): ?string { return $this->registrationDocument; }
    public function getInsuranceDocumentPath(): ?string { return $this->insuranceDocument; }
    public function getPicture(): ?string { return $this->picture; }
    public function getRegistrationNumber(): string { return $this->registrationNumber; }
    public function getFuelType(): string { return $this->fuelType; }
    public function getFirstRegistrationDate(): string { return $this->firstRegistrationDate; }
    public function getColor(): string { return $this->color; }
    public function getDisplayName(): string {
        return "{$this->brand} {$this->model} - {$this->seats} places";
    }
    public function isEcological(): bool {
        return in_array(strtolower($this->fuelType), ['electric', 'hybrid']);
    }

    /**
     * Upload et met à jour la carte grise du véhicule
     */
    public function uploadRegistrationDocument(PDO $pdo, array $file): void {
        try {
            $path = Uploader::upload($file, [
                'targetDir' => 'uploads/vehicle_documents/registration/',
                'allowedExtensions' => ['jpg', 'jpeg', 'png', 'webp'],
                'resize' => true,
                'rename' => "vehicle_{$this->id}_registration"
            ]);

            $stmt = $pdo->prepare("UPDATE vehicles SET registration_document = :path, documents_status = 'pending' WHERE id = :id");
            $stmt->execute([':path' => $path, ':id' => $this->id]);

            $this->registrationDocument = $path;
            $this->documentsStatus = 'pending';
        } catch (Exception $e) {
            throw new Exception("Erreur lors de l'upload de la carte grise : " . $e->getMessage());
        }
    }

    /**
     * Upload et met à jour l'assurance du véhicule
     */
    public function uploadInsuranceDocument(PDO $pdo, array $file): void {
        try {
            $path = Uploader::upload($file, [
                'targetDir' => 'uploads/vehicle_documents/insurance/',
                'allowedExtensions' => ['jpg', 'jpeg', 'png', 'webp'],
                'resize' => true,
                'rename' => "vehicle_{$this->id}_insurance"
            ]);

            $stmt = $pdo->prepare("UPDATE vehicles SET insurance_document = :path, documents_status = 'pending' WHERE id = :id");
            $stmt->execute([':path' => $path, ':id' => $this->id]);

            $this->insuranceDocument = $path;
            $this->documentsStatus = 'pending';
        } catch (Exception $e) {
            throw new Exception("Erreur lors de l'upload de l'assurance : " . $e->getMessage());
        }
    }

    /**
     * Upload et met à jour la photo illustrative du véhicule
     */
    public function uploadPicture(PDO $pdo, array $file): void {
        try {
            $path = Uploader::upload($file, [
                'targetDir' => 'uploads/vehicle_documents/pictures/',
                'allowedExtensions' => ['jpg', 'jpeg', 'png', 'webp'],
                'resize' => true,
                'rename' => "vehicle_{$this->id}_picture"
            ]);

            $stmt = $pdo->prepare("UPDATE vehicles SET picture = :path WHERE id = :id");
            $stmt->execute([':path' => $path, ':id' => $this->id]);

            $this->picture = $path;
        } catch (Exception $e) {
            throw new Exception("Erreur lors de l'upload de la photo du véhicule : " . $e->getMessage());
        }
    }

    /**
     * Supprime ce véhicule de la base après avoir annulé les trajets futurs liés.
     * Utilise le Driver stocké en session pour annuler proprement les trajets associés,
     * supprime les fichiers liés (assurance, carte grise, photo), puis efface le véhicule de la base.
     *
     * @param PDO $pdo Connexion à la base
     * @param int $driverId ID du conducteur demandant la suppression
     * @throws Exception Si le propriétaire n’est pas valide ou autre erreur SQL/logique
     */
    public function deleteSelf(PDO $pdo, int $driverId): void {
        // 1. Vérification que le conducteur connecté est bien le propriétaire du véhicule
        if ($driverId !== $this->owner) {
            throw new Exception("Erreur : vous n’êtes pas autorisé à supprimer ce véhicule.");
        }

        try {
            // 2. Récupération des IDs des trajets encore planifiés liés à ce véhicule
            $stmt = $pdo->prepare("
                SELECT id 
                FROM trips 
                WHERE vehicle_id = :vehicle_id AND status = 'planned'
            ");
            $stmt->execute([':vehicle_id' => $this->id]);
            $tripIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // 3. Vérification de la session utilisateur et annulation propre de chaque trajet
            if (!isset($_SESSION['user']) || !$_SESSION['user'] instanceof Driver) {
                throw new Exception("Utilisateur non valide ou non connecté.");
            }

            /** @var Driver $driver */
            $driver = $_SESSION['user'];

            foreach ($tripIds as $tripId) {
                $driver->cancelOwnTrip($pdo, (int) $tripId);
            }

            // 4. Suppression des fichiers liés s’ils existent
            $paths = [
                $this->registrationDocument,
                $this->insuranceDocument,
                property_exists($this, 'picture') ? $this->picture : null
            ];

            foreach ($paths as $path) {
                if ($path && file_exists($path)) {
                    @unlink($path); // Supprime silencieusement le fichier
                }
            }

            // 5. Suppression finale du véhicule en base de données
            $stmtDelete = $pdo->prepare("DELETE FROM vehicles WHERE id = :id");
            $stmtDelete->execute([':id' => $this->id]);

        } catch (Exception $e) {
            throw new Exception("Erreur lors de la suppression du véhicule : " . $e->getMessage());
        }
    }


}
