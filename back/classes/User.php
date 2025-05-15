<?php
/**
 * Classe abstraite User
 * Représente un utilisateur générique (passager, conducteur, etc.)
 * Toutes les autres classes (Driver, Admin, etc.) hériteront de celle-ci.
 */
abstract class User {
    // === PROPRIÉTÉS PROTÉGÉES (accessibles dans les classes filles) ===

    /** @var int Identifiant unique de l'utilisateur */
    protected int $id;

    /** @var string Pseudo choisi par l'utilisateur */
    protected string $pseudo;

    /** @var string Prénom de l'utilisateur */
    protected string $firstName;

    /** @var string Nom de famille de l'utilisateur */
    protected string $lastName;

    /** @var string Adresse e-mail */
    protected string $email;

    /** @var string Numéro de téléphone */
    protected string $phoneNumber;

    /** @var string Rôle (user, driver, employee, admin) */
    protected string $role;

    /** @var int Nombre de crédits disponibles */
    protected int $credits;

    /** @var string Statut global du compte (authorized, drive_blocked, blocked, banned) */
    protected string $status;

    /** @var int Nombre d'avertissements en tant qu'utilisateur/passager */
    protected int $userWarnings;

    // === CONSTRUCTEUR ===

    /**
     * Initialise un nouvel utilisateur avec ses données de base.
     *
     * @param int $id Identifiant utilisateur
     * @param string $pseudo Pseudo
     * @param string $firstName Prénom
     * @param string $lastName Nom
     * @param string $email Email
     * @param string $phoneNumber Numéro de téléphone
     * @param string $role Rôle de l'utilisateur
     * @param int $credits Crédits initiaux
     * @param string $status Statut du compte (default: 'authorized')
     * @param int $userWarnings Nombre d'avertissements utilisateur (default: 0)
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
        string $status ,
        int $userWarnings
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
    }

    // === GETTERS (accesseurs publics) ===

    /** @return int ID utilisateur */
    public function getId(): int {
        return $this->id;
    }

    /** @return string Pseudo */
    public function getPseudo(): string {
        return $this->pseudo;
    }

    /** @return string Nom complet (prénom + nom) */
    public function getFullName(): string {
        return $this->firstName . ' ' . $this->lastName;
    }

    /** @return string Email */
    public function getEmail(): string {
        return $this->email;
    }

    /** @return string Numéro de téléphone */
    public function getPhoneNumber(): string {
        return $this->phoneNumber;
    }

    /** @return string Rôle de l'utilisateur (user, driver, etc.) */
    public function getRole(): string {
        return $this->role;
    }

    /** @return int Crédits disponibles */
    public function getCredits(): int {
        return $this->credits;
    }

    /** @return string Statut global de l'utilisateur */
    public function getStatus(): string {
        return $this->status;
    }

    /** @return int Nombre d'avertissements utilisateur */
    public function getUserWarnings(): int {
        return $this->userWarnings;
    }

    // === MÉTHODE À SURCHARGER DANS LES CLASSES ENFANTS ===

    /**
     * Méthode générique pour mettre à jour les données utilisateur.
     * Elle sera définie dans les sous-classes (ex : Driver, Admin, etc.)
     *
     * @param PDO $pdo Connexion à la base de données
     */
        // ===== Met à jour les données de l'utilisateur en session ( ne pas surcharger dans SimpleUser ) =====
        public function updateUserSession(PDO $pdo): void {
        try {
            // Requête pour récupérer les infos de l'utilisateur à jour
            $stmt = $pdo->prepare("SELECT pseudo, first_name, last_name, email, phone_number, role, credits FROM users WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $this->id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($data) {
                // Mise à jour des propriétés de l'objet
                $this->pseudo       = $data['pseudo'];
                $this->firstName    = $data['first_name'];
                $this->lastName     = $data['last_name'];
                $this->email        = $data['email'];
                $this->phoneNumber  = $data['phone_number'];
                $this->role         = $data['role'];
                $this->credits      = (int) $data['credits'];

                // Réinjection dans la session
                $_SESSION['user'] = $this;
            }

        } catch (Exception $e) {
            $_SESSION['error'] = "Erreur lors de la mise à jour des informations utilisateur.";
        }
    }

}
