<?php
class Transaction {
    private $conn;
    private $table = "transactions";
    public $lastError = '';

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Log a user action / transaction
     * @param int|null $user_id
     * @param string $action
     * @param string $details
     * @return bool
     */
    public function log($user_id, $action, $details = '') {
        $this->lastError = '';
        $query = "INSERT INTO {$this->table} (user_id, action, details) VALUES (:user_id, :action, :details)";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindValue(':action', $action, PDO::PARAM_STR);
            $stmt->bindValue(':details', $details, PDO::PARAM_STR);
            return $stmt->execute();
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("Transaction log failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all transactions with user names
     * @return array
     */
    public function getAll() {
        $this->lastError = '';
        $query = "SELECT t.transaction_id, t.user_id, u.full_name AS user_name,
                         t.action, t.details, t.created_at
                  FROM {$this->table} t
                  LEFT JOIN users u ON t.user_id = u.user_id
                  ORDER BY t.created_at DESC";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("getAll transactions failed: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get transactions for a specific user
     * @param int $user_id
     * @return array
     */
    public function getByUser($user_id) {
        $this->lastError = '';
        $query = "SELECT t.*, u.full_name AS user_name
                  FROM {$this->table} t
                  LEFT JOIN users u ON t.user_id = u.user_id
                  WHERE t.user_id = :user_id
                  ORDER BY t.created_at DESC";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("getByUser transactions failed: " . $e->getMessage());
            return [];
        }
    }
}