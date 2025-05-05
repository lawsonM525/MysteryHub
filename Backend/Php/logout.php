<?php
/**
 * Mystery Hub User Logout
 * Handles user logout and session destruction
 */

// Start session
session_start();

// Prepare response array
$response = array('success' => true, 'message' => 'Successfully logged out');

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

// Return JSON response instead of redirecting
header('Content-Type: application/json');
echo json_encode($response);
exit;
