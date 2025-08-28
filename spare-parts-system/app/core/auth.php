<?php
/**
 * Fixed Authentication System - No Redirect Loop
 * 
 * Simplified session handling with proper cookie configuration
 */

namespace App\Core;

use App\Models\User;

class Auth
{
    private static $user = null;
    private static $sessionStarted = false;

    /**
     * Start session with proper configuration
     */
    public static function startSession()
    {
        if (!self::$sessionStarted && session_status() === PHP_SESSION_NONE) {
            // Configure session BEFORE starting
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'domain' => '',
                'secure' => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            
            session_name('SPMS_SESSION');
            session_start();
            self::$sessionStarted = true;
            
            // Load user from session
            self::loadUserFromSession();
        }
    }

    /**
     * Attempt to log in a user
     */
    public static function login($username, $password, $remember = false)
    {
        self::startSession();
        
        // Find user by username or email
        $userModel = new User();
        $user = $userModel->findByUsernameOrEmail($username);
        
        if (!$user || !$user['is_active']) {
            return false;
        }
        
        // Verify password
        if (!password_verify($password, $user['password_hash'])) {
            return false;
        }
        
        // Regenerate session ID for security
        session_regenerate_id(true);
        
        // Set user session - SIMPLE and RELIABLE
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_data'] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'full_name' => $user['full_name'],
            'role' => $user['role']
        ];
        $_SESSION['authenticated'] = true;
        $_SESSION['login_time'] = time();
        
        // Set current user
        self::$user = $_SESSION['user_data'];
        
        return true;
    }

    /**
     * Log out the current user
     */
    public static function logout()
    {
        self::startSession();
        
        // Clear session
        $_SESSION = [];
        session_destroy();
        
        // Clear user
        self::$user = null;
        self::$sessionStarted = false;
    }

    /**
     * Check if user is authenticated - SIMPLE CHECK
     */
    public static function check()
    {
        self::startSession();
        
        // Simple, reliable check
        return isset($_SESSION['user_id']) && 
               isset($_SESSION['authenticated']) && 
               $_SESSION['authenticated'] === true;
    }

    /**
     * Get current authenticated user
     */
    public static function user()
    {
        self::startSession();
        return self::$user;
    }

    /**
     * Get current user ID
     */
    public static function id()
    {
        $user = self::user();
        return $user ? $user['id'] : null;
    }

    /**
     * Load user from session
     */
    private static function loadUserFromSession()
    {
        if (isset($_SESSION['user_data']) && 
            isset($_SESSION['authenticated']) && 
            $_SESSION['authenticated'] === true &&
            isset($_SESSION['user_id'])) {
            self::$user = $_SESSION['user_data'];
        }
    }

    /**
     * Require authentication
     */
    public static function requireAuth($redirectUrl = '/login')
    {
        if (!self::check()) {
            header('Location: ' . $redirectUrl);
            exit;
        }
    }

    /**
     * Hash a password
     */
    public static function hashPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Verify a password
     */
    public static function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }

    /**
     * Check if user has specific role
     */
    public static function hasRole($role)
    {
        $user = self::user();
        return $user && $user['role'] === $role;
    }

    /**
     * Check if user has any of the specified roles
     */
    public static function hasAnyRole($roles)
    {
        $user = self::user();
        return $user && in_array($user['role'], $roles);
    }

    /**
     * Require specific role
     */
    public static function requireRole($role, $redirectUrl = '/unauthorized')
    {
        self::requireAuth();
        
        if (!self::hasRole($role)) {
            header('Location: ' . $redirectUrl);
            exit;
        }
    }

    /**
     * Require any of the specified roles
     */
    public static function requireAnyRole($roles, $redirectUrl = '/unauthorized')
    {
        self::requireAuth();
        
        if (!self::hasAnyRole($roles)) {
            header('Location: ' . $redirectUrl);
            exit;
        }
    }

    // Stub methods for compatibility with existing AuthController
    public static function getRemainingLockoutTime($username) { return 0; }
    public static function getFailedAttempts($username) { return 0; }
}
