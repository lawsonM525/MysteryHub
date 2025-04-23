<?php
/**
 * Mystery Hub Update Profile API
 * Handles updating user profile information
 */

// Include database connection


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
define('PROFILE_PICS_DIR', dirname(dirname(__DIR__)) . '/profile_pics');
 

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
$username = sanitizeInput($_POST['username'] ?? '');
$expertise = sanitizeInput($_POST['expertise'] ?? '');
$bio = sanitizeInput($_POST['bio'] ?? '');

// Handle profile picture upload if provided
$profilePicturePath = null;
if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['profile_picture'];
    $file_name = $file['name'];
    $file_tmp = $file['tmp_name'];
    $file_size = $file['size'];
    $file_error = $file['error'];
    
    // Get file extension
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    
    // Allowed file types
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    
    // Check if file type is allowed
    if (in_array($file_ext, $allowed)) {
        // Check for errors
        if ($file_error === 0) {
            // Check file size (max 5MB)
            if ($file_size < 5000000) {
                // Create unique file name
                $file_name_new = uniqid('profile_', true) . '.' . $file_ext;
                
                // Set upload directory
                $upload_dir = PROFILE_PICS_DIR . '/';
                
                // Create directory if it doesn't exist
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                // Set file destination
                $file_destination = $upload_dir . $file_name_new;
                
                // Move uploaded file
                if (move_uploaded_file($file_tmp, $file_destination)) {
                    $profilePicturePath = 'profile_pics/' . $file_name_new;
                } else {
                    $errors[] = "Failed to upload profile picture. Please try again.";
                }
            } else {
                $errors[] = "File size too large. Maximum size is 5MB";
            }
        } else {
            $errors[] = "Error uploading file";
        }
    } else {
        $errors[] = "File type not allowed. Allowed types: jpg, jpeg, png, gif";
    }
}

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

if (empty($username)) {
    $errors[] = "Username is required";
}

if (empty($expertise)) {
    $errors[] = "Field expertise is required";
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

// Check if username already exists (excluding current user)
foreach ($users as $user) {
    if ($user['username'] === $username && $user['id'] !== $userId) {
        $errors[] = "Username already in use by another agent";
        break;
    }
}

// If no errors, update profile
if (empty($errors)) {
    // Update user data
    $users[$userIndex]['firstname'] = $firstname;
    $users[$userIndex]['lastname'] = $lastname;
    $users[$userIndex]['email'] = $email;
    $users[$userIndex]['username'] = $username;
    $users[$userIndex]['expertise'] = $expertise;
    $users[$userIndex]['bio'] = $bio;
    $users[$userIndex]['updated_at'] = date('Y-m-d H:i:s');
    
    // Update profile picture if a new one was uploaded
    if ($profilePicturePath) {
        $users[$userIndex]['profile_picture'] = $profilePicturePath;
    }
    
    // Save updated user data
    if (saveJsonData($usersFile, $users)) {
        // Update session data
        $_SESSION['firstname'] = $firstname;
        $_SESSION['lastname'] = $lastname;
        $_SESSION['username'] = $username;
        
        echo json_encode([
            'success' => true,
            'message' => 'Profile updated successfully!',
            'user' => [
                'firstname' => $firstname,
                'lastname' => $lastname,
                'email' => $email,
                'username' => $username,
                'expertise' => $expertise,
                'bio' => $bio,
                'profile_picture' => $users[$userIndex]['profile_picture']
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
