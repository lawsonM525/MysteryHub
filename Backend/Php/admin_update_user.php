<?php
/**
 * Mystery Hub - Admin Update User
 * Updates an existing user from the admin panel
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

// Validate form input
if (!isset($_POST['user_id']) || empty($_POST['user_id']) ||
    !isset($_POST['firstname']) || empty($_POST['firstname']) ||
    !isset($_POST['lastname']) || empty($_POST['lastname']) ||
    !isset($_POST['username']) || empty($_POST['username']) ||
    !isset($_POST['email']) || empty($_POST['email'])) {
    
    echo json_encode([
        'success' => false,
        'message' => 'Required fields are missing.'
    ]);
    exit;
}

// Get user ID
$userId = $_POST['user_id'];

// Sanitize inputs
$firstname = filter_var($_POST['firstname'], FILTER_SANITIZE_STRING);
$lastname = filter_var($_POST['lastname'], FILTER_SANITIZE_STRING);
$username = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
$expertise = isset($_POST['expertise']) ? filter_var($_POST['expertise'], FILTER_SANITIZE_STRING) : '';
$is_admin = isset($_POST['is_admin']) ? (int)$_POST['is_admin'] : 0;
$password = isset($_POST['password']) && !empty($_POST['password']) ? $_POST['password'] : null;

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid email format.'
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

// Find user by ID and check for duplicate username/email
$userExists = false;
$userIndex = -1;

foreach ($usersData['users'] as $index => $user) {
    if ($user['id'] === $userId) {
        $userExists = true;
        $userIndex = $index;
    } elseif ($user['username'] === $username) {
        echo json_encode([
            'success' => false,
            'message' => 'Username already exists for another agent.'
        ]);
        exit;
    } elseif ($user['email'] === $email) {
        echo json_encode([
            'success' => false,
            'message' => 'Email already exists for another agent.'
        ]);
        exit;
    }
}

if (!$userExists) {
    echo json_encode([
        'success' => false,
        'message' => 'Agent not found.'
    ]);
    exit;
}

// Update user data
$usersData['users'][$userIndex]['firstname'] = $firstname;
$usersData['users'][$userIndex]['lastname'] = $lastname;
$usersData['users'][$userIndex]['username'] = $username;
$usersData['users'][$userIndex]['email'] = $email;
$usersData['users'][$userIndex]['is_admin'] = $is_admin;

// Update password if provided
if ($password !== null) {
    $usersData['users'][$userIndex]['password'] = password_hash($password, PASSWORD_DEFAULT);
}

// Update user profile
$profileFile = USERS_DIR . "/{$userId}_profile.json";
$profileData = [];

if (file_exists($profileFile)) {
    $profileData = json_decode(file_get_contents($profileFile), true);
} else {
    // Create default profile structure if it doesn't exist
    $profileData = [
        'profile' => [
            'bio' => '',
            'location' => '',
            'expertise' => '',
            'profile_picture' => '',
            'twitter' => '',
            'facebook' => '',
            'instagram' => '',
            'linkedin' => ''
        ],
        'stats' => [
            'mysteries_solved' => 0,
            'cases_contributed' => 0,
            'blog_posts' => 0,
            'movie_recommendations' => 0,
            'rank' => 'Recruit'
        ],
        'preferences' => [
            'theme' => 'default',
            'notifications' => true,
            'privacy' => 'public'
        ]
    ];
}

// Update expertise
if (isset($profileData['profile'])) {
    $profileData['profile']['expertise'] = $expertise;
} else {
    $profileData['profile'] = ['expertise' => $expertise];
}

// Save updated users data
if (file_put_contents($usersFile, json_encode($usersData, JSON_PRETTY_PRINT)) &&
    file_put_contents($profileFile, json_encode($profileData, JSON_PRETTY_PRINT))) {
    
    echo json_encode([
        'success' => true,
        'message' => 'Agent updated successfully.'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update agent. Please try again.'
    ]);
}
