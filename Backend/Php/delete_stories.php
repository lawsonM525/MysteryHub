<?php
session_start();
require_once __DIR__ . '/config.php';

$filePath = dirname(__DIR__) . '/Data/blogs.json';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'No ID specified']);
    exit;
}

$postId = $_GET['id'];
$userId = $_SESSION['user_id'];

$stories = file_exists($filePath) ? json_decode(file_get_contents($filePath), true) : [];

$found = false;

// Filter out the story
$newStories = array_filter($stories, function($story) use ($postId, $userId, &$found) {
    if ($story['id'] === $postId && $story['userId'] === $userId) {
        $found = true;
        return false; // Remove this story
    }
    return true;
});

if ($found) {
    file_put_contents($filePath, json_encode(array_values($newStories), JSON_PRETTY_PRINT));
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Story not found or not owned by you']);
}
?>
