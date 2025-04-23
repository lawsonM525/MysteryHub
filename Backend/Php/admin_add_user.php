<?php
/**
 * Mystery Hub - Admin Add User
 * Adds a new user from the admin panel
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
if (!isset($_POST['firstname']) || empty($_POST['firstname']) ||
    !isset($_POST['lastname']) || empty($_POST['lastname']) ||
    !isset($_POST['username']) || empty($_POST['username']) ||
    !isset($_POST['email']) || empty($_POST['email']) ||
    !isset($_POST['password']) || empty($_POST['password']) ||
    !isset($_POST['confirm_password']) || empty($_POST['confirm_password'])) {
    
    echo json_encode([
        'success' => false,
        'message' => 'All fields are required.'
    ]);
    exit;
}

// Check if passwords match
if ($_POST['password'] !== $_POST['confirm_password']) {
    echo json_encode([
        'success' => false,
        'message' => 'Passwords do not match.'
    ]);
    exit;
}

// Sanitize inputs
$firstname = filter_var($_POST['firstname'], FILTER_SANITIZE_STRING);
$lastname = filter_var($_POST['lastname'], FILTER_SANITIZE_STRING);
$username = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
$expertise = isset($_POST['expertise']) ? filter_var($_POST['expertise'], FILTER_SANITIZE_STRING) : '';
$is_admin = isset($_POST['is_admin']) ? (int)$_POST['is_admin'] : 0;

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
    // Create users directory if it doesn't exist
    if (!is_dir(USERS_DIR)) {
        mkdir(USERS_DIR, 0755, true);
    }
    
    // Create empty users file
    file_put_contents($usersFile, json_encode(['users' => []]));
}

$usersData = json_decode(file_get_contents($usersFile), true);

if (!$usersData) {
    $usersData = ['users' => []];
}

// Check if username or email already exists
foreach ($usersData['users'] as $user) {
    if ($user['username'] === $username) {
        echo json_encode([
            'success' => false,
            'message' => 'Username already exists.'
        ]);
        exit;
    }
    
    if ($user['email'] === $email) {
        echo json_encode([
            'success' => false,
            'message' => 'Email already exists.'
        ]);
        exit;
    }
}

// Generate a unique user ID
$userId = 'usr_' . uniqid();

// Hash password
$hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);

// Create new user
$newUser = [
    'id' => $userId,
    'firstname' => $firstname,
    'lastname' => $lastname,
    'username' => $username,
    'email' => $email,
    'password' => $hashedPassword,
    'is_admin' => $is_admin,
    'created_at' => date('Y-m-d H:i:s')
];

// Add to users array
$usersData['users'][] = $newUser;

// Save users data
if (file_put_contents($usersFile, json_encode($usersData, JSON_PRETTY_PRINT))) {
    // Create user profile file
    $profileFile = USERS_DIR . "/{$userId}_profile.json";
    
    $profileData = [
        'profile' => [
            'bio' => '',
            'location' => '',
            'expertise' => $expertise,
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
    
    file_put_contents($profileFile, json_encode($profileData, JSON_PRETTY_PRINT));
    
    echo json_encode([
        'success' => true,
        'message' => 'Agent added successfully.',
        'user_id' => $userId
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to add agent. Please try again.'
    ]);
}
