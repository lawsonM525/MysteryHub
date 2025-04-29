<?php
/**
 * Upload Blog Post - Handles the upload of blog posts
*/

// Start session
session_start();
require_once __DIR__ . '/config.php';

// Define directories
define('WEB_UPLOADS_PATH', '../../Assets/uploads/');

$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'anonymous';
$username = 'Anonymous';

// Get the username from users.json
$usersFile = DATA_DIR . '/users/users.json';
if (file_exists($usersFile)) {
    $users = json_decode(file_get_contents($usersFile), true);
    foreach ($users as $user) {
        if ($user['id'] === $userId) {
            $username = $user['username'];
            break;
        }
    }
}

// Create directories if they don't exist
if (!file_exists(DATA_DIR)) {
    mkdir(DATA_DIR, 0755, true);
}

if (!file_exists(UPLOADS_DIR)) {
    mkdir(UPLOADS_DIR, 0755, true);
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $title = $_POST['post-title'];
    $category = $_POST['post-category'];
    $readTime = $_POST['read-time'];
    $excerpt = $_POST['post-excerpt'];
    $content = $_POST['post-content'];
    $tags = $_POST['post-tags'];
    $sources = isset($_POST['post-sources']) ? $_POST['post-sources'] : '';
    $terms = isset($_POST['terms-agreement']) ? true : false;
    $fileUpload = $_FILES['featured-image'];

    // Validate required fields
    if (empty($title) || empty($category) || empty($readTime) || empty($excerpt) || empty($content)) {
        echo "Please fill in all required fields";
        exit;
    } 

    // Handle featured image
    $featuredImagePath = null;
    if ($fileUpload['error'] === UPLOAD_ERR_OK) {
        $file = $fileUpload;
        $file_name = $file['name'];
        $file_tmp = $file['tmp_name'];
        $file_size = $file['size'];
        $file_error = $file['error'];
        
        // Get file extension
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // Allowed file types
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        
        // Check if file type is allowed
        if (in_array($file_ext, $allowed)) {
            // Check for errors
            if ($file_error === 0) {
                // Check file size (max 5MB)
                if ($file_size < 5000000) {
                    // Create unique file name
                    $file_name_new = uniqid('story_', true) . '.' . $file_ext;
                    
                    // Set upload directory
                    $upload_dir = dirname(__DIR__) . '/Assets/uploads/';
                    
                    // Create directory if it doesn't exist
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    // Set file destination
                    $file_destination = $upload_dir . $file_name_new;
                    
                    // Move uploaded file
                    if (move_uploaded_file($file_tmp, $file_destination)) {
                        // Store relative path for web access
                        $featuredImagePath = $file_name_new;
                    } else {
                        echo "Failed to upload featured image. Please try again.";
                        exit;
                    }
                } else {
                    echo "File size too large. Maximum size is 5MB";
                    exit;
                }
            } else {
                echo "Error uploading file";
                exit;
            }
        } else {
            echo "File type not allowed. Allowed types: jpg, jpeg, png, gif";
            exit;
        }
    } else {
        echo "Error with featured image: " . $fileUpload['error'];
        exit;
    }

    // Save the blog post to the data file
    $blogPost = [
        'id' => uniqid(),
        'title' => $title,
        'category' => $category,
        'readTime' => $readTime,
        'excerpt' => $excerpt,
        'content' => $content,
        'tags' => $tags,
        'sources' => $sources,
        'featuredImage' => $featuredImagePath,
        'createdAt' => date('Y-m-d H:i:s'),
        'updatedAt' => date('Y-m-d H:i:s'),
        'userId' => $userId,
        'status' => 'published',
        'comments' => [],
        'likes' => 0,
        'views' => 0,
        'author' => $username,
    ];

    // Get the user avatar
    $authorAvatar = '../../assets/images/default-avatar.jpg';
    foreach ($users as $user) {
        if ($user['id'] === $blogPost['userId']) {
            // Check if the user has a profile picture
            if (isset($user['profile_picture']) && !empty($user['profile_picture'])) {
                $authorAvatar = '../../profile_pics/' . basename($user['profile_picture']);
            }
            break;
        }
    }
    $blogPost['authorAvatar'] = $authorAvatar;


    // Load existing blogs
    $existingData = [];
    if (file_exists(BLOGS_FILE)) {
        $existingData = json_decode(file_get_contents(BLOGS_FILE), true) ?: [];
    }

    // Add the new blog post to the existing data
    $existingData[] = $blogPost;

    // Save the updated data back to the file
    if (file_put_contents(BLOGS_FILE, json_encode($existingData, JSON_PRETTY_PRINT))) {
        // Redirect to the story list page
        header('Location: ../../Pages/blog/blog.html');
        exit;
    } else {
        echo "Error saving blog post";
        exit;
    }
} else {
    // Redirect to the upload page
    echo "Invalid request method";
    header('Location: ../../Pages/blog/upload.html');
    exit;
}
