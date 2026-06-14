<?php

namespace Controllers;

use Models\User;
use Utils\Response;

class UserController {
    private $userModel;

    public function __construct($db) {
        $this->userModel = new User($db);
    }

    public function updateProfile($id, $data) {
        if (!$data) {
            Response::error("Invalid or empty request body.", 400);
        }

        if (
            empty($data->name) || empty($data->province) || 
            empty($data->district) || empty($data->birthday) || empty($data->phone)
        ) {
            Response::error("All fields are required.", 400);
        }

        // Validate birthday format (YYYY-MM-DD)
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data->birthday)) {
            Response::error("Invalid birthday format. Use YYYY-MM-DD.", 400);
        }

        $userData = [
            'name' => $data->name,
            'province' => $data->province,
            'district' => $data->district,
            'birthday' => $data->birthday,
            'phone' => $data->phone
        ];

        if ($this->userModel->update($id, $userData)) {
            Response::success("Profile updated successfully!");
        } else {
            Response::error("Unable to update profile.", 500);
        }
    }

    public function getAllUsers() {
        $users = $this->userModel->getAll();
        Response::success("Users fetched successfully.", ["users" => $users]);
    }

    public function deleteUser($id) {
        if ($this->userModel->delete($id)) {
            Response::success("User deleted successfully.");
        } else {
            Response::error("Unable to delete user.", 500);
        }
    }
}
