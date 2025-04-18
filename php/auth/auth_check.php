<?php
/**
 * Mystery Hub Authentication Check
 * Include this file on pages that require user authentication
 */

// Include configuration
require_once dirname(__DIR__) . '/config.php';

// Start session
startSession();

// Check if user is logged in
if (!isLoggedIn()) {
    // Store the current URL for redirect after login
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    
    // Set error message
    setFlashMessage('error', 'You must be logged in to access this page.');
    
    // Redirect to login page
    redirect('/MysteryHubProject/Pages/login.html');
    exit;
}
