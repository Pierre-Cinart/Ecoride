<?php
// Classe parente User : toutes les autres classes hériteront de celle-ci
class User {
    // Attributs protégés (accessibles aux classes filles)
    protected $id;
    protected $pseudo;
    protected $firstName;
    protected $lastName;
    protected $email;
    protected $phoneNumber;
    protected $role;
    protected $credits;

    // Constructeur
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

    // ----- GETTERS -----
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

    // ----- SETTERS -----
    // Setter interne (modifie la valeur en mémoire, sans toucher à la base de données)
    public function setCredits(int $credits): void {
        $this->credits = $credits;
    }

    // Mise à jour des crédits (BDD + objet)
    public function updateCredits(PDO $pdo, int $delta): bool {
        try {
            $stmt = $pdo->prepare("UPDATE users SET credits = credits + :delta WHERE id = :id");
            $stmt->execute([':delta' => $delta, ':id' => $this->id]);

            $this->credits += $delta;
            return true;
        } catch (Exception $e) {
            $_SESSION['error'] = "Erreur lors de la mise à jour des crédits.";
            return false;
        }
    }

    // ----- Réservation d'un trajet -----
    public function reserv(PDO $pdo, int $tripId): bool {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("SELECT id, price, available_seats, status FROM trips WHERE id = :id FOR UPDATE");
            $stmt->execute([':id' => $tripId]);
            $trip = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$trip || $trip['status'] !== 'planned' || $trip['available_seats'] <= 0) {
                throw new Exception("Ce trajet n’est pas disponible.");
            }

            $price = (int)$trip['price'];

            if ($this->credits < $price) {
                throw new Exception("Crédits insuffisants.");
            }

            $check = $pdo->prepare("SELECT id, confirmed FROM trip_participants WHERE trip_id = :trip AND user_id = :user");
            $check->execute([':trip' => $tripId, ':user' => $this->id]);
            $existing = $check->fetch(PDO::FETCH_ASSOC);

            if ($existing && $existing['confirmed']) {
                throw new Exception("Vous avez déjà réservé ce trajet.");
            }

            if ($existing && !$existing['confirmed']) {
                $update = $pdo->prepare("UPDATE trip_participants SET confirmed = 1, confirmation_date = NOW(), credits_used = :credits WHERE id = :id");
                $update->execute([':credits' => $price, ':id' => $existing['id']]);
            } else {
                $insert = $pdo->prepare("INSERT INTO trip_participants (trip_id, user_id, confirmed, credits_used, confirmation_date) VALUES (:trip, :user, 1, :credits, NOW())");
                $insert->execute([
                    ':trip' => $tripId,
                    ':user' => $this->id,
                    ':credits' => $price
                ]);
            }

            $updateTrip = $pdo->prepare("UPDATE trips SET available_seats = available_seats - 1 WHERE id = :trip AND available_seats > 0");
            $updateTrip->execute([':trip' => $tripId]);
            if ($updateTrip->rowCount() === 0) {
                throw new Exception("Plus de places disponibles.");
            }

            if (!$this->updateCredits($pdo, -$price)) {
                throw new Exception("Erreur lors du débit des crédits.");
            }

            $pdo->commit();
            unset($_SESSION['tripPending']);
            return true;

        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $_SESSION['error'] = $e->getMessage();
            return false;
        }
    }

    // ----- Annulation d'un trajet -----
    public function cancelTrip(PDO $pdo, int $tripId, int $participantId, int $creditsUsed): bool {
        try {
            $penalite = 2;
            $remboursement = max(0, $creditsUsed - $penalite);

            $pdo->beginTransaction();

            $updateParticipant = $pdo->prepare("UPDATE trip_participants SET confirmed = 0, confirmation_date = NOW() WHERE id = :id AND user_id = :user");
            $updateParticipant->execute([':id' => $participantId, ':user' => $this->id]);
            if ($updateParticipant->rowCount() === 0) throw new Exception("Annulation impossible : réservation introuvable.");

            $updateTrip = $pdo->prepare("UPDATE trips SET available_seats = available_seats + 1 WHERE id = :trip");
            $updateTrip->execute([':trip' => $tripId]);

            if (!$this->updateCredits($pdo, $remboursement)) {
                throw new Exception("Erreur lors du remboursement.");
            }

            $pdo->commit();
            return true;

        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $_SESSION['error'] = $e->getMessage();
            return false;
        }
    }
}
