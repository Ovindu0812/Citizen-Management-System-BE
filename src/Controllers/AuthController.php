<?php

namespace Controllers;

use Models\User;

class AuthController {
    private $userModel;

    public function __construct($db) {
        $this->userModel = new User($db);
    }

    public function register($data) {
        if (
            empty($data->name) || empty($data->nic) || empty($data->email) || 
            empty($data->province) || empty($data->district) || empty($data->birthday) || 
            empty($data->phone) || empty($data->password)
        ) {
            http_response_code(400);
            echo json_encode(["message" => "All fields are required."]);
            return;
        }

        if (strlen($data->password) < 6) {
            http_response_code(400);
            echo json_encode(["message" => "Password must be at least 6 characters long."]);
            return;
        }

        // Check if user exists
        $existingUser = $this->userModel->findByEmailOrNic($data->email);
        $existingNic = $this->userModel->findByEmailOrNic($data->nic);
        
        if ($existingUser || $existingNic) {
            http_response_code(409);
            echo json_encode(["message" => "An account with this Email or NIC already exists."]);
            return;
        }

        $hashedPassword = password_hash($data->password, PASSWORD_BCRYPT);

        $userData = [
            'name' => $data->name,
            'nic' => $data->nic,
            'email' => $data->email,
            'province' => $data->province,
            'district' => $data->district,
            'birthday' => $data->birthday,
            'phone' => $data->phone,
            'password_hash' => $hashedPassword
        ];

        if ($this->userModel->create($userData)) {
            http_response_code(201);
            echo json_encode(["message" => "Account created successfully!"]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "Unable to create account."]);
        }
    }

    public function login($data) {
        if (empty($data->idOrEmail) || empty($data->password)) {
            http_response_code(400);
            echo json_encode(["message" => "Please provide ID/Email and password."]);
            return;
        }

        $user = $this->userModel->findByEmailOrNic($data->idOrEmail);

        if ($user && password_verify($data->password, $user['password_hash'])) {
            http_response_code(200);
            echo json_encode([
                "message" => "Login successful!",
                "user" => [
                    "id" => $user['id'],
                    "name" => $user['name'],
                    "email" => $user['email'],
                    "nic" => $user['nic'],
                    "phone" => $user['phone'],
                    "province" => $user['province'],
                    "district" => $user['district'],
                    "birthday" => $user['birthday'],
                    "created_at" => $user['created_at'],
                    "role" => $user['role']
                ]
            ]);
        } else {
            http_response_code(401);
            echo json_encode(["message" => "Invalid ID/Email or password."]);
        }
    }
    public function getAllUsers() {
        $users = $this->userModel->getAll();
        http_response_code(200);
        echo json_encode(["users" => $users]);
    }
}
