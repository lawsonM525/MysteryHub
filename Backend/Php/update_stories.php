<?php
session_start();
require_once __DIR__ . '/config.php';

define('WEB_UPLOADS_PATH', '../../../assets/uploads/'); 

header('Content-Type: application/json');

$blogsFile = dirname(__DIR__) . '/Data/blogs.json';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

// If editing, it will come from FormData
$id = $_POST['id'] ?? null;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'No blog ID provided']);
    exit;
}


if (!file_exists($blogsFile)) {
    echo json_encode(['success' => false, 'message' => 'Blogs file not found']);
    exit;
}

$blogs = json_decode(file_get_contents($blogsFile), true);

// Find the blog to update
$updated = false;
foreach ($blogs as &$blog) {
    if ($blog['id'] === $id) {
        // Update fields
        $blog['title'] = $_POST['title'];
        $blog['category'] = $_POST['category'];
        $blog['readTime'] = $_POST['readTime'];
        $blog['excerpt'] = $_POST['excerpt'];
        $blog['content'] = $_POST['content'];
        $blog['tags'] = $_POST['tags'];
        $blog['sources'] = $_POST['sources'];
        $blog['updatedAt'] = date('Y-m-d H:i:s');

        // Handle image if a new one uploaded
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadsDir = dirname(dirname(__DIR__)) . '/assets/uploads/';
            if (!file_exists($uploadsDir)) {
                mkdir($uploadsDir, 0755, true);
            }

            $filename = uniqid('story_', true) . '.' . pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $destination = $uploadsDir . $filename;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                // Update image path
                $blog['featuredImage'] = $filename;
            }
        }

        $updated = true;
        break;
    }
}


if ($updated) {
    file_put_contents($blogsFile, json_encode($blogs, JSON_PRETTY_PRINT));
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Blog not found']);
}
?>
