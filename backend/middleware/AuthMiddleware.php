<?php

require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../models/User.php';

class AuthMiddleware {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    public function authenticate() {
        $headers = getallheaders();
        $token = null;
        
        // Check for Authorization header
        if (isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];
            if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
                $token = $matches[1];
            }
        }
        
        // Check for token in query parameter (fallback)
        if (!$token && isset($_GET['token'])) {
            $token = $_GET['token'];
        }
        
        if (!$token) {
            errorResponse('Access token required', 401);
        }
        
        $payload = verifyToken($token);
        if (!$payload) {
            errorResponse('Invalid or expired token', 401);
        }
        
        $user = $this->userModel->findById($payload['userId']);
        if (!$user) {
            errorResponse('User not found', 401);
        }
        
        return $user;
    }
    
    public function adminOnly() {
        $user = $this->authenticate();
        
        if ($user['role'] !== 'hr') {
            errorResponse('Admin access required', 403);
        }
        
        return $user;
    }
    
    public function optionalAuth() {
        $headers = getallheaders();
        $token = null;
        
        if (isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];
            if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
                $token = $matches[1];
            }
        }
        
        if (!$token) {
            return null;
        }
        
        $payload = verifyToken($token);
        if (!$payload) {
            return null;
        }
        
        return $this->userModel->findById($payload['userId']);
    }
}
