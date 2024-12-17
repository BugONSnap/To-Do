<?php
require_once __DIR__ . '/../config/connection.php';

class TodoPost {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function createTodo($title, $description, $due_date, $user_id) {
        try {
            $this->pdo->beginTransaction();

            // Create todo
            $stmt = $this->pdo->prepare(
                "INSERT INTO todos (title, description, due_date) VALUES (?, ?, ?)"
            );
            $stmt->execute([$title, $description, $due_date]);
            $todo_id = $this->pdo->lastInsertId();

            // Create assignment
            $stmt = $this->pdo->prepare(
                "INSERT INTO todo_assignments (todo_id, user_id) VALUES (?, ?)"
            );
            $stmt->execute([$todo_id, $user_id]);

            $this->pdo->commit();
            return ["success" => true, "todo_id" => $todo_id];
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            return ["error" => $e->getMessage()];
        }
    }

    public function updateTodo($todo_id, $title, $description, $status, $due_date, $user_id) {
        try {
            // Verify ownership
            $stmt = $this->pdo->prepare(
                "SELECT 1 FROM todo_assignments WHERE todo_id = ? AND user_id = ?"
            );
            $stmt->execute([$todo_id, $user_id]);
            if (!$stmt->fetch()) {
                return ["error" => "Unauthorized"];
            }

            // Update todo
            $stmt = $this->pdo->prepare(
                "UPDATE todos SET 
                title = ?, 
                description = ?, 
                status = ?,
                due_date = ?
                WHERE id = ?"
            );
            $stmt->execute([$title, $description, $status, $due_date, $todo_id]);
            return ["success" => true];
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    public function deleteTodo($todo_id, $user_id) {
        try {
            // Verify ownership
            $stmt = $this->pdo->prepare(
                "SELECT 1 FROM todo_assignments WHERE todo_id = ? AND user_id = ?"
            );
            $stmt->execute([$todo_id, $user_id]);
            if (!$stmt->fetch()) {
                return ["error" => "Unauthorized"];
            }

            // Delete todo (cascade will handle assignment)
            $stmt = $this->pdo->prepare("DELETE FROM todos WHERE id = ?");
            $stmt->execute([$todo_id]);
            return ["success" => true];
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    public function registerUser($username, $email, $password) {
        try {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $this->pdo->prepare(
                "INSERT INTO users (username, email, password_hash, created_at) 
                 VALUES (?, ?, ?, NOW())"
            );
            
            if ($stmt->execute([$username, $email, $password_hash])) {
                return [
                    "success" => true, 
                    "user_id" => $this->pdo->lastInsertId()
                ];
            }
            return ["success" => false, "error" => "Unable to register user"];
        } catch (PDOException $e) {
            // Check for duplicate entry
            if ($e->getCode() == 23000) {
                return ["success" => false, "error" => "Username or email already exists"];
            }
            return ["success" => false, "error" => $e->getMessage()];
        }
    }
} 