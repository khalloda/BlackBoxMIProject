<?php
/**
 * Fixed Authentication System for GoDaddy Plesk
 * 
 * Simplified session handling for better compatibility
 */

namespace App\Core;

use App\Models\User;

class Auth_Fixed
{
    private static $user = null;
    private static $sessionStarted = false;

    /**
     * Start session with minimal configuration
     */
    public static function startSession()
    {
        if (!self::$sessionStarted && session_status() === PHP_SESSION_NONE) {
            // Minimal session configuration for maximum compatibility
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
        
        // Set user session - simplified
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_data'] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'full_name' => $user['full_name'],
            'role' => $user['role']
        ];
        $_SESSION['login_time'] = time();
        $_SESSION['authenticated'] = true;
        
        // Set current user
        self::$user = $_SESSION['user_data'];
        
        // Force session write
        session_write_close();
        session_start();
        
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
     * Check if user is authenticated
     */
    public static function check()
    {
        self::startSession();
        
        // Check multiple indicators
        return self::$user !== null && 
               isset($_SESSION['authenticated']) && 
               $_SESSION['authenticated'] === true &&
               isset($_SESSION['user_id']);
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
        // Simple session loading without timeout checks for now
        if (isset($_SESSION['user_data']) && isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true) {
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
}
