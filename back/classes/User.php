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
}
