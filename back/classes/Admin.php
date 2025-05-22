<?php
require_once 'User.php';

// Classe administrateur (hérite de Employee)
class Admin extends Employee {
    // ===== Constructeur =====
    public function __construct(array $data) {
        parent::__construct($data);
    }

    // ===== Ajoute un employé =====
    public function addEmployee() {
        // À développer
    }

    // ===== Supprime un employé =====
    public function removeEmployee() {
        // À développer
    }

    // ===== Consulte la liste des utilisateurs (avec filtrage possible) =====
    public function viewUsers() {
        // À développer
    }

    // ===== Consulte la liste des trajets publiés =====
    public function viewTrips() {
        // À développer
    }

    // ===== Supprime un trajet (modération ou urgence) =====
    public function deleteTrip() {
        // À développer
    }

    // ===== Modifie un trajet manuellement (cas exceptionnel) =====
    public function editTrip() {
        // À développer
    }

    // ===== Génère un rapport d'activité (utilisateurs, trajets, crédits, avis, etc.) =====
    public function generateReport() {
        // À développer
    }

    // ===== Consulte les journaux d’actions (employés, modérations, etc.) =====
    public function viewLogs() {
        // À développer
    }

    // ===== Réinitialise le mot de passe d’un utilisateur =====
    public function resetUserPassword() {
        // À développer
    }

    // ===== Modifie le rôle d’un utilisateur (promotion/déclassement) =====
    public function changeUserRole() {
        // À développer
    }
}
?>