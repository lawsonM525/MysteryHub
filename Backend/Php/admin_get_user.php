<?php
/**
 * Mystery Hub - Admin Get Single User
 * Fetches a single user by ID for the admin panel
 */

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include configuration
require_once 'config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access. You must be an admin to view this page.'
    ]);
    exit;
}

// Check if user ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'User ID is required.'
    ]);
    exit;
}

$userId = $_GET['id'];

// Function to get a single user by ID
function getUserById($userId) {
    // Read users.json file
    $usersFile = USERS_FILE;
    if (!file_exists($usersFile)) {
        return null;
    }
    
    $usersData = json_decode(file_get_contents($usersFile), true);
    if (!$usersData || !isset($usersData['users'])) {
        return null;
    }
    
    // Find user by ID
    $user = null;
    foreach ($usersData['users'] as $userData) {
        if ($userData['id'] === $userId) {
            $user = $userData;
            break;
        }
    }
    
    if (!$user) {
        return null;
    }
    
    // Get profile data if available
    $profileFile = USERS_DIR . "/{$userId}_profile.json";
    $profileData = [];
    
    if (file_exists($profileFile)) {
        $profileData = json_decode(file_get_contents($profileFile), true);
    }
    
    // Remove password from the response
    unset($user['password']);
    
    // Add profile data if available
    if (!empty($profileData)) {
        if (isset($profileData['profile'])) {
            $user = array_merge($user, $profileData['profile']);
        }
        
        if (isset($profileData['stats'])) {
            $user['stats'] = $profileData['stats'];
        }
    }
    
    return $user;
}

// Get user by ID
$user = getUserById($userId);

if (!$user) {
    echo json_encode([
        'success' => false,
        'message' => 'User not found.'
    ]);
    exit;
}

// Return as JSON
echo json_encode([
    'success' => true,
    'user' => $user
]);
