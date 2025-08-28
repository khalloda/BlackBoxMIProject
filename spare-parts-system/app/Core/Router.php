<?php
/**
 * MVC Router with Fixed Regex and Parameter Support
 * 
 * FIXED: Regex syntax errors and PSR-4 compatibility
 */

namespace App\Core;

class Router
{
    private static $routes = [];
    private static $middlewares = [];
    private static $currentRoute = null;
    private static $basePath = '';

    /**
     * Set the base path for all routes
     */
    public static function setBasePath($basePath)
    {
        self::$basePath = rtrim($basePath, '/');
    }

    /**
     * Add a GET route
     */
    public static function get($pattern, $handler, $middleware = [])
    {
        self::addRoute('GET', $pattern, $handler, $middleware);
    }

    /**
     * Add a POST route
     */
    public static function post($pattern, $handler, $middleware = [])
    {
        self::addRoute('POST', $pattern, $handler, $middleware);
    }

    /**
     * Add a PUT route
     */
    public static function put($pattern, $handler, $middleware = [])
    {
        self::addRoute('PUT', $pattern, $handler, $middleware);
    }

    /**
     * Add a DELETE route
     */
    public static function delete($pattern, $handler, $middleware = [])
    {
        self::addRoute('DELETE', $pattern, $handler, $middleware);
    }

    /**
     * Add a route for any HTTP method
     */
    private static function addRoute($method, $pattern, $handler, $middleware = [])
    {
        // Normalize pattern
        $pattern = self::normalizePattern($pattern);
        
        self::$routes[] = [
            'method' => strtoupper($method),
            'pattern' => $pattern,
            'handler' => $handler,
            'middleware' => $middleware,
            'regex' => self::patternToRegex($pattern),
            'params' => self::extractParamNames($pattern)
        ];
    }

    /**
     * Add middleware to be applied to all routes
     */
    public static function addGlobalMiddleware($middleware)
    {
        self::$middlewares[] = $middleware;
    }

    /**
     * Dispatch the current request
     */
    public static function dispatch($requestUri = null, $requestMethod = null)
    {
        // Get request details
        $requestUri = $requestUri ?: $_SERVER['REQUEST_URI'];
        $requestMethod = $requestMethod ?: $_SERVER['REQUEST_METHOD'];
        
        // Remove base path and query string
        $path = self::getPathFromUri($requestUri);
        
        // Find matching route
        $route = self::findRoute($requestMethod, $path);
        
        if (!$route) {
            return self::handleNotFound();
        }

        self::$currentRoute = $route;

        try {
            // Apply global middleware
            foreach (self::$middlewares as $middleware) {
                $result = call_user_func($middleware);
                if ($result !== null) {
                    return $result;
                }
            }

            // Apply route-specific middleware
            foreach ($route['middleware'] as $middleware) {
                $result = call_user_func($middleware);
                if ($result !== null) {
                    return $result;
                }
            }

            // Execute handler
            return self::executeHandler($route['handler'], $route['params']);

        } catch (Exception $e) {
            return self::handleError($e);
        }
    }

    /**
     * Normalize URL pattern
     */
    private static function normalizePattern($pattern)
    {
        // Ensure pattern starts with /
        if (!str_starts_with($pattern, '/')) {
            $pattern = '/' . $pattern;
        }
        
        // Remove trailing slash except for root
        if ($pattern !== '/' && str_ends_with($pattern, '/')) {
            $pattern = rtrim($pattern, '/');
        }
        
        return $pattern;
    }

    /**
     * Convert URL pattern to regex - FIXED
     */
    private static function patternToRegex($pattern)
    {
        // Escape special regex characters using # as delimiter
        $regex = preg_quote($pattern, '#');
        
        // Replace parameter placeholders with regex groups
        // FIXED: Proper escaping of braces in preg_quote, then replace
        $regex = preg_replace('#\\\{([a-zA-Z_][a-zA-Z0-9_]*)\\\}#', '([^/]+)', $regex);
        
        // Add start and end anchors with optional trailing slash
        $regex = '#^' . $regex . '/?$#';
        
        return $regex;
    }

    /**
     * Extract parameter names from pattern
     */
    private static function extractParamNames($pattern)
    {
        preg_match_all('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', $pattern, $matches);
        return $matches[1];
    }

    /**
     * Get path from request URI
     */
    private static function getPathFromUri($requestUri)
    {
        // Remove query string
        $path = parse_url($requestUri, PHP_URL_PATH);
        
        // Remove base path
        if (self::$basePath && str_starts_with($path, self::$basePath)) {
            $path = substr($path, strlen(self::$basePath));
        }
        
        // Ensure path starts with /
        if (!str_starts_with($path, '/')) {
            $path = '/' . $path;
        }
        
        // Normalize trailing slash
        if ($path !== '/' && str_ends_with($path, '/')) {
            $path = rtrim($path, '/');
        }
        
        return $path;
    }

    /**
     * Find matching route - FIXED
     */
    private static function findRoute($method, $path)
    {
        foreach (self::$routes as $route) {
            if ($route['method'] === $method || $route['method'] === 'ANY') {
                // FIXED: Proper preg_match with correct parameters
                if (preg_match($route['regex'], $path, $matches)) {
                    // Extract parameter values
                    $params = [];
                    for ($i = 1; $i < count($matches); $i++) {
                        if (isset($route['params'][$i - 1])) {
                            $params[$route['params'][$i - 1]] = $matches[$i];
                        }
                    }
                    
                    $route['params'] = $params;
                    return $route;
                }
            }
        }
        
        return null;
    }

    /**
     * Execute route handler - FIXED for PSR-4
     */
    private static function executeHandler($handler, $params = [])
    {
        if (is_callable($handler)) {
            return call_user_func_array($handler, array_values($params));
        }
        
        if (is_string($handler) && str_contains($handler, '@')) {
            [$controllerName, $methodName] = explode('@', $handler, 2);
            
            // Add namespace if not present - FIXED for PSR-4
            if (!str_contains($controllerName, '\\')) {
                $controllerName = 'App\\Controllers\\' . $controllerName;
            }
            
            if (!class_exists($controllerName)) {
                throw new \Exception("Controller {$controllerName} not found");
            }
            
            $controller = new $controllerName();
            
            if (!method_exists($controller, $methodName)) {
                throw new \Exception("Method {$methodName} not found in {$controllerName}");
            }
            
            return call_user_func_array([$controller, $methodName], array_values($params));
        }
        
        throw new \Exception("Invalid handler format");
    }

    /**
     * Handle 404 Not Found
     */
    private static function handleNotFound()
    {
        http_response_code(404);
        
        // Try to load 404 controller
        if (class_exists('App\\Controllers\\ErrorController')) {
            $controller = new \App\Controllers\ErrorController();
            if (method_exists($controller, 'notFound')) {
                return $controller->notFound();
            }
        }
        
        return "404 - Page Not Found";
    }

    /**
     * Handle errors
     */
    private static function handleError($e)
    {
        http_response_code(500);
        
        // Try to load error controller
        if (class_exists('App\\Controllers\\ErrorController')) {
            $controller = new \App\Controllers\ErrorController();
            if (method_exists($controller, 'error')) {
                return $controller->error($e);
            }
        }
        
        // In development, show detailed error
        if (defined('APP_DEBUG') && APP_DEBUG) {
            return "Error: " . $e->getMessage() . "\n" . $e->getTraceAsString();
        }
        
        return "500 - Internal Server Error";
    }

    /**
     * Generate URL for named route
     */
    public static function url($pattern, $params = [])
    {
        $url = $pattern;
        
        // Replace parameters in URL
        foreach ($params as $key => $value) {
            $url = str_replace('{' . $key . '}', $value, $url);
        }
        
        return self::$basePath . $url;
    }

    /**
     * Get current route
     */
    public static function getCurrentRoute()
    {
        return self::$currentRoute;
    }

    /**
     * Get all registered routes
     */
    public static function getRoutes()
    {
        return self::$routes;
    }

    /**
     * Clear all routes (useful for testing)
     */
    public static function clearRoutes()
    {
        self::$routes = [];
        self::$middlewares = [];
        self::$currentRoute = null;
    }
}
