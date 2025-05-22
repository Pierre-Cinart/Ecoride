<?php

require_once 'User.php';
require_once __DIR__ . '/../composants/loggerFirebase.php';
/**
 * Classe représentant un employé EcoRide.
 * Hérite de la classe User.
 */
class Employee extends User
{
 

    /**
     * Permet à l'employé de gérer (modérer) un commentaire utilisateur.
     *
     * @param PDO $pdo Connexion PDO à la base de données
     * @param int $reviewId ID de l'avis à traiter
     * @param string $action Action à effectuer : 'approve', 'refused' ou 'delete'
     * @return bool True si la requête a réussi, False sinon
     */
    public function manageReview(PDO $pdo, int $reviewId, string $action): bool
    {
        $status = match ($action) {
            'approve' => 'accepted',
            'refused' => 'refused',
            'delete'  => 'deleted',
            default   => null
        };

        if ($status === null) return false;

        $stmt = $pdo->prepare("UPDATE ratings SET status = :status WHERE id = :id");
        $stmt->bindValue(':status', $status);
        $stmt->bindValue(':id', $reviewId, PDO::PARAM_INT);
        return $stmt->execute();
    }
    /**:
     * déblocage passager
     */
    public function unblockUser(PDO $pdo, int $userId): void {
        $stmt = $pdo->prepare("SELECT status, user_warnings , pseudo FROM users WHERE id = :id");
        $stmt->execute([':id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $_SESSION['error'] = "Utilisateur introuvable.";
            return;
        }

        $status = $user['status'];
        $warnings = (int)$user['user_warnings'];
        $pseudo = $user['pseudo'];

        if ($status === 'banned') {
            $_SESSION['error'] = "Cet utilisateur est banni. Déblocage impossible.";
            return;
        }

        if (!in_array($status, ['blocked', 'all_blocked'])) {
            $_SESSION['error'] = "Cet utilisateur n’est pas bloqué.";
            return;
        }

        // Mise à jour du statut
        $newStatus = ($status === 'all_blocked') ? 'drive_blocked' : 'authorized';

        // Gestion des avertissements
        if ($warnings < 10) {
            $warnings = 10;
        } elseif ($warnings < 20) {
            $warnings = 20;
        } elseif ($warnings >= 23) {
            $_SESSION['error'] = "Trop d'avertissements. Cet utilisateur est définitivement banni.";
            $pdo->prepare("UPDATE users SET status = 'banned' WHERE id = :id")->execute([':id' => $userId]);
            return;
        }

        // Mise à jour finale
        $stmt = $pdo->prepare("UPDATE users SET status = :status, user_warnings = :warnings WHERE id = :id");
        $stmt->execute([
            ':status' => $newStatus,
            ':warnings' => $warnings,
            ':id' => $userId
        ]);

            // trace log 
            logActionToFirebase($this->getFullName(), 'Déblocage de l’utilisateur : ' . $pseudo);
             $_SESSION['success'] = "L'utilisateur a bien été débloqué. action log";
      
    }

     /**:
     * déblocage conducteur
     */
    public function unblockDriver(PDO $pdo, int $userId): void {
        $stmt = $pdo->prepare("SELECT status, user_warnings , pseudo FROM users WHERE id = :id");
        $stmt->execute([':id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $_SESSION['error'] = "Utilisateur introuvable.";
            return;
        }

        $status = $user['status'];
        $warnings = (int)$user['user_warnings'];
        $pseudo = $user['pseudo'];

        if ($status === 'banned') {
            $_SESSION['error'] = "Cet utilisateur est banni. Déblocage impossible.";
            return;
        }

        if (!in_array($status, ['drive_blocked', 'all_blocked'])) {
            $_SESSION['error'] = "Ce conducteur n’est pas bloqué.";
            return;
        }

        // Mise à jour du statut
        $newStatus = ($status === 'all_blocked') ? 'blocked' : 'authorized';

        // Gestion des avertissements
        if ($warnings < 10) {
            $warnings = 10;
        } elseif ($warnings < 20) {
            $warnings = 20;
        } elseif ($warnings >= 23) {
            $_SESSION['error'] = "Trop d'avertissements. Cet utilisateur est définitivement banni.";
            $pdo->prepare("UPDATE users SET status = 'banned' WHERE id = :id")->execute([':id' => $userId]);
            return;
        }

        // Mise à jour finale
        $stmt = $pdo->prepare("UPDATE users SET status = :status, user_warnings = :warnings WHERE id = :id");
        $stmt->execute([
            ':status' => $newStatus,
            ':warnings' => $warnings,
            ':id' => $userId
        ]);

         // trace log 
        logActionToFirebase($this->getFullName(), 'Déblocage du conducteur : ' . $pseudo);
        $_SESSION['success'] = "Le conducteur a bien été débloqué.";
    }

     /**:
     * Blocage total utilisateurs
     */
    public function blockUserCompletely(PDO $pdo, int $userId): void {
        $stmt = $pdo->prepare("SELECT status , pseudo  FROM users WHERE id = :id");
        $stmt->execute([':id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $_SESSION['error'] = "Utilisateur introuvable.";
            return;
        }

        $status = $user['status'];
        $pseudo  = $user['pseudo'];

        if (in_array($status, ['blocked', 'drive_blocked', 'all_blocked'])) {
            $_SESSION['error'] = "Cet utilisateur est déjà bloqué.";
            return;
        }

        if ($status === 'banned') {
            $_SESSION['error'] = "Cet utilisateur est banni. Blocage inutile.";
            return;
        }

        // Mise à jour du statut
        $stmt = $pdo->prepare("UPDATE users SET status = 'all_blocked' WHERE id = :id");
        $stmt->execute([':id' => $userId]);

         // trace log 
        logActionToFirebase($this->getFullName(), 'blocage utilisateur : ' . $pseudo);
        $_SESSION['success'] = "L'utilisateur a bien été bloqué.";
    }

}
