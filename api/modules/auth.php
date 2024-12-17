<?php
require_once __DIR__ . '/../config/connection.php';

class Auth {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function register($username, $email, $password) {
        error_log("Register function called with username: $username, email: $email");
        try {
            // Check if username or email already exists
            $stmt = $this->pdo->prepare(
                "SELECT COUNT(*) FROM users WHERE username = ? OR email = ?"
            );
            $stmt->execute([$username, $email]);
            if ($stmt->fetchColumn() > 0) {
                return [
                    "success" => false,
                    "error" => "Username or email already exists"
                ];
            }

            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $this->pdo->prepare(
                "INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)"
            );
            
            if ($stmt->execute([$username, $email, $password_hash])) {
                $userId = $this->pdo->lastInsertId();
                return [
                    "success" => true,
                    "user_id" => $userId,
                    "username" => $username
                ];
            }
            
            return [
                "success" => false, 
                "error" => "Unable to register user"
            ];
        } catch (PDOException $e) {
            error_log("Registration error: " . $e->getMessage());
            return [
                "success" => false, 
                "error" => "Database error occurred"
            ];
        } catch (Exception $e) {
            error_log("Unexpected error: " . $e->getMessage());
            return [
                "success" => false, 
                "error" => "An unexpected error occurred"
            ];
        }
    }

    public function login($username, $password) {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT id, username, password_hash FROM users WHERE username = ?"
            );
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                return [
                    "success" => true,
                    "user_id" => $user['id'],
                    "username" => $user['username']
                ];
            }
            return [
                "success" => false,
                "error" => "Invalid credentials"
            ];
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            return [
                "success" => false,
                "error" => "Database error occurred"
            ];
        }
    }
} 