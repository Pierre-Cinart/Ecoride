<?php
require_once 'User.php';

// Classe utilisateur simple (non conducteur)
class SimpleUser extends User {
     // ===== METHODS:  =====
     // ===== Met à jour les données de l'utilisateur en session =====
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

    // ===== Mise à jour des crédits (débite ou crédite l’utilisateur) =====
    public function updateCredits(PDO $pdo, int $delta): bool {
        try {
            $stmt = $pdo->prepare("UPDATE users SET credits = credits + :delta WHERE id = :id");
            $stmt->execute([':delta' => $delta, ':id' => $this->id]);

            $this->credits += $delta; // Mise à jour en mémoire
            return true;
        } catch (Exception $e) {
            $_SESSION['error'] = "Erreur lors de la mise à jour des crédits.";
            return false;
        }
    }
    
    // ===== Demande de remboursement (log uniquement) =====
    public function logCashback(PDO $pdo, int $credits, string $reason, string $type = 'refund'): bool {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO cashback (user_id, type, credits_requested, reason)
                VALUES (:uid, :type, :credits, :reason)
            ");
            $stmt->execute([
                ':uid' => $this->id,
                ':type' => $type,
                ':credits' => $credits,
                ':reason' => $reason
            ]);
            return true;
        } catch (Exception $e) {
            $_SESSION['error'] = "Erreur lors de l'enregistrement dans le journal de cashback.";
            return false;
        }
    }
    // ===== Réservation d’un trajet (avec paiement immédiat) =====
    public function reserveTrip(PDO $pdo, int $tripId): bool {
        try {
            $pdo->beginTransaction();

            // 1. Lecture et verrouillage du trajet
            $stmt = $pdo->prepare("
                SELECT id, price, available_seats, status 
                FROM trips 
                WHERE id = :id 
                FOR UPDATE
            ");
            $stmt->execute([':id' => $tripId]);
            $trip = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$trip || $trip['status'] !== 'planned' || $trip['available_seats'] <= 0) {
                throw new Exception("Ce trajet n’est pas disponible.");
            }

            $price = (int) $trip['price'];

            if ($this->credits < $price) {
                throw new Exception("Crédits insuffisants.");
            }

            // 2. Vérifie si déjà enregistré
            $check = $pdo->prepare("
                SELECT id, confirmed 
                FROM trip_participants 
                WHERE trip_id = :trip AND user_id = :user
            ");
            $check->execute([
                ':trip' => $tripId,
                ':user' => $this->id
            ]);
            $existing = $check->fetch(PDO::FETCH_ASSOC);

            // 3. Insertion ou mise à jour dans trip_participants
            if ($existing && $existing['confirmed']) {
                throw new Exception("Vous avez déjà réservé ce trajet.");
            }

            if ($existing && !$existing['confirmed']) {
                $update = $pdo->prepare("
                    UPDATE trip_participants 
                    SET confirmed = 1, confirmation_date = NOW(), credits_used = :credits 
                    WHERE id = :id
                ");
                $update->execute([
                    ':credits' => $price,
                    ':id' => $existing['id']
                ]);
            } else {
                $insert = $pdo->prepare("
                    INSERT INTO trip_participants (trip_id, user_id, confirmed, credits_used, confirmation_date)
                    VALUES (:trip, :user, 1, :credits, NOW())
                ");
                $insert->execute([
                    ':trip' => $tripId,
                    ':user' => $this->id,
                    ':credits' => $price
                ]);
            }

            // 4. Décrémente les places disponibles
            $updateTrip = $pdo->prepare("
                UPDATE trips 
                SET available_seats = available_seats - 1 
                WHERE id = :trip AND available_seats > 0
            ");
            $updateTrip->execute([':trip' => $tripId]);

            if ($updateTrip->rowCount() === 0) {
                throw new Exception("Plus de places disponibles.");
            }

            // 5. Débit des crédits utilisateur
            if (!$this->updateCredits($pdo, -$price)) {
                throw new Exception("Erreur lors du débit des crédits.");
            }

            $pdo->commit();
            return true;

        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $_SESSION['error'] = $e->getMessage();
            return false;
        }
    }

    // ===== Annulation d'un trajet (log cashback, sans remboursement immédiat) =====
    public function cancelTrip(PDO $pdo, int $tripId, int $participantId, int $creditsUsed, string $logMessage): bool {
        try {
            $penalite = 2;
            $remboursement = max(0, $creditsUsed - $penalite);

            $pdo->beginTransaction();

            // 1. Marque le participant comme annulé
            $updateParticipant = $pdo->prepare("
                UPDATE trip_participants 
                SET confirmed = 0, confirmation_date = NOW() 
                WHERE id = :id AND user_id = :user
            ");
            $updateParticipant->execute([
                ':id' => $participantId,
                ':user' => $this->id
            ]);

            if ($updateParticipant->rowCount() === 0) {
                throw new Exception("Annulation impossible : réservation introuvable.");
            }

            // 2. Libère une place sur le trajet
            $updateTrip = $pdo->prepare("
                UPDATE trips SET available_seats = available_seats + 1 
                WHERE id = :trip
            ");
            $updateTrip->execute([':trip' => $tripId]);

            // 3. Enregistre une demande de remboursement (log uniquement)
            if ($remboursement > 0) {
                $reason = $logMessage ?: "Annulation du trajet #$tripId";
                if (!$this->logCashback($pdo, $remboursement, $reason, 'refund')) {
                    throw new Exception("Erreur lors de l'enregistrement du remboursement.");
                }
            }

            $pdo->commit();
            return true;

        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $_SESSION['error'] = $e->getMessage();
            return false;
        }
    }

    // ===== Laisse un avis ou le met à jour si existant =====
    public function leaveReview(PDO $pdo, int $tripId, float $rating, ?string $comment = null): bool {
        try {
            // 1. Vérifie la participation confirmée
            $stmt = $pdo->prepare("
                SELECT COUNT(*) 
                FROM trip_participants 
                WHERE trip_id = :trip AND user_id = :user AND confirmed = 1
            ");
            $stmt->execute([
                ':trip' => $tripId,
                ':user' => $this->id
            ]);

            if ($stmt->fetchColumn() == 0) {
                $_SESSION['error'] = "Vous n’avez pas participé à ce trajet.";
                return false;
            }

            // 2. Vérifie si un avis existe déjà
            $stmtExists = $pdo->prepare("
                SELECT id, status 
                FROM ratings 
                WHERE author_id = :user AND trip_id = :trip
            ");
            $stmtExists->execute([
                ':user' => $this->id,
                ':trip' => $tripId
            ]);
            $existing = $stmtExists->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                if (in_array($existing['status'], ['waiting', 'pending'])) {
                    // Mise à jour de l'avis existant
                    $update = $pdo->prepare("
                        UPDATE ratings 
                        SET rating = :rating, comment = :comment, status = 'pending', created_at = NOW() 
                        WHERE id = :id
                    ");
                    $update->execute([
                        ':rating' => $rating,
                        ':comment' => $comment ?: null,
                        ':id' => $existing['id']
                    ]);

                    $_SESSION['success'] = "Avis mis à jour. En attente de validation.";
                    return true;

                } else {
                    $_SESSION['error'] = "Vous avez déjà un avis validé pour ce trajet.";
                    return false;
                }
            } else {
                // Insertion d'un nouvel avis
                $insert = $pdo->prepare("
                    INSERT INTO ratings (author_id, trip_id, rating, comment, status, created_at)
                    VALUES (:author, :trip, :rating, :comment, 'pending', NOW())
                ");
                $insert->execute([
                    ':author' => $this->id,
                    ':trip' => $tripId,
                    ':rating' => $rating,
                    ':comment' => $comment ?: null
                ]);

                $_SESSION['success'] = "Merci pour votre avis ! Il sera publié après validation.";
                return true;
            }

        } catch (Exception $e) {
            $_SESSION['error'] = "Erreur lors de l'enregistrement de l'avis.";
            return false;
        }
    }

    // ===== Supprime le contenu d’un avis (passe en statut "deleted") =====
    public function deleteReview(PDO $pdo, int $ratingId): bool {
        try {
            // Vérifie que l'avis appartient bien à l'utilisateur
            $stmt = $pdo->prepare("
                SELECT id 
                FROM ratings 
                WHERE id = :id AND author_id = :uid
            ");
            $stmt->execute([
                ':id' => $ratingId,
                ':uid' => $this->id
            ]);

            if (!$stmt->fetch()) {
                $_SESSION['error'] = "Avis non trouvé ou non autorisé.";
                return false;
            }

            // Mise à jour : suppression du commentaire et changement de statut
            $update = $pdo->prepare("
                UPDATE ratings 
                SET comment = NULL, status = 'deleted' 
                WHERE id = :id
            ");
            $update->execute([':id' => $ratingId]);

            $_SESSION['success'] = "Votre commentaire a bien été supprimé.";
            return true;

        } catch (Exception $e) {
            $_SESSION['error'] = "Erreur lors de la suppression de l'avis.";
            return false;
        }
    }
    // ===== Envoie de mail =====
    public function sendMail() {
        //  à develloper
    }

}


