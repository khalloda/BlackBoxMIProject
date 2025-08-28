<?php
/**
 * Debug Controller
 * 
 * For debugging authentication and session issues
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;

class DebugController extends Controller
{
    /**
     * Show debug information
     */
    public function index()
    {
        // Start session manually to check
        Auth::startSession();
        
        $debugInfo = [
            'session_status' => session_status(),
            'session_id' => session_id(),
            'session_data' => $_SESSION ?? [],
            'auth_check' => Auth::check(),
            'auth_user' => Auth::user(),
            'cookies' => $_COOKIE ?? [],
            'server_https' => isset($_SERVER['HTTPS']),
            'session_name' => session_name()
        ];
        
        header('Content-Type: application/json');
        echo json_encode($debugInfo, JSON_PRETTY_PRINT);
        exit;
    }
}
