<?php
/**
 * Mystery Hub User Login
 * Handles user authentication
 */

// Include configuration
require_once '../config.php';

// Start session
startSession();

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validate form data
    $errors = [];
    
    if (empty($username)) {
        $errors[] = "Username is required";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    }
    
    // If no validation errors, attempt login
    if (empty($errors)) {
        // Load user data
        $usersFile = USERS_DIR . '/users.json';
        $users = loadJsonData($usersFile);
        
        // Search for matching user
        $authenticated = false;
        foreach ($users as $user) {
            if ($user['username'] === $username && password_verify($password, $user['password'])) {
                // Set user session data
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['firstname'] = $user['firstname'];
                $_SESSION['lastname'] = $user['lastname'];
                $_SESSION['is_admin'] = $user['is_admin'] ?? false;
                $_SESSION['last_login'] = date('Y-m-d H:i:s');
                
                // Update last login time in users.json
                foreach ($users as &$u) {
                    if ($u['id'] === $user['id']) {
                        $u['last_login'] = $_SESSION['last_login'];
                        $u['updated_at'] = date('Y-m-d H:i:s');
                        break;
                    }
                }
                saveJsonData($usersFile, $users);
                
                $authenticated = true;
                break;
            }
        }
        
        if ($authenticated) {
            // Successful login
            setFlashMessage('success', 'Login successful! Welcome, ' . $_SESSION['firstname'] . '!');
            
            // Redirect based on user role
            if ($_SESSION['is_admin']) {
                redirect('/MysteryHubProject/admin/dashboard.php');
            } else {
                redirect('/MysteryHubProject/user/dashboard.php');
            }
        } else {
            // Failed login
            setFlashMessage('error', 'Invalid username or password');
            redirect('/MysteryHubProject/Pages/login.html');
        }
    } else {
        // Validation errors
        foreach ($errors as $error) {
            setFlashMessage('error', $error);
        }
        redirect('/MysteryHubProject/Pages/login.html');
    }
} else {
    // If not a POST request, redirect to login form
    redirect('/MysteryHubProject/Pages/login.html');
}
