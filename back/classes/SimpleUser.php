<?php
require_once 'User.php';

// Classe utilisateur simple (non conducteur)
class SimpleUser extends User {
     // ===== METHODS:  =====
 
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
    /**
     * ===== Réservation d’un trajet (avec paiement immédiat) =====
     *
     * @param PDO $pdo Connexion PDO à la base de données
     * @param int $tripId ID du trajet à réserver
     * @return bool true si la réservation est réussie, false sinon
     */
    public function reserveTrip(PDO $pdo, int $tripId): bool {
        try {
            //  0. Vérifie si l'utilisateur est autorisé à réserver
            if ($this->status != 'authorized') {
                $_SESSION['error'] = "Vous n’avez pas la permission de réserver un voyage. Veuillez contacter l’équipe d’EcoRide.";
                return false;
            }

            $pdo->beginTransaction();

            // 1. Lecture et verrouillage du trajet pour éviter les conflits simultanés
            $stmt = $pdo->prepare("
                SELECT id, price, available_seats, status 
                FROM trips 
                WHERE id = :id 
                FOR UPDATE
            ");
            $stmt->execute([':id' => $tripId]);
            $trip = $stmt->fetch(PDO::FETCH_ASSOC);

            // Trajet inexistant, terminé ou complet
            if (!$trip || $trip['status'] !== 'planned' || $trip['available_seats'] <= 0) {
                throw new Exception("Ce trajet n’est pas disponible.");
            }

            $price = (int) $trip['price'];

            //  Vérifie les crédits de l’utilisateur
            if ($this->credits < $price) {
                throw new Exception("Crédits insuffisants.");
            }

            // 2. Vérifie si déjà inscrit à ce trajet
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

            // 3. Réservation ou réactivation d’une réservation annulée
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

            // 4. Mise à jour du nombre de places disponibles
            $updateTrip = $pdo->prepare("
                UPDATE trips 
                SET available_seats = available_seats - 1 
                WHERE id = :trip AND available_seats > 0
            ");
            $updateTrip->execute([':trip' => $tripId]);

            // Conflit sur les places disponibles
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
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $_SESSION['error'] = $e->getMessage();
            return false;
        }
    }


   /**
 * Permet à un utilisateur d’annuler sa réservation à un voyage.
 * Applique une pénalité, rembourse partiellement, ajoute un avertissement si annulation tardive.
 * Bloque l’utilisateur si 3 avertissements sont atteints.
 *
 * @param PDO $pdo Connexion active à la base
 * @param int $tripId ID du voyage concerné
 * @param int $participantId ID du participant (dans la table `trip_participants`)
 * @param int $creditsUsed Crédits initialement dépensés
 * @param string $logMessage Message à enregistrer pour l’historique
 * @return bool True si l’opération s’est bien passée, False sinon
 */
    public function cancelTrip(PDO $pdo, int $tripId, int $participantId, int $creditsUsed, string $logMessage): bool {
        try {
            $penalite = 2; // Coût fixe pour une annulation
            $remboursement = max(0, $creditsUsed - $penalite);

            $pdo->beginTransaction();

            // 1. Marquer le participant comme annulé
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

            // 2. Vérifie si l’annulation est tardive (< 24h avant départ)
            $stmt = $pdo->prepare("SELECT departure_date FROM trips WHERE id = :tripId");
            $stmt->execute([':tripId' => $tripId]);
            $trip = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($trip) {
                $dateDepart = new DateTime($trip['departure_date']);
                $now = new DateTime();

                $diffInHours = ($dateDepart->getTimestamp() - $now->getTimestamp()) / 3600;

                if ($diffInHours > 0 && $diffInHours < 24) {
                    // 3. Ajout d’un avertissement
                    $warnUpdate = $pdo->prepare("UPDATE users SET user_warnings = user_warnings + 1 WHERE id = :id");
                    $warnUpdate->execute([':id' => $this->id]);

                    // 4. Enregistrement des frais dans la table transactions
                    $pdo->prepare("
                        INSERT INTO transactions (user_id, credits, type, description, created_at)
                        VALUES (:uid, :credits, 'fee', :desc, NOW())
                    ")->execute([
                        ':uid' => $this->id,
                        ':credits' => $penalite,
                        ':desc' => "Frais d’annulation pour le trajet ID $tripId"
                    ]);

                    // 5. Blocage si 3 avertissements atteints
                    $checkWarns = $pdo->prepare("SELECT user_warnings FROM users WHERE id = :id");
                    $checkWarns->execute([':id' => $this->id]);
                    $result = $checkWarns->fetch(PDO::FETCH_ASSOC);

                    if ($result && (int)$result['user_warnings'] >= 3) {
                        $pdo->prepare("UPDATE users SET status = 'blocked' WHERE id = :id")
                            ->execute([':id' => $this->id]);
                    }
                }
            }

            // 6. Libère une place sur le trajet
            $updateTrip = $pdo->prepare("
                UPDATE trips SET available_seats = available_seats + 1 
                WHERE id = :trip
            ");
            $updateTrip->execute([':trip' => $tripId]);

            // 7. Enregistre un remboursement si applicable
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


