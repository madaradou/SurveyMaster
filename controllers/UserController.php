<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/User.php';

class UserController {
    private $db;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->db = config::getConnexion();

        // Create password_reset_tokens table if it doesn't exist
        $sql = file_get_contents(__DIR__ . '/../sql/password_reset_tokens.sql');
        $this->db->exec($sql);
    }

    private function getUserRoleById($userId) {
        $stmt = $this->db->prepare("SELECT role FROM user WHERE user_id = :id");
        $stmt->execute(['id' => $userId]);
        $row = $stmt->fetch();
        return $row ? $row['role'] : null;
    }

    public function timeoutUser($userId, $durationSeconds) {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            return ['status' => false, 'message' => 'Unauthorized access'];
        }
        try {
            $timeoutUntil = date('Y-m-d H:i:s', time() + (int)$durationSeconds);
            $sql = "UPDATE user SET is_timed_out = 1, timeout_until = :timeout_until WHERE user_id = :user_id";
            $query = $this->db->prepare($sql);
            $result = $query->execute([
                'timeout_until' => $timeoutUntil,
                'user_id' => $userId
            ]);
            if ($result) {
                return ['status' => true, 'message' => 'User timed out successfully'];
            } else {
                return ['status' => false, 'message' => 'Failed to timeout user'];
            }
        } catch (Exception $e) {
            return ['status' => false, 'message' => 'An error occurred while timing out the user'];
        }
    }

    public function reactivateUser($userId) {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            return ['status' => false, 'message' => 'Unauthorized access'];
        }
        try {
            $sql = "UPDATE user SET is_timed_out = 0, timeout_until = NULL WHERE user_id = :user_id";
            $query = $this->db->prepare($sql);
            $result = $query->execute(['user_id' => $userId]);
            if ($result) {
                return ['status' => true, 'message' => 'User reactivated successfully'];
            } else {
                return ['status' => false, 'message' => 'Failed to reactivate user'];
            }
        } catch (Exception $e) {
            return ['status' => false, 'message' => 'An error occurred while reactivating the user'];
        }
    }
    public function loginUser($email, $password) {
        try {
            $sql = "SELECT * FROM user WHERE email = :email";
            $query = $this->db->prepare($sql);
            $query->execute(['email' => $email]);
            $user = $query->fetch();

            if (!$user) {
                return ['status' => false, 'message' => 'Invalid email or password'];
            }

            if (!password_verify($password, $user['password'])) {
                return ['status' => false, 'message' => 'Invalid email or password'];
            }

            if ($user['banned']) {
                return ['status' => false, 'message' => 'Your account has been banned'];
            }

            if (isset($user['is_timed_out']) && $user['is_timed_out'] && isset($user['timeout_until']) && strtotime($user['timeout_until']) > time()) {
                $remaining = strtotime($user['timeout_until']) - time();
                return ['status' => false, 'message' => 'Your account is timed out. Please try again in ' . $remaining . ' seconds.'];
            }

            // Set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];

            return ['status' => true, 'message' => 'Login successful'];
        } catch (Exception $e) {
            return ['status' => false, 'message' => 'An error occurred during login'];
        }
    }

    public function addUser($user)
    {
        $sql = "INSERT INTO user(first_name, last_name, email, password, role, verified, banned, date, account_type, image) VALUES (:first_name, :last_name, :email, :password, :role, :verified, :banned, CURRENT_TIMESTAMP(), :account_type, :image)";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(
               [
                'first_name' => $user->getFirstName(),
                'last_name' => $user->getLastName(),
                'email' => $user->getEmail(),
                'password' => $user->getPassword(),
                'role' => $user->getRole() ?? 'client',
                'verified' => $user->getVerified() ?? 0,
                'banned' => $user->getBanned() ?? 0,
                'account_type' => $user->getAccountType() ?? 'normal',
                'image' => $user->getImage() ?? null
                ]
            );
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    public function getUserByEmail($email) {
        try {
            $sql = "SELECT * FROM user WHERE email = :email";
            $query = $this->db->prepare($sql);
            $query->execute(['email' => $email]);
            return $query->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return null;
        }
    }

    public function loginUserWithGoogle($email) {
        try {
            $user = $this->getUserByEmail($email);
            
            if (!$user) {
                return ['status' => false, 'message' => 'User not found'];
            }

            if ($user['banned']) {
                return ['status' => false, 'message' => 'Your account has been banned'];
            }

            // Set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];

            return ['status' => true, 'message' => 'Login successful'];
        } catch (Exception $e) {
            return ['status' => false, 'message' => 'An error occurred during login'];
        }
    }

    public function getAllUsers() {
        try {
            $sql = "SELECT user_id, first_name, last_name, email, role, verified, banned, date, account_type FROM user";
            $query = $this->db->prepare($sql);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception('Error fetching users: ' . $e->getMessage());
        }
    }

    public function banUser($userId) {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            return ['status' => false, 'message' => 'Unauthorized access'];
        }

        // Do not allow actions on admin
        if ($this->getUserRoleById($userId) === 'admin') {
            return ['status' => false, 'message' => 'Cannot ban admin'];
        }

        try {
            $sql = "UPDATE user SET banned = 1 WHERE user_id = :user_id";
            $query = $this->db->prepare($sql);
            $result = $query->execute(['user_id' => $userId]);

            if ($result) {
                return ['status' => true, 'message' => 'User banned successfully'];
            } else {
                return ['status' => false, 'message' => 'Failed to ban user'];
            }
        } catch (Exception $e) {
            return ['status' => false, 'message' => 'An error occurred while banning the user'];
        }
    }

    public function unbanUser($userId) {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            return ['status' => false, 'message' => 'Unauthorized access'];
        }

        // Do not allow actions on admin
        if ($this->getUserRoleById($userId) === 'admin') {
            return ['status' => false, 'message' => 'Cannot unban admin'];
        }

        try {
            $sql = "UPDATE user SET banned = 0 WHERE user_id = :user_id";
            $query = $this->db->prepare($sql);
            $result = $query->execute(['user_id' => $userId]);

            if ($result) {
                return ['status' => true, 'message' => 'User unbanned successfully'];
            } else {
                return ['status' => false, 'message' => 'Failed to unban user'];
            }
        } catch (Exception $e) {
            return ['status' => false, 'message' => 'An error occurred while unbanning the user'];
        }
    }

    //GitHub
    public function get_github_link($client_id=null) {
        if ($client_id === null) {
            $client_id = GITHUB_CLIENT_ID;
        }
        $redirect_uri = urlencode(GITHUB_REDIRECT_URI);
        $scopes = urlencode(GITHUB_SCOPES);
        $link_to_follow = GITHUB_OAUTH_URL . "?client_id={$client_id}&redirect_uri={$redirect_uri}&scope={$scopes}";
        return $link_to_follow;
    }

    public function storeResetToken($email, $token, $expiry) {
        try {
            $stmt = $this->db->prepare("INSERT INTO password_reset_tokens (email, token, expiry) VALUES (?, ?, ?)");
            return $stmt->execute([$email, $token, $expiry]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function validateResetToken($token) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM password_reset_tokens WHERE token = ? AND expiry > NOW() AND used = 0");
            $stmt->execute([$token]);
            return $stmt->fetch() !== false;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function resetPassword($token, $newPassword) {
        try {
            $this->db->beginTransaction();

            // Get email from token
            $stmt = $this->db->prepare("SELECT email FROM password_reset_tokens WHERE token = ? AND expiry > NOW() AND used = 0");
            $stmt->execute([$token]);
            $result = $stmt->fetch();

            if ($result) {
                $email = $result['email'];
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

                // Update password
                $stmt = $this->db->prepare("UPDATE user SET password = ? WHERE email = ?");
                $passwordUpdated = $stmt->execute([$hashedPassword, $email]);

                if ($passwordUpdated) {
                    // Mark token as used
                    $stmt = $this->db->prepare("UPDATE password_reset_tokens SET used = 1 WHERE token = ?");
                    $stmt->execute([$token]);

                    $this->db->commit();
                    return true;
                }
            }

            $this->db->rollBack();
            return false;
        } catch (PDOException $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function invalidateResetToken($token) {
        try {
            $stmt = $this->db->prepare("UPDATE password_reset_tokens SET used = 1 WHERE token = ?");
            return $stmt->execute([$token]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function loginUserWithGithub($email) {
        try {
            $user = $this->getUserByEmail($email);
            
            if (!$user) {
                return ['status' => false, 'message' => 'User not found'];
            }

            if ($user['banned']) {
                return ['status' => false, 'message' => 'Your account has been banned'];
            }

            // Set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];

            return ['status' => true, 'message' => 'Login successful'];
        } catch (Exception $e) {
            return ['status' => false, 'message' => 'An error occurred during login'];
        }
    }

    public function makeAdmin($userId) {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            return ['status' => false, 'message' => 'Unauthorized access'];
        }

        try {
            $sql = "UPDATE user SET role = 'agent' WHERE user_id = :user_id";
            $query = $this->db->prepare($sql);
            $result = $query->execute(['user_id' => $userId]);

            if ($result) {
                return ['status' => true, 'message' => 'User promoted to agent successfully'];
            } else {
                return ['status' => false, 'message' => 'Failed to promote user to agent'];
            }
        } catch (Exception $e) {
            return ['status' => false, 'message' => 'An error occurred while promoting user to agent'];
        }
    }

    public function setUserRole($userId, $role) {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            return ['status' => false, 'message' => 'Unauthorized access'];
        }

        if (!in_array($role, ['agent', 'client'])) {
            return ['status' => false, 'message' => 'Invalid role specified'];
        }

        // Prevent any action on admin accounts
        $currentRole = $this->getUserRoleById($userId);
        if ($currentRole === 'admin') {
            return ['status' => false, 'message' => 'Cannot change role of admin'];
        }

        try {
            $sql = "UPDATE user SET role = :role WHERE user_id = :user_id";
            $query = $this->db->prepare($sql);
            $result = $query->execute([
                'user_id' => $userId,
                'role' => $role
            ]);

            if ($result) {
                return ['status' => true, 'message' => 'User role updated successfully'];
            } else {
                return ['status' => false, 'message' => 'Failed to update user role'];
            }
        } catch (Exception $e) {
            return ['status' => false, 'message' => 'An error occurred while updating user role'];
        }
    }

    public function updateProfile($userId, $firstName, $lastName, $email) {
        try {
            // Validate inputs
            if (empty($firstName) || empty($lastName) || empty($email)) {
                return ['status' => false, 'message' => 'All fields are required'];
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return ['status' => false, 'message' => 'Invalid email format'];
            }

            // Check if email already exists for other users
            $checkEmailSql = "SELECT user_id FROM user WHERE email = :email AND user_id != :user_id";
            $checkEmailQuery = $this->db->prepare($checkEmailSql);
            $checkEmailQuery->execute(['email' => $email, 'user_id' => $userId]);
            
            if ($checkEmailQuery->fetch()) {
                return ['status' => false, 'message' => 'Email already exists'];
            }

            // Update user profile
            $sql = "UPDATE user SET first_name = :first_name, last_name = :last_name, email = :email WHERE user_id = :user_id";
            $query = $this->db->prepare($sql);
            $result = $query->execute([
                'user_id' => $userId,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email
            ]);

            if ($result) {
                // Update session variables
                $_SESSION['first_name'] = $firstName;
                $_SESSION['last_name'] = $lastName;
                $_SESSION['email'] = $email;
                
                return ['status' => true, 'message' => 'Profile updated successfully'];
            } else {
                return ['status' => false, 'message' => 'Failed to update profile'];
            }
        } catch (Exception $e) {
            return ['status' => false, 'message' => 'An error occurred while updating profile'];
        }
    }

    public function deleteUser($userId) {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            return ['status' => false, 'message' => 'Unauthorized access'];
        }

        try {
            // Check if user exists
            $checkSql = "SELECT user_id FROM user WHERE user_id = :user_id";
            $checkQuery = $this->db->prepare($checkSql);
            $checkQuery->execute(['user_id' => $userId]);
            
            if (!$checkQuery->fetch()) {
                return ['status' => false, 'message' => 'User not found'];
            }

            // Start transaction
            $this->db->beginTransaction();

            // Delete the user
            $sql = "DELETE FROM user WHERE user_id = :user_id";
            $query = $this->db->prepare($sql);
            $result = $query->execute(['user_id' => $userId]);

            if ($result) {
                $this->db->commit();
                return ['status' => true, 'message' => 'User deleted successfully'];
            } else {
                $this->db->rollBack();
                return ['status' => false, 'message' => 'Failed to delete user'];
            }
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return ['status' => false, 'message' => 'An error occurred while deleting the user'];
        }
    }




}