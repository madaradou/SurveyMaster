<?php
class User {
    private $db;
    private $user_id;
    private $first_name;
    private $last_name;
    private $email;
    private $password;
    private $role;
    private $date;
    private $banned;
    private $verified;
    private $image;
    private $account_type;

    public function __construct() {
        $this->db = config::getConnexion();
    }

    // Getters and setters
    public function getUserId() { return $this->user_id; }
    public function getFirstName() { return $this->first_name; }
    public function getLastName() { return $this->last_name; }
    public function getEmail() { return $this->email; }
    public function getRole() { return $this->role; }
    public function getDate() { return $this->date; }
    public function getBanned() { return $this->banned; }
    public function getVerified() { return $this->verified; }
    public function getImage() { return $this->image; }
    public function getAccountType() { return $this->account_type; }
    public function getPassword() { return $this->password; }

    public function setFirstName($first_name) { $this->first_name = $first_name; }
    public function setLastName($last_name) { $this->last_name = $last_name; }
    public function setEmail($email) { $this->email = $email; }
    public function setPassword($password) { $this->password = password_hash($password, PASSWORD_DEFAULT); }
    public function setRole($role) { $this->role = $role; }

    public function login($email, $password) {
        $sql = "SELECT * FROM user WHERE email = :email";
        try {
            $query = $this->db->prepare($sql);
            $query->execute(['email' => $email]);
            $user = $query->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                if ($user['banned']) {
                    return ['status' => false, 'message' => 'Your account has been banned'];
                }
                
                // Set all user properties
                $this->user_id = $user['user_id'];
                $this->first_name = $user['first_name'];
                $this->last_name = $user['last_name'];
                $this->email = $user['email'];
                $this->role = $user['role'];
                $this->banned = $user['banned'];
                $this->verified = $user['verified'];
                $this->image = $user['image'];
                $this->account_type = $user['account_type'];
                
                // Start session
                session_start();
                $_SESSION['user_id'] = $this->user_id;
                $_SESSION['email'] = $this->email;
                $_SESSION['role'] = $this->role;
                
                return ['status' => true, 'message' => 'Login successful'];
            }
            return ['status' => false, 'message' => 'Invalid email or password'];
        } catch (Exception $e) {
            return ['status' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    public function setBanned($banned) { $this->banned = $banned; }
    public function setVerified($verified) { $this->verified = $verified; }
    public function setImage($image) { $this->image = $image; }
    public function setAccountType($account_type) { $this->account_type = $account_type; }
}