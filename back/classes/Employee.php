<?php
require_once 'User.php';

// Classe employé (hérite de User)
class Employee extends User {

    // ===== Envoie de mail =====
    public function sendMail() {
        // À développer
    }

    // ===== Bloque un utilisateur =====
    public function blockUser() {
        // À développer
    }

    // ===== Débloque un utilisateur =====
    public function unblockUser() {
        // À développer
    }

    // ===== Valide un commentaire =====
    public function validateReview() {
        // À développer
    }

    // ===== Refuse un commentaire =====
    public function rejectReview() {
        // À développer
    }

    // ===== Ajoute des crédits à un utilisateur =====
    public function grantCredits() {
        // À développer
    }

    // ===== Supprime un avis (modération) =====
    public function deleteReview() {
        // À développer
    }

    // ===== Marque une demande de remboursement comme traitée =====
    public function processCashbackRequest() {
        // À développer
    }

    // ===== Consulter toutes les demandes de remboursement =====
    public function viewCashbackRequests() {
        // À développer
    }
}
?>