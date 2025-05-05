<?php
/**
 * Mystery Hub Profile Data API
 * Provides user profile data to the frontend
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
        'logged_in' => false
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

// Get user ID from session
$userId = $_SESSION['user_id'];

// Load user data
$usersFile = USERS_DIR . '/users.json';
$users = loadJsonData($usersFile);

// Find user data
$userData = null;
foreach ($users as $user) {
    if ($user['id'] === $userId) {
        $userData = $user;
        unset($userData['password']);
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

// Load user profile data
$profileFile = USERS_DIR . "/{$userId}_profile.json";
$profile = loadJsonData($profileFile);

// Calculate user stats
$favoriteBlogs = count($profile['favorites']['blogs'] ?? []);
$favoriteMovies = count($profile['favorites']['movies'] ?? []);
$completedGames = 0;

// Count completed games
if (!empty($profile['game_progress'])) {
    foreach ($profile['game_progress'] as $game) {
        if (isset($game['completed']) && $game['completed'] === true) {
            $completedGames++;
        }
    }
}

// Format date for display
$joinDate = isset($userData['created_at']) ? date('F j, Y', strtotime($userData['created_at'])) : 'Unknown';
$lastActive = isset($userData['last_login']) ? date('F j, Y', strtotime($userData['last_login'])) : 'Unknown';

// Get user's full name
$fullName = $userData['firstname'] . ' ' . $userData['lastname'];
$firstInitial = substr($userData['firstname'], 0, 1);
$lastInitial = substr($userData['lastname'], 0, 1);
$displayName = "Agent " . $firstInitial . ". " . $lastInitial . ".";

// Create response data
$response = [
    'success' => true,
    'user' => $userData,
    'profile' => $profile,
    'stats' => [
        'favoriteBlogs' => $favoriteBlogs,
        'favoriteMovies' => $favoriteMovies,
        'completedGames' => $completedGames
    ],
    'display' => [
        'fullName' => $fullName,
        'displayName' => $displayName,
        'joinDate' => $joinDate,
        'lastActive' => $lastActive
    ],
    'session' => [
        'isLoggedIn' => true,
        'username' => $_SESSION['username'],
        'userId' => $_SESSION['user_id']
    ],
    'flash_messages' => $_SESSION['flash_messages'] ?? []
];

// Clear flash messages after sending
if (isset($_SESSION['flash_messages'])) {
    unset($_SESSION['flash_messages']);
}

// Send response
echo json_encode($response);
exit;
?>
