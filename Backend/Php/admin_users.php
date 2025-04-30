<?php
/**
 * Admin Users Management
 * Handles fetching and deleting users for admin panel
 */

header('Content-Type: application/json');
session_start();

// Define file paths
define('DATA_DIR', __DIR__ . '/../Data');
define('USERS_DIR', DATA_DIR . '/users');
define('USERS_FILE', USERS_DIR . '/users.json');

// Action parameter determines what to do
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Handle different actions
switch ($action) {
    case 'get_all':
        getAllUsers();
        break;
    case 'delete':
        deleteUser();
        break;
    default:
        sendResponse(false, 'Invalid action');
        break;
}

/**
 * Get all users for admin panel
 */
function getAllUsers() {
    if (!file_exists(USERS_FILE)) {
        sendResponse(false, 'Users file not found');
        return;
    }
    
    $users = json_decode(file_get_contents(USERS_FILE), true);
    
    if ($users === null) {
        sendResponse(false, 'Error reading users data');
        return;
    }
    
    // Sort users by created date (newest first)
    usort($users, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
    
    // Remove sensitive information
    foreach ($users as &$user) {
        unset($user['password']);
    }
    
    sendResponse(true, 'Users retrieved successfully', ['users' => $users]);
}

/**
 * Delete a user
 */
function deleteUser() {
    if (!isset($_POST['id']) || empty($_POST['id'])) {
        sendResponse(false, 'User ID is required');
        return;
    }
    
    $userId = $_POST['id'];
    
    if (!file_exists(USERS_FILE)) {
        sendResponse(false, 'Users file not found');
        return;
    }
    
    $users = json_decode(file_get_contents(USERS_FILE), true);
    
    if ($users === null) {
        sendResponse(false, 'Error reading users data');
        return;
    }
    
    // Find user index
    $userIndex = -1;
    foreach ($users as $index => $user) {
        if ($user['id'] === $userId) {
            $userIndex = $index;
            break;
        }
    }
    
    if ($userIndex === -1) {
        sendResponse(false, 'User not found');
        return;
    }
    
    // Get profile picture path to delete
    $profilePicture = $users[$userIndex]['profile_picture'] ?? '';
    
    // Remove user from array
    array_splice($users, $userIndex, 1);
    
    // Save updated users array
    if (file_put_contents(USERS_FILE, json_encode($users, JSON_PRETTY_PRINT))) {
        // Try to delete the profile picture if it's not the default one
        if (!empty($profilePicture) && 
            strpos($profilePicture, 'default') === false && 
            file_exists('../../' . $profilePicture)) {
            @unlink('../../' . $profilePicture);
        }
        
        // Check for user profile file
        $profileFile = USERS_DIR . "/{$userId}_profile.json";
        if (file_exists($profileFile)) {
            @unlink($profileFile);
        }
        
        sendResponse(true, 'User deleted successfully');
    } else {
        sendResponse(false, 'Error saving users data');
    }
}

/**
 * Send JSON response
 */
function sendResponse($success, $message, $data = []) {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}
