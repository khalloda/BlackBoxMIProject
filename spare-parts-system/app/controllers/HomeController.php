<?php
/**
 * Home Controller
 * 
 * Handles the main landing page and redirects authenticated users to dashboard.
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;

class HomeController extends Controller
{
    /**
     * Show home page or redirect to dashboard
     */
    public function index()
    {
        // Temporarily always redirect to dashboard for testing
        $this->redirect('/dashboard');
        return;
        
        // Original logic (commented out for testing)
        /*
        // If user is authenticated, redirect to dashboard
        if (Auth::check()) {
            $this->redirect('/dashboard');
            return;
        }
        
        // Show login page for unauthenticated users
        $this->redirect('/login');
        */
    }
}
