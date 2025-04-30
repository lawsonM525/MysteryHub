<?php
/**
 * Admin Blogs Management
 * Handles fetching and deleting blog posts for admin panel
 */

header('Content-Type: application/json');
session_start();

// Define file paths
define('DATA_DIR', __DIR__ . '/../Data');
define('BLOGS_FILE', DATA_DIR . '/blogs.json');

// Action parameter determines what to do
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Handle different actions
switch ($action) {
    case 'get_all':
        getAllBlogs();
        break;
    case 'delete':
        deleteBlog();
        break;
    default:
        sendResponse(false, 'Invalid action');
        break;
}

/**
 * Get all blogs for admin panel
 */
function getAllBlogs() {
    if (!file_exists(BLOGS_FILE)) {
        sendResponse(false, 'Blogs file not found');
        return;
    }
    
    $blogs = json_decode(file_get_contents(BLOGS_FILE), true);
    
    if ($blogs === null) {
        sendResponse(false, 'Error reading blogs data');
        return;
    }
    
    // Sort blogs by created date (newest first)
    usort($blogs, function($a, $b) {
        return strtotime($b['createdAt']) - strtotime($a['createdAt']);
    });
    
    sendResponse(true, 'Blogs retrieved successfully', ['blogs' => $blogs]);
}

/**
 * Delete a blog post
 */
function deleteBlog() {
    if (!isset($_POST['id']) || empty($_POST['id'])) {
        sendResponse(false, 'Blog ID is required');
        return;
    }
    
    $blogId = $_POST['id'];
    
    if (!file_exists(BLOGS_FILE)) {
        sendResponse(false, 'Blogs file not found');
        return;
    }
    
    $blogs = json_decode(file_get_contents(BLOGS_FILE), true);
    
    if ($blogs === null) {
        sendResponse(false, 'Error reading blogs data');
        return;
    }
    
    // Find blog index
    $blogIndex = -1;
    foreach ($blogs as $index => $blog) {
        if ($blog['id'] === $blogId) {
            $blogIndex = $index;
            break;
        }
    }
    
    if ($blogIndex === -1) {
        sendResponse(false, 'Blog not found');
        return;
    }
    
    // Get featured image path to delete
    $featuredImage = $blogs[$blogIndex]['featuredImage'];
    
    // Remove blog from array
    array_splice($blogs, $blogIndex, 1);
    
    // Save updated blogs array
    if (file_put_contents(BLOGS_FILE, json_encode($blogs, JSON_PRETTY_PRINT))) {
        // Try to delete the featured image if it's not an external URL
        if (strpos($featuredImage, 'http') !== 0 && file_exists('../../' . $featuredImage)) {
            @unlink('../../' . $featuredImage);
        }
        
        sendResponse(true, 'Blog deleted successfully');
    } else {
        sendResponse(false, 'Error saving blogs data');
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
