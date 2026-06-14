<?php

namespace Utils;

class Response {
    /**
     * Send a JSON response and terminate the script
     */
    public static function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }

    /**
     * Send a standard error response
     */
    public static function error($message, $statusCode = 400) {
        self::json(["message" => $message], $statusCode);
    }

    /**
     * Send a standard success response
     */
    public static function success($message, $data = [], $statusCode = 200) {
        $response = ["message" => $message];
        if (!empty($data)) {
            $response = array_merge($response, $data);
        }
        self::json($response, $statusCode);
    }
}
