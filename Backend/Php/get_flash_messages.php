<?php
/**
 * Mystery Hub Flash Messages API
 * Returns flash messages and form data stored in session
 */

// Start session
session_start();

// Set content type to JSON
header('Content-Type: application/json');

// Initialize response
$response = [
    'messages' => [],
    'form_data' => null
];

// Get flash messages if they exist
if (isset($_SESSION['flash_messages'])) {
    $response['messages'] = $_SESSION['flash_messages'];
    
    // Clear flash messages after sending
    unset($_SESSION['flash_messages']);
}

// Get form data if it exists (for repopulating form fields after validation error)
if (isset($_SESSION['form_data'])) {
    $response['form_data'] = $_SESSION['form_data'];
    
    // Clear form data after sending
    unset($_SESSION['form_data']);
}

// Send response
echo json_encode($response);
exit;
?>
