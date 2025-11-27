<?php

require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../models/User.php';

class AuthMiddleware {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    public function authenticate() {
        $user = getSessionUser();
        
        if (!$user) {
            errorResponse('Please login first', 401);
        }
        
        $currentUser = $this->userModel->findById($user['id']);
        if (!$currentUser) {
            clearSession();
            errorResponse('User not found', 401);
        }
        
        return $currentUser;
    }
    
    public function adminOnly() {
        $user = $this->authenticate();
        
        if ($user['role'] !== 'hr') {
            errorResponse('Admin access required', 403);
        }
        
        return $user;
    }
    
    public function optionalAuth() {
        $user = getSessionUser();
        if (!$user) {
            return null;
        }
        
        return $this->userModel->findById($user['id']);
    }
}
