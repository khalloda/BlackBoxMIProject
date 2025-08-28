<?php
/**
 * Error Controller
 * 
 * Handles error pages and HTTP status codes
 */

namespace App\Controllers;

use App\Core\Controller;

class ErrorController extends Controller
{
    /**
     * 404 Not Found
     */
    public function notFound()
    {
        http_response_code(404);
        
        $this->setTitle('404 - Page Not Found');
        
        return $this->view('errors/404', [
            'requested_url' => $_SERVER['REQUEST_URI'] ?? '/'
        ]);
    }
    
    /**
     * 403 Forbidden
     */
    public function forbidden()
    {
        http_response_code(403);
        
        $this->setTitle('403 - Forbidden');
        
        return $this->view('errors/403');
    }
    
    /**
     * 500 Internal Server Error
     */
    public function serverError()
    {
        http_response_code(500);
        
        $this->setTitle('500 - Internal Server Error');
        
        return $this->view('errors/500');
    }
    
    /**
     * 401 Unauthorized
     */
    public function unauthorized()
    {
        http_response_code(401);
        
        $this->setTitle('401 - Unauthorized');
        
        return $this->view('errors/401');
    }
}
