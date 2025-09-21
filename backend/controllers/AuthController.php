<?php

require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class AuthController {
    private $userModel;
    private $authMiddleware;
    
    public function __construct() {
        $this->userModel = new User();
        $this->authMiddleware = new AuthMiddleware();
    }
    
    public function register() {
        cors();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            errorResponse('Method not allowed', 405);
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $input = sanitizeInput($input);
        
        // Validate required fields
        $requiredFields = ['name', 'email', 'password', 'department', 'position', 'employee_id'];
        $errors = validateRequired($input, $requiredFields);
        
        if (!empty($errors)) {
            errorResponse(implode(', ', $errors), 400);
        }
        
        // Validate email format
        if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            errorResponse('Invalid email format', 400);
        }
        
        // Validate password length
        if (strlen($input['password']) < 6) {
            errorResponse('Password must be at least 6 characters', 400);
        }
        
        // Check if user already exists
        if ($this->userModel->exists($input['email'], $input['employee_id'])) {
            errorResponse('User with this email or employee ID already exists', 400);
        }
        
        try {
            $userId = $this->userModel->create($input);
            $user = $this->userModel->findById($userId);
            $token = generateToken($userId);
            
            successResponse([
                'token' => $token,
                'user' => $this->userModel->toArray($user)
            ], 'User registered successfully');
            
        } catch (Exception $e) {
            errorResponse('Registration failed: ' . $e->getMessage(), 500);
        }
    }
    
    public function login() {
        cors();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            errorResponse('Method not allowed', 405);
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $input = sanitizeInput($input);
        
        // Validate required fields
        $requiredFields = ['email', 'password'];
        $errors = validateRequired($input, $requiredFields);
        
        if (!empty($errors)) {
            errorResponse(implode(', ', $errors), 400);
        }
        
        // Validate email format
        if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            errorResponse('Invalid email format', 400);
        }
        
        try {
            $user = $this->userModel->findByEmail($input['email']);
            
            if (!$user || !$this->userModel->verifyPassword($input['password'], $user['password'])) {
                errorResponse('Invalid credentials', 400);
            }
            
            $token = generateToken($user['id']);
            
            successResponse([
                'token' => $token,
                'user' => $this->userModel->toArray($user)
            ], 'Login successful');
            
        } catch (Exception $e) {
            errorResponse('Login failed: ' . $e->getMessage(), 500);
        }
    }
    
    public function me() {
        cors();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            errorResponse('Method not allowed', 405);
        }
        
        try {
            $user = $this->authMiddleware->authenticate();
            successResponse(['user' => $this->userModel->toArray($user)]);
            
        } catch (Exception $e) {
            errorResponse('Authentication failed: ' . $e->getMessage(), 401);
        }
    }
}
