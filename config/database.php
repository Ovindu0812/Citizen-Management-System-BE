<?php

namespace Config;

use PDO;
use PDOException;

class Database {
    private $host = "127.0.0.1";
    private $db_name = "citizen_management";
    private $username = "root";
    private $password = "3641759Jaffna";
    private $conn;

    public function __construct() {
        // Simple .env parser for basic setups
        $envFile = __DIR__ . '/../.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) continue;
                list($name, $value) = explode('=', $line, 2);
                $_ENV[trim($name)] = trim($value);
            }
        }

        $this->host = $_ENV['DB_HOST'] ?? $this->host;
        $this->db_name = $_ENV['DB_NAME'] ?? $this->db_name;
        $this->username = $_ENV['DB_USER'] ?? $this->username;
        $this->password = $_ENV['DB_PASSWORD'] ?? $this->password;
    }

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo json_encode(["message" => "Database connection error: " . $exception->getMessage()]);
            exit;
        }
        return $this->conn;
    }
}
