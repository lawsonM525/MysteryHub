<?php
/**
 * Get Movies
 * Fetches movie data from JSON file
 */

header('Content-Type: application/json');

// Define file paths
define('DATA_DIR', __DIR__ . '/../Data');
define('MOVIES_FILE', DATA_DIR . '/movies.json');

// Check if the file exists
if (!file_exists(MOVIES_FILE)) {
    echo json_encode([
        'success' => false,
        'message' => 'Movies data file not found',
        'data' => []
    ]);
    exit;
}

// Read and parse the JSON file
$movies = json_decode(file_get_contents(MOVIES_FILE), true);

if ($movies === null) {
    echo json_encode([
        'success' => false,
        'message' => 'Error parsing movies data',
        'data' => []
    ]);
    exit;
}

// Return the movies data
echo json_encode([
    'success' => true,
    'message' => 'Movies retrieved successfully',
    'data' => $movies
]);
