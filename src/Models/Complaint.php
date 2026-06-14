<?php

namespace Models;

use PDO;

class Complaint {
    private $conn;
    private $table_name = "complaints";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (user_id, title, category, urgency, location, description) 
                  VALUES (:user_id, :title, :category, :urgency, :location, :description)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":user_id", $data['user_id']);
        $stmt->bindParam(":title", $data['title']);
        $stmt->bindParam(":category", $data['category']);
        $stmt->bindParam(":urgency", $data['urgency']);
        $stmt->bindParam(":location", $data['location']);
        $stmt->bindParam(":description", $data['description']);

        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function getByUserId($userId) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE user_id = :user_id ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $userId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAll() {
        // Fetch complaints with user details
        $query = "SELECT c.*, u.name as citizenName, u.email as citizenEmail, u.nic as citizenNic 
                  FROM " . $this->table_name . " c 
                  JOIN citizens u ON c.user_id = u.id 
                  ORDER BY c.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function updateStatus($id, $status, $adminRemarks) {
        $query = "UPDATE " . $this->table_name . " 
                  SET status = :status, 
                      admin_remarks = :admin_remarks,
                      resolved_at = CASE WHEN :status2 = 'resolved' THEN CURRENT_TIMESTAMP ELSE NULL END
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':status2', $status);
        $stmt->bindParam(':admin_remarks', $adminRemarks);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }
}
