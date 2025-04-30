<?php
/**
 * Get Saved Blogs
 * Fetches all blog posts saved by the current user
 */

header('Content-Type: application/json');
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'User not logged in',
        'data' => []
    ]);
    exit;
}

// Define paths
define('DATA_DIR', __DIR__ . '/../Data');
define('USERS_DIR', DATA_DIR . '/users');
define('BLOGS_FILE', DATA_DIR . '/blogs.json');

$userId = $_SESSION['user_id'];
$userProfileFile = USERS_DIR . "/{$userId}_profile.json";

// Check if user profile exists
if (!file_exists($userProfileFile)) {
    echo json_encode([
        'success' => false,
        'message' => 'User profile not found',
        'data' => []
    ]);
    exit;
}

// Load user profile
$userProfile = json_decode(file_get_contents($userProfileFile), true);
if (!$userProfile) {
    echo json_encode([
        'success' => false,
        'message' => 'Error loading user profile',
        'data' => []
    ]);
    exit;
}

// Get saved blog IDs
$savedBlogIds = [];
if (isset($userProfile['favorites']) && 
    isset($userProfile['favorites']['blogs']) && 
    is_array($userProfile['favorites']['blogs'])) {
    $savedBlogIds = $userProfile['favorites']['blogs'];
}

// If no saved blogs, return empty array
if (empty($savedBlogIds)) {
    echo json_encode([
        'success' => true,
        'message' => 'No saved blogs found',
        'data' => []
    ]);
    exit;
}

// Load all blogs
if (!file_exists(BLOGS_FILE)) {
    echo json_encode([
        'success' => false,
        'message' => 'Blogs data file not found',
        'data' => []
    ]);
    exit;
}

$allBlogs = json_decode(file_get_contents(BLOGS_FILE), true);
if (!$allBlogs) {
    echo json_encode([
        'success' => false,
        'message' => 'Error loading blogs data',
        'data' => []
    ]);
    exit;
}

// Filter blogs by saved IDs
$savedBlogs = [];
foreach ($allBlogs as $blog) {
    if (in_array($blog['id'], $savedBlogIds)) {
        $savedBlogs[] = $blog;
    }
}

// Return saved blogs
echo json_encode([
    'success' => true,
    'message' => 'Saved blogs retrieved successfully',
    'data' => $savedBlogs
]);
