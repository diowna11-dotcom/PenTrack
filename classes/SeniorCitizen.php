<?php
class SeniorCitizen {
    private $conn;
    private $table = "senior_citizens";

    public $senior_id;
    public $full_name;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll() {
        $query = "SELECT * FROM {$this->table} ORDER BY senior_id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function add() {
        $query = "INSERT INTO {$this->table} (full_name) VALUES (:full_name)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':full_name', $this->full_name);
        return $stmt->execute();
    }

    public function update($id) {
        $query = "UPDATE {$this->table} SET full_name = :full_name WHERE senior_id = :senior_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':full_name', $this->full_name);
        $stmt->bindParam(':senior_id', $id);
        return $stmt->execute();
    }

    public function delete($id) {
        $query = "DELETE FROM {$this->table} WHERE senior_id = :senior_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':senior_id', $id);
        return $stmt->execute();
    }
}