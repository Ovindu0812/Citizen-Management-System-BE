<?php

namespace Controllers;

use Models\User;
use Utils\Response;

class AuthController {
    private $userModel;

    public function __construct($db) {
        $this->userModel = new User($db);
    }

    public function register($data) {
        if (!$data) {
            Response::error("Invalid or empty request body.", 400);
        }

        if (
            empty($data->name) || empty($data->username) || empty($data->nic) || empty($data->email) || 
            empty($data->province) || empty($data->district) || empty($data->birthday) || 
            empty($data->phone) || empty($data->password)
        ) {
            Response::error("All fields are required.", 400);
        }

        // Validate Email
        if (!filter_var($data->email, FILTER_VALIDATE_EMAIL)) {
            Response::error("Invalid email format.", 400);
        }
        
        // Ensure email is entirely lowercase
        if (preg_match('/[A-Z]/', $data->email)) {
            Response::error("Email must be entirely in lowercase letters.", 400);
        }

        // Validate NIC (Sri Lanka format: 9 digits + V/X or 12 digits)
        if (!preg_match('/^([0-9]{9}[vVxX]|[0-9]{12})$/', $data->nic)) {
            Response::error("Invalid NIC format. Use 9 digits with V/X or 12 digits.", 400);
        }

        // Validate Phone (Basic 10-digit format)
        if (!preg_match('/^[0-9]{10}$/', $data->phone)) {
            $phone = preg_replace('/^\+94/', '0', $data->phone);
            if (!preg_match('/^[0-9]{10}$/', $phone)) {
                Response::error("Invalid phone number. Use 10 digits (e.g., 0771234567).", 400);
            }
            $data->phone = $phone;
        }

        if (strlen($data->password) < 6) {
            Response::error("Password must be at least 6 characters long.", 400);
        }

        // Validate birthday format (YYYY-MM-DD) strict
        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01])$/', $data->birthday)) {
            Response::error("Invalid birthday format. Use YYYY-MM-DD with valid month (01-12) and day (01-31).", 400);
        }

        // Check if user exists
        $existingUser = $this->userModel->findByEmailOrNic($data->email);
        $existingNic = $this->userModel->findByEmailOrNic($data->nic);
        $existingUsername = $this->userModel->findByUsername($data->username);
        
        if ($existingUser || $existingNic || $existingUsername) {
            Response::error("An account with this Username, Email, or NIC already exists.", 409);
        }

        $hashedPassword = password_hash($data->password, PASSWORD_BCRYPT);

        $userData = [
            'name' => $data->name,
            'username' => $data->username,
            'nic' => $data->nic,
            'email' => $data->email,
            'province' => $data->province,
            'district' => $data->district,
            'birthday' => $data->birthday,
            'phone' => $data->phone,
            'password_hash' => $hashedPassword
        ];

        if ($this->userModel->create($userData)) {
            Response::success("Account created successfully!", [], 201);
        } else {
            Response::error("Unable to create account.", 500);
        }
    }

    public function login($data) {
        if (!$data || empty($data->username) || empty($data->password)) {
            Response::error("Please provide a username and password.", 400);
        }

        $user = $this->userModel->findByUsername($data->username);

        if ($user && password_verify($data->password, $user['password_hash'])) {
            Response::success("Login successful!", [
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
            Response::error("Invalid username or password.", 401);
        }
    }
}
