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

    // Setters
    public function setCredits(int $credits): void {
        $this->credits = $credits;
    }

    // Réservation d'un trajet
    public function reserv(PDO $pdo, int $tripId): bool {
        try {
            $stmt = $pdo->prepare("SELECT t.id, t.price, t.available_seats FROM trips t WHERE t.id = :id AND t.status = 'planned' AND t.available_seats > 0 FOR UPDATE");
            $stmt->execute([':id' => $tripId]);
            $trip = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$trip) throw new Exception("Trajet invalide ou complet.");
            $price = (int)$trip['price'];
            if ($this->credits < $price) throw new Exception("Crédits insuffisants.");

            $check = $pdo->prepare("SELECT id FROM trip_participants WHERE trip_id = :trip AND user_id = :user");
            $check->execute([':trip' => $tripId, ':user' => $this->id]);
            if ($check->fetch()) throw new Exception("Vous avez déjà réservé ce trajet.");

            $pdo->beginTransaction();

            $insert = $pdo->prepare("INSERT INTO trip_participants (trip_id, user_id, confirmed, credits_used, confirmation_date) VALUES (:trip, :user, 1, :credits, NOW())");
            $insert->execute([':trip' => $tripId, ':user' => $this->id, ':credits' => $price]);

            $updateTrip = $pdo->prepare("UPDATE trips SET available_seats = available_seats - 1 WHERE id = :trip AND available_seats > 0");
            $updateTrip->execute([':trip' => $tripId]);
            if ($updateTrip->rowCount() === 0) throw new Exception("Plus de place disponible.");

            $updateUser = $pdo->prepare("UPDATE users SET credits = credits - :credits WHERE id = :id");
            $updateUser->execute([':credits' => $price, ':id' => $this->id]);

            $pdo->commit();
            unset($_SESSION['tripPending']);
            $this->setCredits($this->credits - $price);

            return true;

        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $_SESSION['error'] = $e->getMessage();
            return false;
        }
    }

    // Annulation d'un trajet réservé (passager)
    public function cancelTrip(PDO $pdo, int $tripId, int $participantId, int $creditsUsed): bool {
        try {
            $penalite = 2;
            $remboursement = max(0, $creditsUsed - $penalite);

            $pdo->beginTransaction();

            // Mise à jour de la participation (annulation)
            $updateParticipant = $pdo->prepare("UPDATE trip_participants SET confirmed = 0, confirmation_date = NOW() WHERE id = :id AND user_id = :user");
            $updateParticipant->execute([':id' => $participantId, ':user' => $this->id]);
            if ($updateParticipant->rowCount() === 0) throw new Exception("Annulation impossible : réservation introuvable.");

            // Libérer une place sur le trajet
            $updateTrip = $pdo->prepare("UPDATE trips SET available_seats = available_seats + 1 WHERE id = :trip");
            $updateTrip->execute([':trip' => $tripId]);

            // Créditer l'utilisateur (avec pénalité)
            $updateUser = $pdo->prepare("UPDATE users SET credits = credits + :credits WHERE id = :id");
            $updateUser->execute([':credits' => $remboursement, ':id' => $this->id]);

            $pdo->commit();
            $this->setCredits($this->credits + $remboursement);

            return true;

        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $_SESSION['error'] = $e->getMessage();
            return false;
        }
    }
}
