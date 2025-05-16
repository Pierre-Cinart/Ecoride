<?php
/**
 * Classe abstraite User
 * Représente un utilisateur générique (passager, conducteur, etc.)
 * Toutes les autres classes (Driver, Admin, etc.) hériteront de celle-ci.
 */
abstract class User {
    // === PROPRIÉTÉS PROTÉGÉES ===

    protected int $id;
    protected string $pseudo;
    protected string $firstName;
    protected string $lastName;
    protected string $email;
    protected string $phoneNumber;
    protected string $role;
    protected int $credits;
    protected string $status;
    protected int $userWarnings;

    protected ?string $birthdate;        // Date de naissance (YYYY-MM-DD)
    protected ?string $gender;           // Sexe : 'male' ou 'female'
    protected ?string $profilPicture;    // Chemin de la photo de profil

    // === CONSTRUCTEUR ===

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
        string $birthdate ,
        string $gender ,
        ?string $profilPicture = null
    ) {
        $this->id = $id;
        $this->pseudo = $pseudo;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->phoneNumber = $phoneNumber;
        $this->role = $role;
        $this->credits = $credits;
        $this->status = $status;
        $this->userWarnings = $userWarnings;
        $this->permitStatus = $permitStatus;
        $this->birthdate = $birthdate;
        $this->gender = $gender;
        $this->profilPicture = $profilPicture;
    }

    // === GETTERS ===

    public function getId(): int {
        return $this->id;
    }

    public function getPseudo(): string {
        return $this->pseudo;
    }

    public function getFullName(): string {
        return $this->firstName . ' ' . $this->lastName;
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function getPhoneNumber(): string {
        return $this->phoneNumber;
    }

    public function getRole(): string {
        return $this->role;
    }

    public function getCredits(): int {
        return $this->credits;
    }

    public function getStatus(): string {
        return $this->status;
    }

    public function getUserWarnings(): int {
        return $this->userWarnings;
    }
    public function getPermitStatus () {
        return $this->permitStatus;
    }
    public function getBirthdate(): ?string {
        return $this->birthdate;
    }

    public function getGender(): ?string {
        return $this->gender;
    }

    public function getProfilPicture(): ?string {
        return $this->profilPicture;
    }

    // === MISE À JOUR SESSION ===

    public function updateUserSession(PDO $pdo): void {
        try {
            $stmt = $pdo->prepare("SELECT pseudo, first_name, last_name, email, phone_number, role, credits, birthdate, gender, profil_picture FROM users WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $this->id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($data) {
                $this->pseudo = $data['pseudo'];
                $this->firstName = $data['first_name'];
                $this->lastName = $data['last_name'];
                $this->email = $data['email'];
                $this->phoneNumber = $data['phone_number'];
                $this->role = $data['role'];
                $this->credits = (int) $data['credits'];
                $this->permitStatus = $data['permit_status'];
                $this->birthdate = $data['birthdate'];
                $this->gender = $data['gender'];
                $this->profilPicture = $data['profil_picture'];

                $_SESSION['user'] = $this;
            }

        } catch (Exception $e) {
            $_SESSION['error'] = "Erreur lors de la mise à jour des informations utilisateur.";
        }
    }
}
?>
