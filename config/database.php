<?php
class Database {
    private static $connection = null;
    
    public static function connect() {
        if (self::$connection === null) {
            try {
                // Database Configuration
                // For LOCAL development (XAMPP):
                $host = 'localhost';
                $dbname = 'store_management_system';
                $username = 'root';
                $password = '';
                
                self::$connection = new PDO(
                    "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
                    $username,
                    $password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );
            } catch(PDOException $e) {
                error_log("Database connection failed: " . $e->getMessage());
                die("Unable to connect to database. Please contact support.");
            }
        }
        return self::$connection;
    }
}
?>