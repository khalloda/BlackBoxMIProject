<?php
/**
 * Database Setup Script
 * 
 * This script creates the database tables and populates initial data
 */

// Include configuration
require_once __DIR__ . '/app/core/Autoloader.php';
App\Core\Autoloader::register();

use App\Core\Config;

try {
    // Load database configuration
    $config = Config::load('database');
    $dbConfig = $config['default'];
    
    echo "Connecting to database...\n";
    
    // Connect to MySQL server (without database)
    $pdo = new PDO(
        "mysql:host={$dbConfig['host']};port={$dbConfig['port']};charset=utf8mb4",
        $dbConfig['username'],
        $dbConfig['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    echo "Connected successfully!\n";
    
    // Create database if it doesn't exist
    echo "Creating database '{$dbConfig['database']}'...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbConfig['database']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `{$dbConfig['database']}`");
    
    echo "Database created/selected successfully!\n";
    
    // Read and execute schema
    echo "Creating tables from schema.sql...\n";
    $schema = file_get_contents(__DIR__ . '/sql/schema.sql');
    
    // Remove the database creation part since we're using the configured database
    $schema = preg_replace('/SET FOREIGN_KEY_CHECKS = 0;.*?USE spare_parts_system;/s', '', $schema);
    $schema = str_replace('spare_parts_system', $dbConfig['database'], $schema);
    
    // Split into individual statements and execute
    $statements = array_filter(array_map('trim', explode(';', $schema)));
    
    foreach ($statements as $statement) {
        if (!empty($statement) && !preg_match('/^(--|\/\*)/', $statement)) {
            try {
                $pdo->exec($statement);
            } catch (PDOException $e) {
                echo "Warning: " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "Tables created successfully!\n";
    
    // Read and execute seed data
    echo "Inserting seed data...\n";
    $seeds = file_get_contents(__DIR__ . '/sql/seeds.sql');
    $seeds = str_replace('USE spare_parts_system;', '', $seeds);
    
    // Split into individual statements and execute
    $statements = array_filter(array_map('trim', explode(';', $seeds)));
    
    foreach ($statements as $statement) {
        if (!empty($statement) && !preg_match('/^(--|\/\*)/', $statement)) {
            try {
                $pdo->exec($statement);
            } catch (PDOException $e) {
                echo "Warning: " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "Seed data inserted successfully!\n";
    
    // Verify setup
    echo "\nVerifying setup...\n";
    
    // Check users table
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $userCount = $stmt->fetch()['count'];
    echo "Users created: {$userCount}\n";
    
    // Check products table
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM products");
    $productCount = $stmt->fetch()['count'];
    echo "Products created: {$productCount}\n";
    
    // Check clients table
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM clients");
    $clientCount = $stmt->fetch()['count'];
    echo "Clients created: {$clientCount}\n";
    
    echo "\n✅ Database setup completed successfully!\n";
    echo "\nYou can now login with:\n";
    echo "Username: admin\n";
    echo "Password: password\n";
    echo "\nOther test accounts:\n";
    echo "Username: manager, Password: password\n";
    echo "Username: user, Password: password\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Please check your database configuration and try again.\n";
}
