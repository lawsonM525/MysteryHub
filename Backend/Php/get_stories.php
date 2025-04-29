<?php

/**
 * This file is used to get all the stories from the data file
 */

// Start session
session_start();

require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

$filePath = dirname(__DIR__) . '/Data/blogs.json';
$usersFile = DATA_DIR . '/users/users.json';

$users = file_exists($usersFile) ? json_decode(file_get_contents($usersFile), true) : [];

if (file_exists($filePath)) {
    $stories = json_decode(file_get_contents($filePath), true);

    $filteredStories = [];

    foreach ($stories as $story) {
        $featuredImage = $story['featuredImage'];
        $authorAvatar = '../../assets/images/default-avatar.jpg';

        foreach ($users as $user) {
            if ($user['id'] === $story['userId']) {
                if (!empty($user['profile_picture'])) {
                    $authorAvatar = '../../profile_pics/' . basename($user['profile_picture']);
                }
                break;
            }
        }

        // Always use just the filename for featuredImage
        $featuredImage = basename($featuredImage);
        
        $filteredStories[] = [
            'id' => $story['id'],
            'title' => $story['title'],
            'category' => $story['category'],
            'readTime' => $story['readTime'],
            'excerpt' => $story['excerpt'],
            'featuredImage' => $featuredImage,
            'createdAt' => $story['createdAt'],
            'author' => $story['author'],
            'userId' => $story['userId'],
            'content' => $story['content'],
            'likes' => isset($story['likes']) ? $story['likes'] : 0,
            'tags' => $story['tags'] ?? [],
            'sources' => $story['sources'] ?? [],
            'commentCount' => isset($story['comments']) ? count($story['comments']) : 0,
            'authorAvatar' => $authorAvatar
        ];
    }
    if (isset($_GET['id'])) {
        $postId = $_GET['id'];
        $foundPost = null;

        foreach ($filteredStories as $story) {
            if ($story['id'] === $postId) {
                $foundPost = $story;
                break;
            }
        }
        if ($foundPost) {
            $filteredStories = [$foundPost]; // if post is found, return it as an array
        } else {
            echo json_encode(['error' => 'Post not found']);
            exit;
        }
    }
    echo json_encode($filteredStories); // if not return all stories
} else {
    echo json_encode(['error' => 'No stories found']);
}

?>
