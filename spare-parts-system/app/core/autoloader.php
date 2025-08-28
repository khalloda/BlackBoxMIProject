<?php
/**
 * PSR-4 Autoloader with Lowercase Filesystem Support
 * 
 * Maps App\ namespace to app/ directory with all lowercase files
 */

namespace App\Core;

class Autoloader
{
    private static $prefixes = [];
    private static $registered = false;

    /**
     * Register the autoloader
     */
    public static function register()
    {
        if (!self::$registered) {
            spl_autoload_register([__CLASS__, 'loadClass']);
            self::$registered = true;
            
            // Register App namespace to app directory
            self::addNamespace('App\\', __DIR__ . '/../');
        }
    }

    /**
     * Add a namespace prefix
     */
    public static function addNamespace($prefix, $baseDir)
    {
        // Normalize namespace prefix
        $prefix = trim($prefix, '\\') . '\\';
        
        // Normalize base directory with trailing separator
        $baseDir = rtrim($baseDir, DIRECTORY_SEPARATOR) . '/';
        
        // Initialize the namespace prefix array
        if (!isset(self::$prefixes[$prefix])) {
            self::$prefixes[$prefix] = [];
        }
        
        // Add the base directory to the namespace prefix array
        array_push(self::$prefixes[$prefix], $baseDir);
    }

    /**
     * Load a class file for a given class name
     */
    public static function loadClass($class)
    {
        // Work backwards through the namespace names to find a match
        $prefix = $class;
        while (false !== $pos = strrpos($prefix, '\\')) {
            // Retain the trailing namespace separator in the prefix
            $prefix = substr($class, 0, $pos + 1);
            
            // The rest is the relative class name
            $relativeClass = substr($class, $pos + 1);
            
            // Try to load a mapped file for the prefix and relative class
            $mappedFile = self::loadMappedFile($prefix, $relativeClass);
            if ($mappedFile) {
                return $mappedFile;
            }
            
            // Remove the trailing namespace separator for the next iteration
            $prefix = rtrim($prefix, '\\');
        }
        
        return false;
    }

    /**
     * Load the mapped file for a namespace prefix and relative class
     */
    private static function loadMappedFile($prefix, $relativeClass)
    {
        // Are there any base directories for this namespace prefix?
        if (!isset(self::$prefixes[$prefix])) {
            return false;
        }

        // Look through base directories for this namespace prefix
        foreach (self::$prefixes[$prefix] as $baseDir) {
            // Convert namespace to lowercase filesystem path
            $file = $baseDir . self::namespaceToLowercasePath($relativeClass) . '.php';
            
            // If the mapped file exists, require it
            if (self::requireFile($file)) {
                return $file;
            }
        }
        
        return false;
    }

    /**
     * Convert namespace path to lowercase filesystem path
     */
    private static function namespaceToLowercasePath($relativeClass)
    {
        // Replace namespace separators with directory separators
        $path = str_replace('\\', '/', $relativeClass);
        
        // Convert entire path to lowercase
        return strtolower($path);
    }

    /**
     * If a file exists, require it from the file system
     */
    private static function requireFile($file)
    {
        if (file_exists($file)) {
            require $file;
            return true;
        }
        return false;
    }

    /**
     * Get registered prefixes (for debugging)
     */
    public static function getPrefixes()
    {
        return self::$prefixes;
    }

    /**
     * Clear all registered prefixes (for testing)
     */
    public static function clearPrefixes()
    {
        self::$prefixes = [];
    }
}
