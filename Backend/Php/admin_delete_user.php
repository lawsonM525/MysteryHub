<?php
/**
 * Mystery Hub - Admin Delete User
 * Deletes a user from the admin panel
 */

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include configuration
require_once 'config.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access. You must be an admin to perform this action.'
    ]);
    exit;
}

// Get JSON data from request
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Validate input
if (!isset($data['user_id']) || empty($data['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'User ID is required.'
    ]);
    exit;
}

$userId = $data['user_id'];

// Check if user is trying to delete themselves
if ($userId === $_SESSION['user_id']) {
    echo json_encode([
        'success' => false,
        'message' => 'You cannot delete your own account while logged in.'
    ]);
    exit;
}

// Load existing users
$usersFile = USERS_FILE;

if (!file_exists($usersFile)) {
    echo json_encode([
        'success' => false,
        'message' => 'Users file not found.'
    ]);
    exit;
}

$usersData = json_decode(file_get_contents($usersFile), true);

if (!$usersData || !isset($usersData['users'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid users data.'
    ]);
    exit;
}

// Find user by ID
$userExists = false;
$userIndex = -1;

foreach ($usersData['users'] as $index => $user) {
    if ($user['id'] === $userId) {
        $userExists = true;
        $userIndex = $index;
        break;
    }
}

if (!$userExists) {
    echo json_encode([
        'success' => false,
        'message' => 'Agent not found.'
    ]);
    exit;
}

// Remove user from array
array_splice($usersData['users'], $userIndex, 1);

// Save updated users data
if (file_put_contents($usersFile, json_encode($usersData, JSON_PRETTY_PRINT))) {
    // Delete user profile file
    $profileFile = USERS_DIR . "/{$userId}_profile.json";
    
    if (file_exists($profileFile)) {
        unlink($profileFile);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Agent successfully removed from the system.'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to delete agent. Please try again.'
    ]);
}
