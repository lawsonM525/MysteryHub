<?php

session_start();
require_once __DIR__ . '/config.php';

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$postId = $data['postId'];
$content = $data['content'];
$userId = $_SESSION['user_id'] ?? 'anonymous';

// get users and comment author info
$usersFile = DATA_DIR . '/users/users.json';
$users = file_exists($usersFile) ? json_decode(file_get_contents($usersFile), true) : [];
$username = 'Anonymous';
$userAvatar = '../../assets/images/default-avatar.jpg';

// find the matching user
foreach ($users as $user) {
    if ($user['id'] === $userId) {
        $username = $user['username'];
        $userAvatar = '../../profile_pics/' . basename($user['profile_picture']);
        break;
    }
}


// read the blog post
$blogsFile = DATA_DIR . '/blogs.json';
$blogs = file_exists($blogsFile) ? json_decode(file_get_contents($blogsFile), true) : [];

foreach ($blogs as &$blog) {
    if ($blog['id'] === $postId) {
        error_log("postId: $postId");
        $newComment = [
            'id' => uniqid('comment_'),
            'userId' => $userId,
            'username' => $username,
            'userAvatar' => $userAvatar,
            'content' => $content,
            'createdAt' => date('Y-m-d H:i:s')
        ];
        $blog['comments'][] = $newComment;
        break;
    }
}

// save the updated blog post
file_put_contents($blogsFile, json_encode($blogs, JSON_PRETTY_PRINT));
echo json_encode(['success' => true]);
