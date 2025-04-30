<?php
/**
 * Mystery Hub User Login
 * Handles user authentication
 */

// Start session
session_start();

// Data directory paths
define('DATA_DIR', dirname(dirname(__DIR__)) . '/Backend/Data');
define('USERS_DIR', DATA_DIR . '/users');

// Helper functions
function loadJsonData($file) {
    if (!file_exists($file)) {
        return [];
    }
    
    $jsonData = file_get_contents($file);
    return json_decode($jsonData, true) ?: [];
}

function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function setFlashMessage($type, $message) {
    if (!isset($_SESSION['flash_messages'])) {
        $_SESSION['flash_messages'] = [];
    }
    
    if (!isset($_SESSION['flash_messages'][$type])) {
        $_SESSION['flash_messages'][$type] = [];
    }
    
    $_SESSION['flash_messages'][$type][] = $message;
}

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
                
                // Save updated users data
                $jsonData = json_encode($users, JSON_PRETTY_PRINT);
                file_put_contents($usersFile, $jsonData);
                
                $authenticated = true;
                break;
            }
        }
        
        if ($authenticated) {
            // Successful login
            setFlashMessage('success', 'Login successful! Welcome, ' . $_SESSION['firstname'] . '!');
            
            // Redirect to dashboard/profile
            header("Location: ../../Pages/profile.html");
            exit;
        } else {
            // Failed login
            setFlashMessage('error', 'Invalid username or password');
            header("Location: ../../Pages/login.html");
            exit;
        }
    } else {
        // Validation errors
        foreach ($errors as $error) {
            setFlashMessage('error', $error);
        }
        header("Location: ../../Pages/login.html");
        exit;
    }
} else {
    // If not a POST request, redirect to login form
    header("Location: ../../Pages/login.html");
}

