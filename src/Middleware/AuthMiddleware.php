<?php

namespace Middleware;

class AuthMiddleware {
    /**
     * Fallback for getallheaders() if it doesn't exist
     */
    private static function getAllHeaders() {
        if (function_exists('getallheaders')) {
            return getallheaders();
        }
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }

    public static function isAuthenticated() {
        $headers = self::getAllHeaders();
        if (isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];
            if (preg_match('/Bearer\s(\d+)/', $authHeader, $matches)) {
                return $matches[1]; // Return User ID
            }
        }
        
        http_response_code(401);
        echo json_encode(["message" => "Unauthorized access. Please login."]);
        exit;
    }

    public static function isAdmin($db) {
        $userId = self::isAuthenticated();
        
        // Check database for role
        $query = "SELECT role FROM citizens WHERE id = :id LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":id", $userId);
        $stmt->execute();
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($user && $user['role'] === 'admin') {
            return $userId;
        }

        http_response_code(403);
        echo json_encode(["message" => "Forbidden. Admin access required."]);
        exit;
    }
}
