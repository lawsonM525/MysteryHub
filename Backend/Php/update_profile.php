<?php
/**
 * Mystery Hub Update Profile API
 * Handles updating user profile information
 */

// Start session
session_start();

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'User not logged in',
        'redirect' => '/MysteryHubProject/Pages/login.html'
    ]);
    exit;
}

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}

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

function saveJsonData($file, $data) {
    $jsonData = json_encode($data, JSON_PRETTY_PRINT);
    return file_put_contents($file, $jsonData) !== false;
}

function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Get user ID from session
$userId = $_SESSION['user_id'];

// Get and sanitize form data
$firstname = sanitizeInput($_POST['firstname'] ?? '');
$lastname = sanitizeInput($_POST['lastname'] ?? '');
$email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
$bio = sanitizeInput($_POST['bio'] ?? '');
$currentPassword = $_POST['current_password'] ?? '';
$newPassword = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

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

// Load users data
$usersFile = USERS_DIR . '/users.json';
$users = loadJsonData($usersFile);

// Find current user data
$userData = null;
foreach ($users as $index => $user) {
    if ($user['id'] === $userId) {
        $userData = $user;
        $userIndex = $index;
        break;
    }
}

if (!$userData) {
    echo json_encode([
        'success' => false,
        'message' => 'User data not found'
    ]);
    exit;
}

// Check if email already exists (excluding current user)
foreach ($users as $user) {
    if ($user['email'] === $email && $user['id'] !== $userId) {
        $errors[] = "Email already in use by another agent";
        break;
    }
}

// Password change (optional)
$passwordChanged = false;
if (!empty($newPassword) || !empty($confirmPassword)) {
    // Current password required for changes
    if (empty($currentPassword) || !password_verify($currentPassword, $userData['password'])) {
        $errors[] = "Current security key (password) is incorrect";
    }
    
    if (strlen($newPassword) < 6) {
        $errors[] = "New security key must be at least 6 characters";
    }
    
    if ($newPassword !== $confirmPassword) {
        $errors[] = "New security key and confirmation do not match";
    }
    
    $passwordChanged = true;
}

// If no errors, update profile
if (empty($errors)) {
    // Update user data
    $users[$userIndex]['firstname'] = $firstname;
    $users[$userIndex]['lastname'] = $lastname;
    $users[$userIndex]['email'] = $email;
    $users[$userIndex]['bio'] = $bio;
    $users[$userIndex]['updated_at'] = date('Y-m-d H:i:s');
    
    // Update password if changed
    if ($passwordChanged && !empty($newPassword)) {
        $users[$userIndex]['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
    }
    
    // Save updated user data
    if (saveJsonData($usersFile, $users)) {
        // Update session data
        $_SESSION['firstname'] = $firstname;
        $_SESSION['lastname'] = $lastname;
        
        echo json_encode([
            'success' => true,
            'message' => 'Profile updated successfully!',
            'user' => [
                'firstname' => $firstname,
                'lastname' => $lastname,
                'email' => $email,
                'bio' => $bio
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to save profile data. Please try again.'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Validation errors',
        'errors' => $errors
    ]);
}
?>
