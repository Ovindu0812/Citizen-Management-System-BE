<?php

namespace Controllers;

use Models\Complaint;

class ComplaintController {
    private $complaintModel;

    public function __construct($db) {
        $this->complaintModel = new Complaint($db);
    }

    public function submit($data) {
        if (
            empty($data->user_id) || empty($data->title) || empty($data->category) || 
            empty($data->urgency) || empty($data->location) || empty($data->description)
        ) {
            http_response_code(400);
            echo json_encode(["message" => "All fields are required."]);
            return;
        }

        $complaintData = [
            'user_id' => $data->user_id,
            'title' => $data->title,
            'category' => $data->category,
            'urgency' => $data->urgency,
            'location' => $data->location,
            'description' => $data->description
        ];

        $insertId = $this->complaintModel->create($complaintData);

        if ($insertId) {
            http_response_code(201);
            echo json_encode(["message" => "Complaint submitted successfully!", "id" => $insertId]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "Unable to submit complaint."]);
        }
    }

    public function getUserComplaints() {
        if (!isset($_GET['userId']) || empty($_GET['userId'])) {
            http_response_code(400);
            echo json_encode(["message" => "User ID is required."]);
            return;
        }

        $userId = $_GET['userId'];
        $complaints = $this->complaintModel->getByUserId($userId);
        
        http_response_code(200);
        echo json_encode(["complaints" => $complaints]);
    }

    public function getAllComplaints() {
        $complaints = $this->complaintModel->getAll();
        http_response_code(200);
        echo json_encode(["complaints" => $complaints]);
    }
    public function updateComplaint($id, $data) {
        if (empty($data->status)) {
            http_response_code(400);
            echo json_encode(["message" => "Status is required."]);
            return;
        }

        $adminRemarks = isset($data->admin_remarks) ? $data->admin_remarks : null;
        
        if ($this->complaintModel->updateStatus($id, $data->status, $adminRemarks)) {
            http_response_code(200);
            echo json_encode(["message" => "Complaint updated successfully."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "Unable to update complaint."]);
        }
    }
}
