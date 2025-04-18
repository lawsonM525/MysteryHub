<?php
/**
 * Mystery Hub Configuration
 * Contains global configuration settings and utility functions
 */

// Data directory paths
define('DATA_DIR', dirname(__DIR__) . '/data');
define('USERS_DIR', DATA_DIR . '/users');

// Ensure data directories exist
if (!file_exists(DATA_DIR)) {
    mkdir(DATA_DIR, 0755, true);
}
if (!file_exists(USERS_DIR)) {
    mkdir(USERS_DIR, 0755, true);
}

// Initialize users.json if it doesn't exist
$usersFile = USERS_DIR . '/users.json';
if (!file_exists($usersFile)) {
    file_put_contents($usersFile, json_encode([]));
}

/**
 * Load JSON data from a file
 *
 * @param string $file Path to the JSON file
 * @return array The decoded JSON data as an array
 */
function loadJsonData($file) {
    if (!file_exists($file)) {
        return [];
    }
    
    $jsonData = file_get_contents($file);
    return json_decode($jsonData, true) ?: [];
}

/**
 * Save data to a JSON file
 *
 * @param string $file Path to the JSON file
 * @param array $data The data to save
 * @return bool True if successful, false otherwise
 */
function saveJsonData($file, $data) {
    $jsonData = json_encode($data, JSON_PRETTY_PRINT);
    return file_put_contents($file, $jsonData) !== false;
}

/**
 * Generate a unique ID
 *
 * @return string A unique ID
 */
function generateUniqueId() {
    return uniqid('mh_', true);
}

/**
 * Sanitize user input
 *
 * @param string $input The input to sanitize
 * @return string The sanitized input
 */
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect to a URL
 *
 * @param string $url The URL to redirect to
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Start or resume a session
 */
function startSession() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Check if a user is logged in
 *
 * @return bool True if the user is logged in, false otherwise
 */
function isLoggedIn() {
    startSession();
    return isset($_SESSION['user_id']);
}

/**
 * Check if a user is an admin
 *
 * @return bool True if the user is an admin, false otherwise
 */
function isAdmin() {
    startSession();
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

/**
 * Set a flash message
 *
 * @param string $type The type of message (success, error, info, warning)
 * @param string $message The message
 */
function setFlashMessage($type, $message) {
    startSession();
    $_SESSION['flash_messages'][$type][] = $message;
}

/**
 * Get flash messages
 *
 * @param string $type The type of message to get (null for all)
 * @return array The flash messages
 */
function getFlashMessages($type = null) {
    startSession();
    
    if (!isset($_SESSION['flash_messages'])) {
        return [];
    }
    
    $messages = [];
    
    if ($type === null) {
        $messages = $_SESSION['flash_messages'];
        unset($_SESSION['flash_messages']);
    } elseif (isset($_SESSION['flash_messages'][$type])) {
        $messages = [$type => $_SESSION['flash_messages'][$type]];
        unset($_SESSION['flash_messages'][$type]);
    }
    
    return $messages;
}
