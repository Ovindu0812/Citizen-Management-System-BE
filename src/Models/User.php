<?php

namespace Models;

use PDO;

class User {
    private $conn;
    private $table_name = "citizens";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (name, nic, email, province, district, birthday, phone, password_hash) 
                  VALUES (:name, :nic, :email, :province, :district, :birthday, :phone, :password_hash)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":name", $data['name']);
        $stmt->bindParam(":nic", $data['nic']);
        $stmt->bindParam(":email", $data['email']);
        $stmt->bindParam(":province", $data['province']);
        $stmt->bindParam(":district", $data['district']);
        $stmt->bindParam(":birthday", $data['birthday']);
        $stmt->bindParam(":phone", $data['phone']);
        $stmt->bindParam(":password_hash", $data['password_hash']);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function findByEmailOrNic($idOrEmail) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE email = :idOrEmail OR nic = :idOrEmail LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":idOrEmail", $idOrEmail);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function getAll() {
        $query = "SELECT id, name, nic, email, province, district, phone, role, created_at FROM " . $this->table_name . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
