<?php
/**
 * Mystery Hub User Logout
 * Handles user logout and session destruction
 */

// Include configuration
require_once '../config.php';

// Start session
startSession();

// Destroy session
$_SESSION = array();

// If a session cookie is used, destroy it
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Redirect to home page with message
setFlashMessage('info', 'You have been successfully logged out.');
redirect('/MysteryHubProject/index.html');
