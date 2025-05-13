<?php

class Vehicle {
    private int $id;
    private string $brand;
    private string $model;
    private ?string $registrationDocument;
    private ?string $insuranceDocument;
    private string $documentsStatus;

    public function __construct(PDO $pdo, int $id) {
        $stmt = $pdo->prepare("SELECT * FROM vehicles WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            throw new Exception("Véhicule introuvable.");
        }

        $this->id = (int) $data['id'];
        $this->brand = $data['brand'];
        $this->model = $data['model'];
        $this->registrationDocument = $data['registration_document'];
        $this->insuranceDocument = $data['insurance_document'];
        $this->documentsStatus = $data['documents_status'];
        
    }

    public function getDocumentsStatus(): string {
        return $this->documentsStatus;
    }

    public function getRegistrationDocumentPath(): ?string {
        return $this->registrationDocument;
    }

    public function getInsuranceDocumentPath(): ?string {
        return $this->insuranceDocument;
    }

   
}

