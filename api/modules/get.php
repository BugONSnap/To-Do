<?php
require_once __DIR__ . '/../config/connection.php';

class TodoGet {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getAllTodos($user_id) {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT t.* FROM todos t 
                JOIN todo_assignments ta ON t.id = ta.todo_id 
                WHERE ta.user_id = ?"
            );
            $stmt->execute([$user_id]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    public function getTodoById($todo_id, $user_id) {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT t.* FROM todos t 
                JOIN todo_assignments ta ON t.id = ta.todo_id 
                WHERE t.id = ? AND ta.user_id = ?"
            );
            $stmt->execute([$todo_id, $user_id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }
} 