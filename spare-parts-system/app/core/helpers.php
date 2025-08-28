<?php
/**
 * Global Helper Functions
 * 
 * This file contains global helper functions that need to be available
 * throughout the application.
 */

if (!function_exists('__')) {
    /**
     * Translate a key (shorthand function)
     * 
     * @param string $key Translation key
     * @param array $params Parameters for substitution
     * @return string Translated text
     */
    function __($key, $params = [])
    {
        // If Language class is available, use it
        if (class_exists('App\Core\Language')) {
            return \App\Core\Language::get($key, $params);
        }
        
        // Fallback: return the key itself
        return $key;
    }
}

if (!function_exists('_n')) {
    /**
     * Translate with pluralization (shorthand function)
     * 
     * @param string $key Translation key
     * @param int $count Count for pluralization
     * @param array $params Parameters for substitution
     * @return string Translated text
     */
    function _n($key, $count, $params = [])
    {
        // If Language class is available, use it
        if (class_exists('App\Core\Language')) {
            return \App\Core\Language::choice($key, $count, $params);
        }
        
        // Fallback: return the key itself
        return $key;
    }
}

if (!function_exists('config')) {
    /**
     * Get configuration value
     * 
     * @param string $key Configuration key
     * @param mixed $default Default value
     * @return mixed Configuration value
     */
    function config($key, $default = null)
    {
        if (class_exists('App\Core\Config')) {
            return \App\Core\Config::get($key, $default);
        }
        
        return $default;
    }
}

if (!function_exists('auth')) {
    /**
     * Get authentication instance or user
     * 
     * @return mixed Auth user or null
     */
    function auth()
    {
        if (class_exists('App\Core\Auth')) {
            return \App\Core\Auth::user();
        }
        
        return null;
    }
}

if (!function_exists('csrf_token')) {
    /**
     * Get CSRF token
     * 
     * @return string CSRF token
     */
    function csrf_token()
    {
        if (class_exists('App\Core\CSRF')) {
            return \App\Core\CSRF::getToken();
        }
        
        return '';
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Get CSRF field HTML
     * 
     * @return string CSRF field HTML
     */
    function csrf_field()
    {
        if (class_exists('App\Core\CSRF')) {
            return \App\Core\CSRF::field();
        }
        
        return '';
    }
}

if (!function_exists('old')) {
    /**
     * Get old input value
     * 
     * @param string $key Input key
     * @param mixed $default Default value
     * @return mixed Old input value
     */
    function old($key, $default = null)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return $_SESSION['old_input'][$key] ?? $default;
    }
}

if (!function_exists('redirect')) {
    /**
     * Redirect to URL
     * 
     * @param string $url URL to redirect to
     * @param int $status HTTP status code
     */
    function redirect($url, $status = 302)
    {
        http_response_code($status);
        header("Location: {$url}");
        exit;
    }
}

if (!function_exists('url')) {
    /**
     * Generate URL
     * 
     * @param string $path URL path
     * @return string Full URL
     */
    function url($path = '')
    {
        $baseUrl = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
        return $baseUrl . '/' . ltrim($path, '/');
    }
}

if (!function_exists('asset')) {
    /**
     * Generate asset URL
     * 
     * @param string $path Asset path
     * @return string Asset URL
     */
    function asset($path)
    {
        return url($path);
    }
}

if (!function_exists('dd')) {
    /**
     * Dump and die (for debugging)
     * 
     * @param mixed ...$vars Variables to dump
     */
    function dd(...$vars)
    {
        echo '<pre>';
        foreach ($vars as $var) {
            var_dump($var);
        }
        echo '</pre>';
        die();
    }
}

if (!function_exists('env')) {
    /**
     * Get environment variable
     * 
     * @param string $key Environment variable key
     * @param mixed $default Default value
     * @return mixed Environment variable value
     */
    function env($key, $default = null)
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
        
        if ($value === false) {
            return $default;
        }
        
        // Convert string booleans
        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'null':
            case '(null)':
                return null;
            case 'empty':
            case '(empty)':
                return '';
        }
        
        return $value;
    }
}
