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

     ///////////////////////////////////////////////////////
            //            GETTERS        //
    //////////////////////////////////////////////////////
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

   
    ///////////////////////////////////////////////////////
            //            SETTERS        //
    //////////////////////////////////////////////////////

    public function setCredits(int $credits): void {
        $this->credits = $credits;
    }

    ///////////////////////////////////////////////////////
            //            METHODS        //
    //////////////////////////////////////////////////////

    // Réservation
    public function reserv(PDO $pdo, int $tripId): bool {
        try {
            // 1. Vérifie que le trajet existe et est valable
            $stmt = $pdo->prepare("
                SELECT t.id, t.price, t.available_seats
                FROM trips t
                WHERE t.id = :id AND t.status = 'planned' AND t.available_seats > 0
                FOR UPDATE
            ");
            $stmt->execute([':id' => $tripId]);
            $trip = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$trip) {
                throw new Exception("Trajet invalide ou complet.");
            }

            $price = (int)$trip['price'];

            // 2. Vérifie les crédits
            if ($this->credits < $price) {
                throw new Exception("Crédits insuffisants.");
            }

            // 3. Vérifie doublon
            $check = $pdo->prepare("SELECT id FROM trip_participants WHERE trip_id = :trip AND user_id = :user");
            $check->execute([':trip' => $tripId, ':user' => $this->id]);
            if ($check->fetch()) {
                throw new Exception("Vous avez déjà réservé ce trajet.");
            }

            // 4. Transaction
            $pdo->beginTransaction();

            // 5. Insert dans trip_participants
            $insert = $pdo->prepare("
                INSERT INTO trip_participants (trip_id, user_id, confirmed, credits_used, confirmation_date)
                VALUES (:trip, :user, 1, :credits, NOW())
            ");
            $insert->execute([
                ':trip' => $tripId,
                ':user' => $this->id,
                ':credits' => $price
            ]);

            // 6. Décrémenter les places
            $updateTrip = $pdo->prepare("UPDATE trips SET available_seats = available_seats - 1 WHERE id = :trip AND available_seats > 0");
            $updateTrip->execute([':trip' => $tripId]);

            if ($updateTrip->rowCount() === 0) {
                throw new Exception("Plus de place disponible.");
            }

            // 7. Décrémenter crédits
            $updateUser = $pdo->prepare("UPDATE users SET credits = credits - :credits WHERE id = :id");
            $updateUser->execute([
                ':credits' => $price,
                ':id' => $this->id
            ]);

            // 8. Commit + session
            $pdo->commit();
            unset($_SESSION['tripPending']);

            // Met à jour l'objet en mémoire
            $this->setCredits($this->credits - $price);

            return true;

        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $_SESSION['error'] = $e->getMessage(); // Utile pour retour interface
            return false;
        }
    }
}
