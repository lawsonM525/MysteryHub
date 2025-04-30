<?php
/**
 * Save Post to User Favorites
 * Adds a blog post to a user's bookmarks
 */

header('Content-Type: application/json');
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'User not logged in',
    ]);
    exit;
}

// Get request data
$jsonData = file_get_contents('php://input');
$data = json_decode($jsonData, true);

if (!isset($data['postId']) || empty($data['postId'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Post ID is required',
    ]);
    exit;
}

// Define paths
define('DATA_DIR', __DIR__ . '/../Data');
define('USERS_DIR', DATA_DIR . '/users');

$userId = $_SESSION['user_id'];
$postId = $data['postId'];
$userProfileFile = USERS_DIR . "/{$userId}_profile.json";

// Check if user profile exists
if (!file_exists($userProfileFile)) {
    // Create new profile if it doesn't exist
    $userProfile = [
        'id' => $userId,
        'favorites' => [
            'blogs' => [$postId],
            'movies' => []
        ],
        'game_progress' => [],
        'ratings' => [],
        'comments' => []
    ];
} else {
    // Load existing profile
    $userProfile = json_decode(file_get_contents($userProfileFile), true);
    
    // Initialize favorites array if it doesn't exist
    if (!isset($userProfile['favorites'])) {
        $userProfile['favorites'] = [
            'blogs' => [],
            'movies' => []
        ];
    }
    
    // Initialize blogs array if it doesn't exist
    if (!isset($userProfile['favorites']['blogs'])) {
        $userProfile['favorites']['blogs'] = [];
    }
    
    // Check if post is already in favorites
    if (in_array($postId, $userProfile['favorites']['blogs'])) {
        echo json_encode([
            'success' => true,
            'message' => 'Post already saved to favorites',
            'alreadySaved' => true
        ]);
        exit;
    }
    
    // Add post ID to favorites
    $userProfile['favorites']['blogs'][] = $postId;
}

// Save user profile
if (file_put_contents($userProfileFile, json_encode($userProfile, JSON_PRETTY_PRINT))) {
    echo json_encode([
        'success' => true,
        'message' => 'Post saved to favorites'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to save profile'
    ]);
}
