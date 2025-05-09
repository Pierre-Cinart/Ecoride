<?php
// Classe parente User : toutes les autres classes hériteront de celle-ci
class User {
    protected $id;
    protected $pseudo;
    protected $firstName;
    protected $lastName;
    protected $email;
    protected $phoneNumber;
    protected $role;
    protected $credits;

    // Constructeur commun
    public function __construct($id, $pseudo, $firstName, $lastName, $email, $phoneNumber, $role, $credits = 0) {
        $this->id = $id;
        $this->pseudo = $pseudo;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->phoneNumber = $phoneNumber;
        $this->role = $role;
        $this->credits = $credits;
    }

    // Getters
    public function getFullName() {
        return $this->firstName . ' ' . $this->lastName;
    }
    public function getRole() {
        return $this->role;
    }
    public function getPseudo() {
        return $this->pseudo;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getCredits() {
        return $this->credits;
    }

    public function getId() {
        return $this->id;
    }

    public function reserv(PDO $pdo, int $tripId): bool {
        try {
            // 1. Vérifie que le trajet existe et est valable
            $stmt = $pdo->prepare("
                SELECT t.id, t.price, t.available_seats
                FROM trips t
                WHERE t.id = :id AND t.status = 'planned' AND t.available_seats > 0
            ");
            $stmt->execute([':id' => $tripId]);
            $trip = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if (!$trip) {
                throw new Exception("Trajet invalide ou complet.");
            }
    
            $price = (int)$trip['price'];
    
            // 2. Vérifie les crédits de l’utilisateur
            if ($this->credits < $price) {
                header("Location: ../../front/user/addCredits.php?error=credits");
                exit;
            }
    
            // 3. Vérifie que l'utilisateur n'est pas déjà inscrit à ce trajet
            $check = $pdo->prepare("SELECT id FROM trip_participants WHERE trip_id = :trip AND user_id = :user");
            $check->execute([
                ':trip' => $tripId,
                ':user' => $this->id
            ]);
            if ($check->fetch()) {
                throw new Exception("Déjà inscrit à ce trajet.");
            }
    
            // 4. Démarrer la transaction
            $pdo->beginTransaction();
    
            // 5. Enregistrement dans trip_participants
            $insert = $pdo->prepare("
                INSERT INTO trip_participants (trip_id, user_id, confirmed, credits_used, confirmation_date)
                VALUES (:trip, :user, 1, :credits, NOW())
            ");
            $insert->execute([
                ':trip' => $tripId,
                ':user' => $this->id,
                ':credits' => $price
            ]);
    
            // 6. Décrémente les places disponibles
            $updateTrip = $pdo->prepare("UPDATE trips SET available_seats = available_seats - 1 WHERE id = :trip");
            $updateTrip->execute([':trip' => $tripId]);
    
            // 7. Décrémente les crédits utilisateur
            $updateUser = $pdo->prepare("UPDATE users SET credits = credits - :credits WHERE id = :id");
            $updateUser->execute([
                ':credits' => $price,
                ':id' => $this->id
            ]);
    
            // 8. Commit & suppression de la variable temporaire
            $pdo->commit();
            unset($_SESSION['selectedTripId']);
    
            return true;
    
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            // Log ou afficher l’erreur pour debug : echo $e->getMessage();
            return false;
        }
    }
    
}
