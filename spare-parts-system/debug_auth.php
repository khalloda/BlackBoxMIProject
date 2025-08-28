<?php
/**
 * Debug Authentication Script
 * 
 * This script helps debug session and authentication issues
 */

// Include configuration
require_once __DIR__ . '/app/core/Autoloader.php';
App\Core\Autoloader::register();

use App\Core\Auth;
use App\Models\User;

// Start session debugging
echo "<h2>Session Debug Information</h2>";

// Check session status before starting
echo "<p><strong>Session Status Before:</strong> " . session_status() . "</p>";
echo "<p><strong>Session ID Before:</strong> " . session_id() . "</p>";

// Start Auth session
Auth::startSession();

echo "<p><strong>Session Status After:</strong> " . session_status() . "</p>";
echo "<p><strong>Session ID After:</strong> " . session_id() . "</p>";
echo "<p><strong>Session Name:</strong> " . session_name() . "</p>";

// Check session data
echo "<h3>Session Data:</h3>";
echo "<pre>";
print_r($_SESSION ?? []);
echo "</pre>";

// Check cookies
echo "<h3>Cookies:</h3>";
echo "<pre>";
print_r($_COOKIE ?? []);
echo "</pre>";

// Check Auth status
echo "<h3>Auth Status:</h3>";
echo "<p><strong>Auth::check():</strong> " . (Auth::check() ? 'TRUE' : 'FALSE') . "</p>";
echo "<p><strong>Auth::user():</strong></p>";
echo "<pre>";
print_r(Auth::user());
echo "</pre>";

// Test login
if (isset($_POST['test_login'])) {
    echo "<h3>Testing Login:</h3>";
    $result = Auth::login('admin', 'password');
    echo "<p><strong>Login Result:</strong> " . ($result ? 'SUCCESS' : 'FAILED') . "</p>";
    
    if ($result) {
        echo "<p><strong>Auth::check() after login:</strong> " . (Auth::check() ? 'TRUE' : 'FALSE') . "</p>";
        echo "<p><strong>Auth::user() after login:</strong></p>";
        echo "<pre>";
        print_r(Auth::user());
        echo "</pre>";
        
        echo "<p><strong>Session data after login:</strong></p>";
        echo "<pre>";
        print_r($_SESSION ?? []);
        echo "</pre>";
    }
}

// Test database connection
echo "<h3>Database Test:</h3>";
try {
    $userModel = new User();
    $user = $userModel->findByUsernameOrEmail('admin');
    if ($user) {
        echo "<p><strong>Database Connection:</strong> SUCCESS</p>";
        echo "<p><strong>Admin User Found:</strong> YES</p>";
        echo "<p><strong>User Active:</strong> " . ($user['is_active'] ? 'YES' : 'NO') . "</p>";
        echo "<p><strong>Password Hash:</strong> " . substr($user['password_hash'], 0, 20) . "...</p>";
        
        // Test password verification
        $passwordTest = password_verify('password', $user['password_hash']);
        echo "<p><strong>Password 'password' matches:</strong> " . ($passwordTest ? 'YES' : 'NO') . "</p>";
    } else {
        echo "<p><strong>Admin User Found:</strong> NO</p>";
    }
} catch (Exception $e) {
    echo "<p><strong>Database Error:</strong> " . $e->getMessage() . "</p>";
}

// Server information
echo "<h3>Server Information:</h3>";
echo "<p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>";
echo "<p><strong>Server Software:</strong> " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "</p>";
echo "<p><strong>HTTPS:</strong> " . (isset($_SERVER['HTTPS']) ? 'YES' : 'NO') . "</p>";
echo "<p><strong>Session Save Path:</strong> " . session_save_path() . "</p>";
echo "<p><strong>Session Cookie Params:</strong></p>";
echo "<pre>";
print_r(session_get_cookie_params());
echo "</pre>";

?>

<!DOCTYPE html>
<html>
<head>
    <title>Auth Debug</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }
        .test-form { background: #e8f4fd; padding: 15px; border-radius: 5px; margin: 20px 0; }
    </style>
</head>
<body>
    <h1>Authentication Debug Tool</h1>
    
    <div class="test-form">
        <h3>Test Login</h3>
        <form method="POST">
            <button type="submit" name="test_login" value="1">Test Login (admin/password)</button>
        </form>
    </div>
    
    <div class="test-form">
        <h3>Manual Session Test</h3>
        <p>Visit this page, then go to <a href="/dashboard" target="_blank">/dashboard</a> to test if session persists.</p>
    </div>
</body>
</html>
