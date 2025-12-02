<?php

require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/LeaveController.php';
require_once __DIR__ . '/../controllers/UserController.php';

class Router {
    private $routes = [];
    
    public function __construct() {
        $this->setupRoutes();
    }
    
    private function setupRoutes() {
        $this->routes['POST']['/api/auth/register'] = ['AuthController', 'register'];
        $this->routes['POST']['/api/auth/login'] = ['AuthController', 'login'];
        $this->routes['POST']['/api/auth/logout'] = ['AuthController', 'logout'];
        $this->routes['GET']['/api/auth/me'] = ['AuthController', 'me'];
        
        $this->routes['POST']['/api/leaves'] = ['LeaveController', 'create'];
        $this->routes['GET']['/api/leaves/my-leaves'] = ['LeaveController', 'myLeaves'];
        $this->routes['GET']['/api/leaves/all'] = ['LeaveController', 'allLeaves'];
        $this->routes['GET']['/api/leaves/today'] = ['LeaveController', 'todayLeaves'];
        $this->routes['GET']['/api/leaves/active'] = ['LeaveController', 'activeLeaves'];
        $this->routes['PUT']['/api/leaves/approve'] = ['LeaveController', 'approve'];
        $this->routes['PUT']['/api/leaves/reject'] = ['LeaveController', 'reject'];
        $this->routes['PUT']['/api/leaves/update'] = ['LeaveController', 'update'];
        $this->routes['DELETE']['/api/leaves/cancel'] = ['LeaveController', 'cancel'];
        $this->routes['GET']['/api/leaves/get'] = ['LeaveController', 'getById'];
        
        $this->routes['GET']['/api/users/stats'] = ['UserController', 'getStats'];
        $this->routes['GET']['/api/users/admin-stats'] = ['UserController', 'getAdminStats'];
        $this->routes['GET']['/api/users/employees'] = ['UserController', 'getEmployees'];
    }
    
    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        $path = str_replace('/php-LMS/backend', '', $path);
        
        $basePath = $path;
        if (strpos($path, '/api/leaves/') === 0) {
            if (preg_match('/\/api\/leaves\/(\d+)\/(approve|reject|update|cancel)/', $path, $matches)) {
                $basePath = '/api/leaves/' . $matches[2];
                $_GET['id'] = $matches[1];
            } elseif (preg_match('/\/api\/leaves\/(\d+)/', $path, $matches)) {
                $basePath = '/api/leaves/get';
                $_GET['id'] = $matches[1];
            }
            // Keep the original path for routes like /api/leaves/active, /api/leaves/today, etc.
            // Only modify if it's a numeric ID route
        }
        
        if (!isset($this->routes[$method][$basePath])) {
            errorResponse('Route not found', 404);
        }
        
        $route = $this->routes[$method][$basePath];
        $controllerName = $route[0];
        $methodName = $route[1];
        
        if (!class_exists($controllerName)) {
            errorResponse('Controller not found: ' . $controllerName, 500);
            return;
        }
        
        try {
            $controller = new $controllerName();
            
            if (!method_exists($controller, $methodName)) {
                errorResponse('Method not found: ' . $methodName . ' in ' . $controllerName, 500);
                return;
            }
            
            $controller->$methodName();
        } catch (Exception $e) {
            errorResponse('Internal server error: ' . $e->getMessage(), 500);
        } catch (Error $e) {
            errorResponse('Fatal error: ' . $e->getMessage(), 500);
        }
    }
}

$router = new Router();
$router->handleRequest();
