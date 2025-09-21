<?php
/**
 * Setup script for PHP Leave Management System
 * Run this script to initialize the database and create sample data
 */

// Load configuration
require_once __DIR__ . '/config/helpers.php';

// Database configuration
$host = env('DB_HOST', 'localhost');
$dbname = env('DB_DATABASE', 'lms_db');
$username = env('DB_USERNAME', 'root');
$password = env('DB_PASSWORD', '');

echo "PHP Leave Management System Setup\n";
echo "==================================\n\n";

try {
    // Connect to MySQL server (without database)
    $dsn = "mysql:host=$host;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    echo "✓ Connected to MySQL server\n";
    
    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✓ Database '$dbname' created/verified\n";
    
    // Connect to the specific database
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    // Read and execute schema
    $schema = file_get_contents(__DIR__ . '/database/schema.sql');
    $statements = explode(';', $schema);
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement) && !preg_match('/^--/', $statement)) {
            $pdo->exec($statement);
        }
    }
    
    echo "✓ Database schema imported successfully\n";
    echo "✓ Sample data inserted\n";
    
    echo "\nSetup completed successfully!\n\n";
    echo "Default credentials:\n";
    echo "HR Admin: admin@company.com / password\n";
    echo "Employee: john@company.com / password\n\n";
    echo "You can now access the application at:\n";
    echo "Frontend: http://localhost/php-LMS/frontend/\n";
    echo "Backend API: http://localhost/php-LMS/backend/\n";
    
} catch (PDOException $e) {
    echo "✗ Database error: " . $e->getMessage() . "\n";
    echo "Please check your database configuration in the .env file\n";
    exit(1);
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
