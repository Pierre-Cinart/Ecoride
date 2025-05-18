<?php
// Composant pour convertir et enregistrer les images
require_once __DIR__ . '/../composants/uploader.php';
define('PROJECT_ROOT', dirname(__DIR__)); // remonte de 'classes' vers 'back'

class Vehicle {
    private PDO $pdo;
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
        $this->pdo = $pdo;
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

    public function getRegistrationDocumentPath(): ?string {
        return $this->registrationDocument ? PROJECT_ROOT . '/' . $this->registrationDocument : null;
    }

    public function getInsuranceDocumentPath(): ?string {
        return $this->insuranceDocument ? PROJECT_ROOT . '/' . $this->insuranceDocument : null;
    }

    public function getPicture(): ?string {
        return $this->picture ? PROJECT_ROOT . '/' . $this->picture : null;
    }

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
     * Upload un document lié au véhicule (photo, carte grise ou assurance).
     */
    public function uploadDocument(string $type, array $file): bool {
        try {
            $validTypes = ['picture', 'registration', 'insurance'];
            if (!in_array($type, $validTypes)) {
                throw new Exception("Type de document invalide : $type");
            }

            $backUrl = '/user/account.php';
            $typeOfPicture = ($type === 'picture') ? 'vehicle' : 'document';
            $typeOfDocument = in_array($type, ['registration', 'insurance']) ? $type : null;

            $imagePath = uploadImage(
                $this->pdo,
                $this->getOwner(),
                $file,
                $typeOfPicture,
                $backUrl,
                $this->id,
                $typeOfDocument
            );

            if (!$imagePath) {
                throw new Exception("Upload échoué ou chemin invalide.");
            }

            switch ($type) {
                case 'picture':
                    $this->picture = $imagePath;
                    $dbField = 'picture';
                    break;
                case 'registration':
                    $this->registrationDocument = $imagePath;
                    $dbField = 'registration_document';
                    break;
                case 'insurance':
                    $this->insuranceDocument = $imagePath;
                    $dbField = 'insurance_document';
                    break;
            }

            $this->documentsStatus = 'pending';

            $stmt = $this->pdo->prepare("UPDATE vehicles SET $dbField = :path, documents_status = 'pending' WHERE id = :id");
            $stmt->execute([
                ':path' => $imagePath,
                ':id'   => $this->id
            ]);

            return true;
        } catch (Exception $e) {
            error_log("Erreur Vehicle::uploadDocument() : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Supprime ce véhicule de la base après avoir annulé les trajets futurs liés.
     */
    public function deleteSelf(PDO $pdo, int $driverId): void {
        if ($driverId !== $this->owner) {
            throw new Exception("Erreur : vous n’êtes pas autorisé à supprimer ce véhicule.");
        }

        try {
            $stmt = $pdo->prepare("
                SELECT id 
                FROM trips 
                WHERE vehicle_id = :vehicle_id AND status = 'planned'
            ");
            $stmt->execute([':vehicle_id' => $this->id]);
            $tripIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

            $driver = $_SESSION['user'];

            foreach ($tripIds as $tripId) {
                $driver->cancelOwnTrip($pdo, (int) $tripId);
            }

            $paths = [
                $this->getRegistrationDocumentPath(),
                $this->getInsuranceDocumentPath(),
                $this->getPicture(),
            ];
            foreach ($paths as $path) {
                if ($path && file_exists($path)) {
                    @unlink($path);
                }
            }

            $stmtDelete = $pdo->prepare("DELETE FROM vehicles WHERE id = :id");
            $stmtDelete->execute([':id' => $this->id]);

        } catch (Exception $e) {
            throw new Exception("Erreur lors de la suppression du véhicule : " . $e->getMessage());
        }
    }
}
