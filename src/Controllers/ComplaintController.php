<?php

namespace Controllers;

use Models\Complaint;
use Utils\Response;

class ComplaintController {
    private $complaintModel;

    public function __construct($db) {
        $this->complaintModel = new Complaint($db);
    }

    public function submit($data) {
        if (!$data) {
            Response::error("Invalid or empty request body.", 400);
        }

        if (
            empty($data->user_id) || empty($data->title) || empty($data->category) || 
            empty($data->urgency) || empty($data->location) || empty($data->description)
        ) {
            Response::error("All fields are required.", 400);
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
            Response::success("Complaint submitted successfully!", ["id" => $insertId], 201);
        } else {
            Response::error("Unable to submit complaint due to a server error.", 500);
        }
    }

    public function getUserComplaints() {
        if (!isset($_GET['userId']) || empty($_GET['userId'])) {
            Response::error("User ID is required.", 400);
        }

        $userId = $_GET['userId'];
        $complaints = $this->complaintModel->getByUserId($userId);
        
        Response::success("Complaints fetched successfully.", ["complaints" => $complaints]);
    }

    public function getAllComplaints() {
        $complaints = $this->complaintModel->getAll();
        Response::success("All complaints fetched successfully.", ["complaints" => $complaints]);
    }

    public function updateComplaint($id, $data) {
        if (!$data || empty($data->status)) {
            Response::error("Status is required.", 400);
        }

        $adminRemarks = isset($data->admin_remarks) ? $data->admin_remarks : null;
        
        if ($this->complaintModel->updateStatus($id, $data->status, $adminRemarks)) {
            Response::success("Complaint updated successfully.");
        } else {
            Response::error("Unable to update complaint.", 500);
        }
    }
}
