<?php
class PensionClaim {
    private $conn;
    private $table = "pension_claims";

    public $claim_id;
    public $senior_id;
    public $program_id;
    public $user_id;
    public $claim_date;
    public $status;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function addPensionClaim() {
        $query = "INSERT INTO {$this->table} (senior_id, program_id, user_id, claim_date, status)
                   VALUES (:senior_id, :program_id, :user_id, :claim_date, :status)";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':senior_id', $this->senior_id);
            $stmt->bindParam(':program_id', $this->program_id);
            $stmt->bindParam(':user_id', $this->user_id);
            $stmt->bindParam(':claim_date', $this->claim_date);
            $stmt->bindParam(':status', $this->status);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("addPensionClaim failed: " . $e->getMessage());
            return false;
        }
    }

    public function getPensionClaims() {
        $query = "SELECT pc.claim_id, sc.full_name AS senior_name, pp.name AS program_name,
                          u.full_name AS processed_by, pc.claim_date, pc.status
                   FROM {$this->table} pc
                   JOIN senior_citizens sc ON pc.senior_id = sc.senior_id
                   JOIN pension_programs pp ON pc.program_id = pp.program_id
                   JOIN users u ON pc.user_id = u.user_id
                   ORDER BY pc.claim_date DESC";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("getPensionClaims failed: " . $e->getMessage());
            return [];
        }
    }

    public function updatePensionClaim($id) {
        $query = "UPDATE {$this->table}
                   SET senior_id = :senior_id, program_id = :program_id,
                       user_id = :user_id, claim_date = :claim_date, status = :status
                   WHERE claim_id = :claim_id";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':senior_id', $this->senior_id);
            $stmt->bindParam(':program_id', $this->program_id);
            $stmt->bindParam(':user_id', $this->user_id);
            $stmt->bindParam(':claim_date', $this->claim_date);
            $stmt->bindParam(':status', $this->status);
            $stmt->bindParam(':claim_id', $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("updatePensionClaim failed: " . $e->getMessage());
            return false;
        }
    }

    public function deletePensionClaim($id) {
        $query = "DELETE FROM {$this->table} WHERE claim_id = :claim_id";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':claim_id', $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("deletePensionClaim failed: " . $e->getMessage());
            return false;
        }
    }

    public function searchPensionClaims($keyword) {
        $query = "SELECT pc.claim_id, sc.full_name AS senior_name, pp.name AS program_name,
                          u.full_name AS processed_by, pc.claim_date, pc.status
                   FROM {$this->table} pc
                   JOIN senior_citizens sc ON pc.senior_id = sc.senior_id
                   JOIN pension_programs pp ON pc.program_id = pp.program_id
                   JOIN users u ON pc.user_id = u.user_id
                   WHERE sc.full_name LIKE :keyword
                      OR pp.name LIKE :keyword
                      OR u.full_name LIKE :keyword
                      OR pc.status LIKE :keyword
                   ORDER BY pc.claim_date DESC";
        try {
            $stmt = $this->conn->prepare($query);
            $search = "%" . $keyword . "%";
            $stmt->bindParam(':keyword', $search);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("searchPensionClaims failed: " . $e->getMessage());
            return [];
        }
    }
}