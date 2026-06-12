<?php

// Allow CORS
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
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
elseif (preg_match('/\/api\/users\/?$/', $uri)) {
    if ($method !== 'GET') {
        http_response_code(405);
        echo json_encode(["message" => "Method not allowed. Please use GET to fetch users."]);
        return;
    }
    $authController = new AuthController($db);
    $authController->getAllUsers();
}
else {
    http_response_code(404);
    echo json_encode([
        "message" => "Endpoint not found.",
        "requested_uri" => $uri,
        "method" => $method
    ]);
}
