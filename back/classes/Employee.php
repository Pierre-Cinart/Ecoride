<?php

require_once 'User.php';

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
}
