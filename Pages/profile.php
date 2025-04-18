<?php
/**
 * Mystery Hub User Profile
 * Allows users to view and edit their profile information
 */

// Include configuration
require_once dirname(__DIR__) . '/php/config.php';

// Include authentication check - redirect if not logged in
require_once dirname(__DIR__) . '/php/auth/auth_check.php';

// Get user data
$userId = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Load user data
$usersFile = USERS_DIR . '/users.json';
$users = loadJsonData($usersFile);
$userData = null;

foreach ($users as $user) {
    if ($user['id'] === $userId) {
        $userData = $user;
        break;
    }
}

if (!$userData) {
    setFlashMessage('error', 'User data not found.');
    redirect('/MysteryHubProject/index.html');
    exit;
}

// Load user profile data
$profileFile = USERS_DIR . "/{$userId}_profile.json";
$profile = loadJsonData($profileFile);

// Calculate user stats
$favoriteBlogs = count($profile['favorites']['blogs'] ?? []);
$favoriteMovies = count($profile['favorites']['movies'] ?? []);
$completedGames = 0;

// Count completed games
if (!empty($profile['game_progress'])) {
    foreach ($profile['game_progress'] as $game) {
        if (isset($game['completed']) && $game['completed'] === true) {
            $completedGames++;
        }
    }
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    // Get and sanitize form data
    $firstname = sanitizeInput($_POST['firstname'] ?? '');
    $lastname = sanitizeInput($_POST['lastname'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $bio = sanitizeInput($_POST['bio'] ?? '');
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Form validation
    $errors = [];
    
    if (empty($firstname)) {
        $errors[] = "First name is required";
    }
    
    if (empty($lastname)) {
        $errors[] = "Last name is required";
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required";
    }
    
    // Check if email already exists (excluding current user)
    foreach ($users as $user) {
        if ($user['email'] === $email && $user['id'] !== $userId) {
            $errors[] = "Email already in use by another agent";
            break;
        }
    }
    
    // Password change (optional)
    $passwordChanged = false;
    if (!empty($newPassword) || !empty($confirmPassword)) {
        // Current password required for changes
        if (empty($currentPassword) || !password_verify($currentPassword, $userData['password'])) {
            $errors[] = "Current security key (password) is incorrect";
        }
        
        if (strlen($newPassword) < 6) {
            $errors[] = "New security key must be at least 6 characters";
        }
        
        if ($newPassword !== $confirmPassword) {
            $errors[] = "New security key and confirmation do not match";
        }
        
        $passwordChanged = true;
    }
    
    // If no errors, update profile
    if (empty($errors)) {
        // Update user data
        foreach ($users as &$user) {
            if ($user['id'] === $userId) {
                $user['firstname'] = $firstname;
                $user['lastname'] = $lastname;
                $user['email'] = $email;
                $user['bio'] = $bio;
                $user['updated_at'] = date('Y-m-d H:i:s');
                
                // Update password if changed
                if ($passwordChanged && !empty($newPassword)) {
                    $user['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
                }
                
                break;
            }
        }
        
        // Save updated user data
        if (saveJsonData($usersFile, $users)) {
            // Update session data
            $_SESSION['firstname'] = $firstname;
            $_SESSION['lastname'] = $lastname;
            
            setFlashMessage('success', 'Agent file updated successfully!');
            redirect('/MysteryHubProject/Pages/profile.php');
            exit;
        } else {
            setFlashMessage('error', 'Failed to save profile data. Please try again.');
        }
    } else {
        // Set error messages
        foreach ($errors as $error) {
            setFlashMessage('error', $error);
        }
    }
}

// Page title
$pageTitle = "Agent Dossier - Mystery Hub";

// Format date for display
$joinDate = isset($userData['created_at']) ? date('F j, Y', strtotime($userData['created_at'])) : 'Unknown';
$lastActive = isset($userData['updated_at']) ? date('F j, Y', strtotime($userData['updated_at'])) : 'Unknown';

// Get user's full name
$fullName = $userData['firstname'] . ' ' . $userData['lastname'];
$firstInitial = substr($userData['firstname'], 0, 1);
$lastInitial = substr($userData['lastname'], 0, 1);
$displayName = "Agent " . $firstInitial . ". " . $lastInitial . ".";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <script>
        window.MYSTERY_HUB_CONFIG = {
            navbarClock: true
        };
    </script>
    <script src="../clock/clock.js"></script>
    <style>
        body {
            background-image: url('https://www.transparenttextures.com/patterns/old-paper.png'), 
                              url('https://www.transparenttextures.com/patterns/cardboard.png');
            background-color: var(--aged-paper);
        }
        
        .profile-container {
            max-width: 1000px;
            margin: 2rem auto;
        }
        
        /* Agent Dossier Header */
        .agent-dossier {
            background-color: var(--manila-folder);
            border: 1px solid var(--faded-ink);
            padding: 2rem;
            position: relative;
            box-shadow: 3px 3px 8px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            display: flex;
            align-items: flex-start;
        }
        
        .agent-dossier::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('https://www.transparenttextures.com/patterns/cardboard.png');
            opacity: 0.4;
            pointer-events: none;
        }
        
        .agent-photo-container {
            position: relative;
            margin-right: 2rem;
            flex-shrink: 0;
        }
        
        .agent-photo {
            width: 150px;
            height: 180px;
            border: 1px solid var(--faded-ink);
            background-color: rgba(255, 255, 255, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        .agent-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .agent-photo::after {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('https://www.transparenttextures.com/patterns/film-grain.png');
            opacity: 0.2;
            pointer-events: none;
        }
        
        .photo-paperclip {
            position: absolute;
            top: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 30px;
            height: 50px;
            border: 3px solid #a8a8a8;
            border-radius: 15px 15px 0 0;
            border-bottom: none;
            z-index: 10;
        }
        
        .agent-info {
            flex: 1;
            position: relative;
        }
        
        .agent-code-name {
            font-family: var(--typewriter-font);
            font-size: 1.8rem;
            color: var(--folder-red);
            margin-bottom: 0.5rem;
            border-bottom: 2px solid var(--folder-red);
            padding-bottom: 0.5rem;
            display: inline-block;
        }
        
        .agent-codename {
            font-family: var(--handwritten-font);
            font-size: 1.2rem;
            color: var(--dark-ink);
            margin-bottom: 1.5rem;
            font-style: italic;
        }
        
        .agent-details {
            margin-bottom: 1.5rem;
        }
        
        .agent-detail {
            display: flex;
            margin-bottom: 0.5rem;
        }
        
        .detail-label {
            font-family: var(--typewriter-font);
            color: var(--dark-ink);
            width: 120px;
            font-size: 0.9rem;
        }
        
        .detail-value {
            font-family: var(--handwritten-font);
            color: var(--dark-ink);
            flex: 1;
        }
        
        .agent-stats {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }
        
        .agent-stat {
            padding: 0.5rem 1rem;
            background-color: rgba(160, 44, 44, 0.1);
            font-family: var(--typewriter-font);
            font-size: 0.8rem;
            color: var(--folder-red);
            display: flex;
            align-items: center;
        }
        
        .agent-stat i {
            margin-right: 0.5rem;
        }
        
        .edit-profile-btn {
            padding: 0.7rem 1.2rem;
            background-color: var(--folder-red);
            color: var(--aged-paper);
            border: none;
            font-family: var(--typewriter-font);
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            position: relative;
            overflow: hidden;
            border: 1px solid var(--folder-red);
            margin-top: 1rem;
            font-size: 0.9rem;
        }
        
        .edit-profile-btn:hover {
            background-color: transparent;
            color: var(--folder-red);
        }
        
        .clearance-stamp {
            position: absolute;
            top: 20px;
            right: 30px;
            transform: rotate(-15deg);
            color: var(--stamp-red);
            border: 2px solid var(--stamp-red);
            padding: 5px 12px;
            font-family: var(--typewriter-font);
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 1px;
        }
        
        .file-number {
            position: absolute;
            top: 10px;
            left: 20px;
            font-family: var(--typewriter-font);
            font-size: 0.8rem;
            color: var(--folder-red);
        }
        
        /* Tabs Styling */
        .dossier-tabs {
            display: flex;
            background-color: var(--manila-folder);
            position: relative;
            overflow: hidden;
        }
        
        .dossier-tabs::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('https://www.transparenttextures.com/patterns/cardboard.png');
            opacity: 0.4;
            pointer-events: none;
        }
        
        .dossier-tab {
            flex: 1;
            padding: 1rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            font-family: var(--typewriter-font);
            color: var(--dark-ink);
            border-bottom: 3px solid transparent;
            position: relative;
            z-index: 1;
        }
        
        .dossier-tab.active {
            background-color: rgba(160, 44, 44, 0.1);
            border-bottom: 3px solid var(--folder-red);
            color: var(--folder-red);
        }
        
        .dossier-tab:hover:not(.active) {
            background-color: rgba(160, 44, 44, 0.05);
        }
        
        /* Tab Content */
        .dossier-content {
            background-color: var(--manila-folder);
            padding: 2rem;
            position: relative;
            min-height: 400px;
            box-shadow: 3px 3px 8px rgba(0,0,0,0.1);
        }
        
        .dossier-content::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('https://www.transparenttextures.com/patterns/cardboard.png');
            opacity: 0.4;
            pointer-events: none;
        }
        
        .tab-content {
            display: none;
            position: relative;
            z-index: 1;
        }
        
        .tab-content.active {
            display: block;
        }
        
        /* Form Styling */
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-family: var(--typewriter-font);
            color: var(--dark-ink);
        }
        
        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--faded-ink);
            background-color: rgba(255, 255, 255, 0.5);
            font-family: var(--handwritten-font);
            color: var(--dark-ink);
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--folder-red);
            box-shadow: 0 0 0 2px rgba(160, 44, 44, 0.1);
        }
        
        .dossier-section-title {
            font-family: var(--typewriter-font);
            color: var(--folder-red);
            margin-bottom: 1.5rem;
            border-bottom: 2px solid var(--folder-red);
            padding-bottom: 0.5rem;
            display: inline-block;
        }
        
        /* Flash Messages */
        .flash-messages {
            max-width: 1000px;
            margin: 0 auto 1rem;
        }
        
        .flash-message {
            padding: 10px 15px;
            margin-bottom: 10px;
            border-radius: 4px;
            font-family: var(--typewriter-font);
            display: flex;
            align-items: center;
        }
        
        .flash-message.success {
            background-color: rgba(40, 167, 69, 0.1);
            border-left: 4px solid #28a745;
            color: #155724;
        }
        
        .flash-message.error {
            background-color: rgba(220, 53, 69, 0.1);
            border-left: 4px solid #dc3545;
            color: #721c24;
        }
        
        .flash-message.info {
            background-color: rgba(23, 162, 184, 0.1);
            border-left: 4px solid #17a2b8;
            color: #0c5460;
        }
        
        .flash-message i {
            margin-right: 10px;
        }
        
        .fingerprint-bg {
            position: absolute;
            bottom: 20px;
            right: 30px;
            width: 120px;
            height: 120px;
            background-image: url('https://www.transparenttextures.com/patterns/fingerprint.png');
            opacity: 0.05;
            pointer-events: none;
        }
        
        /* Case Files Grid */
        .case-files-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
        }
    </style>
</head>
<body>
    <!-- Header with Navigation -->
    <header>
        <nav class="navbar">
            <div class="logo">
                <a href="../index.html">
                    <h1>Mystery<span>Hub</span></h1>
                </a>
            </div>
            <ul class="nav-links">
                <li><a href="../index.html">Home Base</a></li>
                <li><a href="../games/game1.html">Field Operations</a></li>
                <li><a href="../blog/blog.html">Case Files</a></li>
                <li><a href="../movies/movies.html">Surveillance Footage</a></li>
                <li><a href="../user/dashboard.php">Dashboard</a></li>
                <li><a href="../php/auth/logout.php" title="Logout"><i class="fas fa-sign-out-alt"></i></a></li>
            </ul>
            <div class="menu-toggle">
                <i class="fas fa-bars"></i>
            </div>
        </nav>
    </header>
    
    <!-- Flash Messages -->
    <div class="flash-messages">
        <?php 
        $messages = getFlashMessages();
        if (!empty($messages)) {
            foreach ($messages as $type => $typeMessages) {
                foreach ($typeMessages as $message) {
                    $icon = '';
                    switch ($type) {
                        case 'success':
                            $icon = '<i class="fas fa-check-circle"></i>';
                            break;
                        case 'error':
                            $icon = '<i class="fas fa-exclamation-circle"></i>';
                            break;
                        case 'info':
                            $icon = '<i class="fas fa-info-circle"></i>';
                            break;
                    }
                    echo "<div class='flash-message $type'>$icon $message</div>";
                }
            }
        }
        ?>
    </div>

    <!-- Profile Content -->
    <section class="main-content">
        <div class="container">
            <div class="profile-container">
                <!-- Agent Dossier Header -->
                <div class="agent-dossier">
                    <div class="file-number">AGENT FILE #<?php echo substr($userId, 0, 8); ?></div>
                    <div class="clearance-stamp">Level 3 Clearance</div>
                    
                    <div class="agent-photo-container">
                        <div class="photo-paperclip"></div>
                        <div class="agent-photo">
                            <img src="https://i.pravatar.cc/150?img=<?php echo ord($firstInitial) % 70; ?>" alt="Agent Photo">
                        </div>
                    </div>
                    
                    <div class="agent-info">
                        <h2 class="agent-code-name"><?php echo $displayName; ?></h2>
                        <div class="agent-codename">Codename: "<?php echo htmlspecialchars($username); ?>"</div>
                        
                        <div class="agent-details">
                            <div class="agent-detail">
                                <div class="detail-label">Status:</div>
                                <div class="detail-value">Active Field Agent</div>
                            </div>
                            <div class="agent-detail">
                                <div class="detail-label">Expertise:</div>
                                <div class="detail-value"><?php echo isset($userData['expertise']) ? ucfirst(htmlspecialchars($userData['expertise'])) : 'General Investigation'; ?></div>
                            </div>
                            <div class="agent-detail">
                                <div class="detail-label">Joined:</div>
                                <div class="detail-value"><?php echo $joinDate; ?></div>
                            </div>
                            <div class="agent-detail">
                                <div class="detail-label">Contact:</div>
                                <div class="detail-value"><?php echo htmlspecialchars($userData['email']); ?></div>
                            </div>
                        </div>
                        
                        <div class="agent-stats">
                            <div class="agent-stat">
                                <i class="fas fa-gamepad"></i> <?php echo $completedGames; ?> Field Operations
                            </div>
                            <div class="agent-stat">
                                <i class="fas fa-book-open"></i> <?php echo $favoriteBlogs; ?> Case Files
                            </div>
                            <div class="agent-stat">
                                <i class="fas fa-film"></i> <?php echo $favoriteMovies; ?> Evidence Logs
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Profile Tabs -->
                <div class="dossier-tabs">
                    <div class="dossier-tab active" data-tab="field-ops">Field Operations</div>
                    <div class="dossier-tab" data-tab="case-files">Case Files</div>
                    <div class="dossier-tab" data-tab="evidence-logs">Evidence Logs</div>
                    <div class="dossier-tab" data-tab="agent-settings">Agent Settings</div>
                </div>
                
                <div class="dossier-content">
                    <!-- Field Operations Tab -->
                    <div class="tab-content active" id="field-ops">
                        <h3 class="dossier-section-title">ACTIVE FIELD OPERATIONS</h3>
                        <div class="case-files-grid">
                            <div class="case-file-item">
                                <div class="case-file-img">
                                    <img src="https://images.unsplash.com/photo-1599707367072-cd6ada2bc375?q=80&w=1000" alt="Mystery Game">
                                </div>
                                <div class="case-file-content">
                                    <span class="case-file-category">Field Operation</span>
                                    <h3 class="case-file-title">Who Killed Kayla?</h3>
                                    <p class="case-file-meta">Progress: <?php echo isset($profile['game_progress']['game1']) ? $profile['game_progress']['game1']['progress'] . '%' : 'Not Started'; ?></p>
                                    <a href="../games/game1.html" class="edit-profile-btn" style="display: inline-block; font-size: 0.8rem; margin-top: 0.5rem; padding: 0.3rem 0.6rem;">
                                        <i class="fas fa-play"></i> Play
                                    </a>
                                </div>
                            </div>
                            
                            <div class="case-file-item">
                                <div class="case-file-img">
                                    <img src="https://images.unsplash.com/photo-1487821619655-4943a3202619?q=80&w=1000" alt="Mystery Game">
                                </div>
                                <div class="case-file-content">
                                    <span class="case-file-category">Field Operation</span>
                                    <h3 class="case-file-title">Crime Scene Investigation</h3>
                                    <p class="case-file-meta">Progress: <?php echo isset($profile['game_progress']['game2']) ? $profile['game_progress']['game2']['progress'] . '%' : 'Not Started'; ?></p>
                                    <a href="../games/game2.html" class="edit-profile-btn" style="display: inline-block; font-size: 0.8rem; margin-top: 0.5rem; padding: 0.3rem 0.6rem;">
                                        <i class="fas fa-play"></i> Play
                                    </a>
                                </div>
                            </div>
                            
                            <div class="case-file-item">
                                <div class="case-file-img">
                                    <img src="https://images.unsplash.com/photo-1635614017406-7c192d1f8b76?q=80&w=1000" alt="Mystery Game">
                                </div>
                                <div class="case-file-content">
                                    <span class="case-file-category">Field Operation</span>
                                    <h3 class="case-file-title">The Stolen Manuscript</h3>
                                    <p class="case-file-meta">Progress: <?php echo isset($profile['game_progress']['game3']) ? $profile['game_progress']['game3']['progress'] . '%' : 'Not Started'; ?></p>
                                    <a href="../games/game3.html" class="edit-profile-btn" style="display: inline-block; font-size: 0.8rem; margin-top: 0.5rem; padding: 0.3rem 0.6rem;">
                                        <i class="fas fa-play"></i> Play
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="fingerprint-bg"></div>
                    </div>
                    
                    <!-- Case Files Tab -->
                    <div class="tab-content" id="case-files">
                        <h3 class="dossier-section-title">ARCHIVED CASE FILES</h3>
                        <div class="case-files-grid">
                            <?php
                            // Placeholder for favorite blogs
                            $favBlogs = isset($profile['favorites']['blogs']) ? $profile['favorites']['blogs'] : [];
                            if (empty($favBlogs)) {
                                echo '<p>No case files have been archived yet. Visit the Case Files section to get started.</p>';
                                echo '<a href="../blog/blog.html" class="edit-profile-btn" style="display: inline-block; margin-top: 1rem;">
                                    <i class="fas fa-folder-open"></i> Browse Case Files
                                </a>';
                            } else {
                                // Display favorite blogs (placeholder)
                                echo '<div class="case-file-item">
                                    <div class="case-file-img">
                                        <img src="https://images.unsplash.com/photo-1487821619655-4943a3202619?q=80&w=1000" alt="Case File">
                                    </div>
                                    <div class="case-file-content">
                                        <span class="case-file-category">True Crime</span>
                                        <h3 class="case-file-title">The Zodiac Killer</h3>
                                        <p class="case-file-meta">Archived: April 12, 2025</p>
                                    </div>
                                </div>';
                            }
                            ?>
                        </div>
                        <div class="fingerprint-bg"></div>
                    </div>
                    
                    <!-- Evidence Logs Tab -->
                    <div class="tab-content" id="evidence-logs">
                        <h3 class="dossier-section-title">EVIDENCE CATALOG</h3>
                        <div class="case-files-grid">
                            <?php
                            // Placeholder for favorite movies
                            $favMovies = isset($profile['favorites']['movies']) ? $profile['favorites']['movies'] : [];
                            if (empty($favMovies)) {
                                echo '<p>No evidence logs have been cataloged yet. Visit the Evidence Archive to review surveillance footage.</p>';
                                echo '<a href="../movies/movies.html" class="edit-profile-btn" style="display: inline-block; margin-top: 1rem;">
                                    <i class="fas fa-film"></i> Browse Evidence Archive
                                </a>';
                            } else {
                                // Display favorite movies (placeholder)
                                echo '<div class="case-file-item">
                                    <div class="case-file-img">
                                        <img src="https://images.unsplash.com/photo-1485846234645-a62644f84728?q=80&w=1000" alt="Evidence Log">
                                    </div>
                                    <div class="case-file-content">
                                        <span class="case-file-category">Film Evidence</span>
                                        <h3 class="case-file-title">Knives Out</h3>
                                        <p class="case-file-meta">Analysis Rating: <span class="star-rating">★★★★★</span></p>
                                    </div>
                                </div>';
                            }
                            ?>
                        </div>
                        <div class="fingerprint-bg"></div>
                    </div>
                    
                    <!-- Agent Settings Tab -->
                    <div class="tab-content" id="agent-settings">
                        <h3 class="dossier-section-title">AGENT FILE MAINTENANCE</h3>
                        <form id="profile-settings-form" action="" method="POST">
                            <div class="form-row" style="display: flex; gap: 15px;">
                                <div class="form-group" style="flex: 1;">
                                    <label for="firstname">First Name</label>
                                    <input type="text" id="firstname" name="firstname" value="<?php echo htmlspecialchars($userData['firstname']); ?>" class="form-control" required>
                                </div>
                                <div class="form-group" style="flex: 1;">
                                    <label for="lastname">Last Name</label>
                                    <input type="text" id="lastname" name="lastname" value="<?php echo htmlspecialchars($userData['lastname']); ?>" class="form-control" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="username">Codename (Cannot be changed)</label>
                                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($userData['username']); ?>" class="form-control" disabled>
                            </div>
                            <div class="form-group">
                                <label for="email">Secure Contact (Email)</label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($userData['email']); ?>" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="bio">Field Notes (Optional)</label>
                                <textarea id="bio" name="bio" rows="4" class="form-control"><?php echo isset($userData['bio']) ? htmlspecialchars($userData['bio']) : ''; ?></textarea>
                            </div>
                            
                            <h3 class="dossier-section-title" style="margin-top: 2rem;">SECURITY CREDENTIALS</h3>
                            <div class="form-group">
                                <label for="current_password">Current Security Key (Required for changes)</label>
                                <input type="password" id="current_password" name="current_password" class="form-control">
                            </div>
                            <div class="form-row" style="display: flex; gap: 15px;">
                                <div class="form-group" style="flex: 1;">
                                    <label for="new_password">New Security Key (leave blank to keep current)</label>
                                    <input type="password" id="new_password" name="new_password" class="form-control">
                                </div>
                                <div class="form-group" style="flex: 1;">
                                    <label for="confirm_password">Verify Security Key</label>
                                    <input type="password" id="confirm_password" name="confirm_password" class="form-control">
                                </div>
                            </div>
                            <button type="submit" name="update_profile" class="edit-profile-btn">
                                <i class="fas fa-save"></i> Submit Updates
                            </button>
                        </form>
                        <div class="fingerprint-bg"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Navigation</h3>
                    <ul class="footer-links">
                        <li><a href="../index.html">Home Base</a></li>
                        <li><a href="../games/game1.html">Field Operations</a></li>
                        <li><a href="../blog/blog.html">Case Files</a></li>
                        <li><a href="../movies/movies.html">Surveillance Footage</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Legal Notice</h3>
                    <p>All case files contained herein are property of Mystery Hub Investigations. Unauthorized access will be prosecuted.</p>
                </div>
            </div>
            <div class="copyright">
                <p>&copy; 2025 Mystery Hub | Bintu Jalloh & Michelle Lawson</p>
            </div>
        </div>
    </footer>
    
    <script>
        // Tab switching functionality
        document.addEventListener('DOMContentLoaded', function() {
            const tabs = document.querySelectorAll('.dossier-tab');
            const tabContents = document.querySelectorAll('.tab-content');
            
            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    const tabId = this.getAttribute('data-tab');
                    
                    // Remove active class from all tabs and contents
                    tabs.forEach(t => t.classList.remove('active'));
                    tabContents.forEach(c => c.classList.remove('active'));
                    
                    // Add active class to current tab and content
                    this.classList.add('active');
                    document.getElementById(tabId).classList.add('active');
                });
            });
        });
    </script>
</body>
</html>
