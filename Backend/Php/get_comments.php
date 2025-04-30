<?php

// get_comments.php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json');
$postId = $_GET['postId'] ?? '';

$blogsFile = DATA_DIR . '/blogs.json';
$blogs = file_exists($blogsFile) ? json_decode(file_get_contents($blogsFile), true) : [];

foreach ($blogs as $blog) {
    if ($blog['id'] === $postId) {
        echo json_encode($blog['comments'] ?? []);
        exit;
    }
}
echo json_encode([]);

