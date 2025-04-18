// This file will accept POST data from the register form (like name, email, password).
// Validate to make sure the email isn't already registered and Sanitize the data 
// Save the new user into the users.json file
<?php

/**
 * Mystery Hub User Registration
 * Handles new user registration
 */

// Start session
session_start();

// Define direct file paths - this is more reliable than constructing them
define('DATA_DIR', '/Applications/MAMP/htdocs/MysteryHubProject/Backend/Data');
define('USERS_DIR', DATA_DIR . '/users');
define('USERS_FILE', USERS_DIR . '/users.json');

// Debug information
error_log("DATA_DIR path: " . DATA_DIR);
error_log("USERS_DIR path: " . USERS_DIR);
error_log("USERS_FILE path: " . USERS_FILE);

// Ensure data directories exist
if (!file_exists(DATA_DIR)) {
    mkdir(DATA_DIR, 0755, true);
    error_log("Created DATA_DIR: " . DATA_DIR);
}
if (!file_exists(USERS_DIR)) {
    mkdir(USERS_DIR, 0755, true);
    error_log("Created USERS_DIR: " . USERS_DIR);
}

// Initialize users.json if it doesn't exist
if (!file_exists(USERS_FILE)) {
    if (file_put_contents(USERS_FILE, json_encode([])) === false) {
        error_log("Failed to create users.json");
    } else {
        error_log("Created users.json");
    }
}

/**
 * Helper Functions
 */
function loadJsonData($file) {
    if (!file_exists($file)) {
        error_log("File does not exist: " . $file);
        return [];
    }
    
    $jsonData = file_get_contents($file);
    if ($jsonData === false) {
        error_log("Failed to read file: " . $file);
        return [];
    }
    
    $data = json_decode($jsonData, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("JSON decode error: " . json_last_error_msg());
        return [];
    }
    
    return $data ?: [];
}

function saveJsonData($file, $data) {
    $jsonData = json_encode($data, JSON_PRETTY_PRINT);
    if ($jsonData === false) {
        error_log("JSON encode error: " . json_last_error_msg());
        return false;
    }
    
    $result = file_put_contents($file, $jsonData);
    if ($result === false) {
        error_log("Failed to write file: " . $file);
        return false;
    }
    
    error_log("Successfully wrote to file: " . $file);
    return true;
}

function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function generateUniqueId() {
    return uniqid('mh_', true);
}

function redirect($url) {
    error_log("Redirecting to: " . $url);
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
    error_log("Flash message set: " . $type . " - " . $message);
}

// Debug POST data
error_log("Registration POST data: " . print_r($_POST, true));

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize form data
    $firstname = sanitizeInput($_POST['firstname'] ?? '');
    $lastname = sanitizeInput($_POST['lastname'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $expertise = sanitizeInput($_POST['security_question'] ?? '');
    
    // Form validation
    $errors = [];
    
    if (empty($firstname)) {
        $errors[] = "First name is required";
    }
    
    if (empty($lastname)) {
        $errors[] = "Last name is required";
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required";
    }
    
    if (empty($username) || strlen($username) < 3) {
        $errors[] = "Username is required and must be at least 3 characters";
    }
    
    if (empty($password) || strlen($password) < 6) {
        $errors[] = "Password is required and must be at least 6 characters";
    }
    
    if ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match";
    }
    
    if (empty($expertise)) {
        $errors[] = "Please select your field expertise";
    }
    
    // If no errors, proceed with registration
    if (empty($errors)) {
        // Load existing users
        $users = loadJsonData(USERS_FILE);
        error_log("Loaded users: " . count($users));
        
        // Check if username or email already exists
        foreach ($users as $user) {
            if ($user['username'] === $username) {
                $errors[] = "Username already exists";
                break;
            }
            if ($user['email'] === $email) {
                $errors[] = "Email already exists";
                break;
            }
        }
        
        // If username and email are unique, create new user
        if (empty($errors)) {
            // Create new user array
            $newUserId = generateUniqueId();
            $newUser = [
                'id' => $newUserId,
                'username' => $username,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'email' => $email,
                'firstname' => $firstname,
                'lastname' => $lastname,
                'expertise' => $expertise,
                'is_admin' => false, // Default to regular user
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            error_log("New user created: " . $newUserId);
            
            // Add new user to users array
            $users[] = $newUser;
            
            // Save updated users array
            if (saveJsonData(USERS_FILE, $users)) {
                error_log("User saved successfully");
                // Create user profile file
                $userProfile = [
                    'id' => $newUserId,
                    'favorites' => [
                        'blogs' => [],
                        'movies' => []
                    ],
                    'game_progress' => [],
                    'ratings' => [],
                    'comments' => []
                ];
                
                $profileFile = USERS_DIR . "/{$newUserId}_profile.json";
                if (saveJsonData($profileFile, $userProfile)) {
                    error_log("User profile created: " . $profileFile);
                } else {
                    error_log("Failed to create user profile");
                }
                
                // Set success message
                setFlashMessage('success', 'Registration successful! You can now log in.');
                
                // Redirect to login page
                redirect('/MysteryHubProject/Pages/login.html');
                exit; // Ensure script stops here
            } else {
                $errors[] = "Failed to save user data. Please try again.";
                error_log("Failed to save user data");
            }
        }
    }
    
    // If there are errors, store them in session and redirect back to registration form
    if (!empty($errors)) {
        error_log("Registration errors: " . print_r($errors, true));
        foreach ($errors as $error) {
            setFlashMessage('error', $error);
        }
        
        // Store form data in session for form repopulation
        $_SESSION['form_data'] = [
            'firstname' => $firstname,
            'lastname' => $lastname,
            'email' => $email,
            'username' => $username,
            'expertise' => $expertise
        ];
        
        redirect('/MysteryHubProject/Pages/register.html');
        exit; // Ensure script stops here
    }
} else {
    // If not a POST request, redirect to registration form
    error_log("Not a POST request, redirecting to registration form");
    redirect('/MysteryHubProject/Pages/register.html');
    exit; // Ensure script stops here
}