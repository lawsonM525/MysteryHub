<?php
/**
 * Mystery Hub User Registration
 * Handles new user registration
 */

// Include configuration
require_once '../config.php';

// Start session
startSession();

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
        $usersFile = USERS_DIR . '/users.json';
        $users = loadJsonData($usersFile);
        
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
            
            // Add new user to users array
            $users[] = $newUser;
            
            // Save updated users array
            if (saveJsonData($usersFile, $users)) {
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
                saveJsonData($profileFile, $userProfile);
                
                // Set success message
                setFlashMessage('success', 'Registration successful! You can now log in.');
                
                // Redirect to login page
                redirect('/MysteryHubProject/Pages/login.html');
            } else {
                $errors[] = "Failed to save user data. Please try again.";
            }
        }
    }
    
    // If there are errors, store them in session and redirect back to registration form
    if (!empty($errors)) {
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
    }
} else {
    // If not a POST request, redirect to registration form
    redirect('/MysteryHubProject/Pages/register.html');
}
