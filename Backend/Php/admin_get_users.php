<?php
/**
 * Mystery Hub - Admin Get Users
 * Fetches all users for the admin panel
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

// Function to get all users
function getAllUsers() {
    $users = [];
    $usersDir = USERS_DIR;
    
    if (!is_dir($usersDir)) {
        return $users;
    }
    
    // Read users.json file
    $usersFile = USERS_FILE;
    if (!file_exists($usersFile)) {
        return $users;
    }
    
    $usersData = json_decode(file_get_contents($usersFile), true);
    if (!$usersData || !isset($usersData['users'])) {
        return $users;
    }
    
    foreach ($usersData['users'] as $user) {
        // Get profile data if available
        $profileFile = USERS_DIR . "/{$user['id']}_profile.json";
        $profileData = [];
        
        if (file_exists($profileFile)) {
            $profileData = json_decode(file_get_contents($profileFile), true);
        }
        
        // Merge user data with profile data
        $userData = $user;
        
        // Remove password from the response
        unset($userData['password']);
        
        // Add profile data if available
        if (!empty($profileData)) {
            if (isset($profileData['profile'])) {
                $userData = array_merge($userData, $profileData['profile']);
            }
            
            if (isset($profileData['stats'])) {
                $userData['stats'] = $profileData['stats'];
            }
        }
        
        $users[] = $userData;
    }
    
    return $users;
}

// Get all users
$users = getAllUsers();

// Return as JSON
echo json_encode([
    'success' => true,
    'users' => $users
]);
