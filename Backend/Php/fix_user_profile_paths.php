<?php
// Path to your users.json file
$usersFile = __DIR__ . '/../Data/users/users.json';

// Load the users data
if (file_exists($usersFile)) {
    $users = json_decode(file_get_contents($usersFile), true);

    foreach ($users as &$user) {
        if (isset($user['profile_picture']) && !empty($user['profile_picture'])) {
            $profilePic = $user['profile_picture'];

            // If it's an absolute path, strip it down to just the filename
            $filename = basename($profilePic);

            // Now always format it as: profile_pics/filename
            $user['profile_picture'] = 'profile_pics/' . $filename;
        }
    }

    // Save the cleaned-up users.json
    if (file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT))) {
        echo "User profile pictures updated successfully!\n";
    } else {
        echo "Error writing to users.json.\n";
    }
} else {
    echo "users.json file not found.\n";
}
?>
