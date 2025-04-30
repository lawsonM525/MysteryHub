<?php
// Path to your blogs.json file
$filePath = __DIR__ . '/../Data/blogs.json';

// Check if file exists
if (!file_exists($filePath)) {
    die("blogs.json not found!");
}

$backupPath = __DIR__ . '/../Data/blogs_backup_' . date('Ymd_His') . '.json';
if (!copy($filePath, $backupPath)) {
    die("Failed to create backup!");
}
echo "Backup created: " . basename($backupPath) . "<br>";

// Load existing blogs
$blogs = json_decode(file_get_contents($filePath), true);

if ($blogs === null) {
    die("Error decoding blogs.json!");
}

// Loop through each blog and fix the featuredImage path
foreach ($blogs as &$blog) {
    if (isset($blog['featuredImage'])) {
        // Remove 'Assets/uploads/' if it exists
        $blog['featuredImage'] = basename($blog['featuredImage']);
    }
}

// Save back to blogs.json
file_put_contents($filePath, json_encode($blogs, JSON_PRETTY_PRINT));

echo "✅ All blog featuredImage paths have been fixed!";
?>
