<?php
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $conn;

    public function __construct() {
        $this->host = getenv('DB_HOST') ?: 'db';
        $this->db_name = getenv('DB_NAME') ?: 'podcast_db';
        $this->username = getenv('DB_USER') ?: 'podcast_user';
        $this->password = getenv('DB_PASSWORD') ?: 'podcast_pass';
    }

    public function getConnection() {
        $this->conn = null;
        
        $port = getenv('DB_PORT') ?: '3306';
        $dsn = "mysql:host=" . $this->host . ";port=" . $port . ";dbname=" . $this->db_name . ";charset=utf8mb4";
        
        try {
            $this->conn = new PDO(
                $dsn,
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_TIMEOUT => 5,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
                ]
            );
            $this->conn->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
        } catch(PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            return null;
        }
        
        return $this->conn;
    }
}

