<?php

// Allow CORS
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Basic autoloader
spl_autoload_register(function ($class_name) {
    if (strpos($class_name, 'Config\\') === 0) {
        // config/database.php logic
        require_once __DIR__ . '/../config/database.php';
    } else {
        $path = __DIR__ . '/../src/' . str_replace('\\', '/', $class_name) . '.php';
        if (file_exists($path)) {
            require_once $path;
        }
    }
});

use Config\Database;
use Controllers\AuthController;
use Controllers\ComplaintController;
use Controllers\UserController;
use Middleware\AuthMiddleware;

// Initialize Database connection
$database = new Database();
$db = $database->getConnection();

// Parse request
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

$data = json_decode(file_get_contents("php://input"));

// Robust Router
// Use preg_match to handle trailing slashes and any unexpected base paths (like /public/index.php)
if (preg_match('/\/api\/auth\/register\/?$/', $uri)) {
    if ($method !== 'POST') {
        http_response_code(405);
        echo json_encode(["message" => "Method not allowed. Please use POST for registration."]);
        return;
    }
    $authController = new AuthController($db);
    $authController->register($data);
} 
elseif (preg_match('/\/api\/auth\/login\/?$/', $uri)) {
    if ($method !== 'POST') {
        http_response_code(405);
        echo json_encode(["message" => "Method not allowed. Please use POST for login."]);
        return;
    }
    $authController = new AuthController($db);
    $authController->login($data);
}
elseif (preg_match('/\/api\/users\/([0-9]+)\/?$/', $uri, $matches)) {
    AuthMiddleware::isAuthenticated();
    $userController = new UserController($db);
    if ($method === 'PUT') {
        $userController->updateProfile($matches[1], $data);
    } elseif ($method === 'DELETE') {
        AuthMiddleware::isAdmin($db);
        $userController->deleteUser($matches[1]);
    } else {
        http_response_code(405);
        echo json_encode(["message" => "Method not allowed."]);
    }
}
elseif (preg_match('/\/api\/users\/?$/', $uri)) {
    AuthMiddleware::isAdmin($db);
    if ($method !== 'GET') {
        http_response_code(405);
        echo json_encode(["message" => "Method not allowed."]);
        return;
    }
    $userController = new UserController($db);
    $userController->getAllUsers();
}
elseif (preg_match('/\/api\/complaints\/user\/?$/', $uri)) {
    AuthMiddleware::isAuthenticated();
    if ($method !== 'GET') {
        http_response_code(405);
        echo json_encode(["message" => "Method not allowed."]);
        return;
    }
    $complaintController = new ComplaintController($db);
    $complaintController->getUserComplaints();
}
elseif (preg_match('/\/api\/complaints\/?$/', $uri)) {
    $complaintController = new ComplaintController($db);
    if ($method === 'POST') {
        AuthMiddleware::isAuthenticated();
        $complaintController->submit($data);
    } elseif ($method === 'GET') {
        AuthMiddleware::isAdmin($db);
        $complaintController->getAllComplaints();
    } else {
        http_response_code(405);
        echo json_encode(["message" => "Method not allowed."]);
    }
}
elseif (preg_match('/\/api\/complaints\/([0-9]+)\/?$/', $uri, $matches)) {
    AuthMiddleware::isAdmin($db);
    $complaintController = new ComplaintController($db);
    if ($method === 'PUT') {
        $complaintController->updateComplaint($matches[1], $data);
    } else {
        http_response_code(405);
        echo json_encode(["message" => "Method not allowed."]);
    }
}
elseif (preg_match('/^\/?$/', $uri)) {
    if ($method !== 'GET') {
        http_response_code(405);
        echo json_encode(["message" => "Method not allowed."]);
        return;
    }
    http_response_code(200);
    echo json_encode(["message" => "Citizen Management System API is running."]);
}
else {
    http_response_code(404);
    echo json_encode([
        "message" => "Endpoint not found.",
        "requested_uri" => $uri,
        "method" => $method
    ]);
}
