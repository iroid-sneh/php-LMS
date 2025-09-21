<?php

require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class UserController {
    private $userModel;
    private $authMiddleware;
    
    public function __construct() {
        $this->userModel = new User();
        $this->authMiddleware = new AuthMiddleware();
    }
    
    public function getStats() {
        cors();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            errorResponse('Method not allowed', 405);
        }
        
        try {
            $user = $this->authMiddleware->authenticate();
            $stats = $this->userModel->getStats($user['id']);
            
            successResponse($stats);
            
        } catch (Exception $e) {
            errorResponse('Failed to fetch user statistics: ' . $e->getMessage(), 500);
        }
    }
    
    public function getAdminStats() {
        cors();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            errorResponse('Method not allowed', 405);
        }
        
        try {
            $this->authMiddleware->adminOnly();
            $stats = $this->userModel->getAdminStats();
            $todayLeavesDetails = $this->userModel->getTodayLeavesDetails();
            
            $stats['today_leaves_details'] = array_map([$this->userModel, 'toArray'], $todayLeavesDetails);
            
            successResponse($stats);
            
        } catch (Exception $e) {
            errorResponse('Failed to fetch admin statistics: ' . $e->getMessage(), 500);
        }
    }
    
    public function getEmployees() {
        cors();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            errorResponse('Method not allowed', 405);
        }
        
        try {
            $this->authMiddleware->adminOnly();
            $employees = $this->userModel->findAll('employee');
            
            $formattedEmployees = array_map([$this->userModel, 'toArray'], $employees);
            successResponse($formattedEmployees);
            
        } catch (Exception $e) {
            errorResponse('Failed to fetch employees: ' . $e->getMessage(), 500);
        }
    }
}
