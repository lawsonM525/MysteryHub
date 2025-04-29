<?php
session_start();

header('Content-Type: application/json');

$blogsFile = dirname(__DIR__) . '/Data/blogs.json';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $postId = $input['postId'];

    if (file_exists($blogsFile)) {
        $blogs = json_decode(file_get_contents($blogsFile), true);

        foreach ($blogs as &$blog) {
            if ($blog['id'] === $postId) {
                $blog['likes'] = isset($blog['likes']) ? $blog['likes'] + 1 : 1;
                file_put_contents($blogsFile, json_encode($blogs, JSON_PRETTY_PRINT));
                echo json_encode(['success' => true]);
                exit;
            }
        }
    }
}

echo json_encode(['success' => false]);
?>
