<?php
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    public $conn;

    public function __construct() {
        $serverName = $_SERVER['SERVER_NAME'] ?? '';
        $serverAddr = $_SERVER['SERVER_ADDR'] ?? '';

        // Detect local XAMPP environment (localhost or 127.0.0.1)
        if ($serverName === 'localhost' || $serverName === '127.0.0.1' || $serverAddr === '127.0.0.1' || strpos($serverName, 'localhost') !== false) {
            // XAMPP (local)
            $this->host     = "localhost";
            $this->db_name  = "barangay_pension_db";
            $this->username = "root";
            $this->password = "";
        } else {
            // InfinityFree (online)
            $this->host     = "sql306.infinityfree.com";
            $this->db_name  = "if0_43216134_PensionTrack_db";
            $this->username = "if0_43216134";
            $this->password = "IMONG_DB_PASSWORD";
        }
    }

    public function connect() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
        return $this->conn;
    }
}