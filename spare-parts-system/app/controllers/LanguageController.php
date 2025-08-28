<?php
/**
 * Language Controller
 * 
 * Handles language switching functionality
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Language;

class LanguageController extends Controller
{
    /**
     * Switch language
     */
    public function switch($lang)
    {
        // Validate language
        if (!in_array($lang, Language::getSupportedLanguages())) {
            $this->flash('error', 'Invalid language selected');
            return $this->back();
        }
        
        // Set language
        if (Language::setLanguage($lang)) {
            $this->flash('success', 'Language changed successfully');
        } else {
            $this->flash('error', 'Failed to change language');
        }
        
        // Redirect back to previous page
        return $this->back('/dashboard');
    }
}
