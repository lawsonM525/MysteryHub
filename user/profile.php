<?php
/**
 * Mystery Hub User Profile
 * Allows users to view and update their profile information
 */

// Include authentication check
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
    redirect('/MysteryHubProject/user/dashboard.php');
    exit;
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    // Get and sanitize form data
    $firstname = sanitizeInput($_POST['firstname'] ?? '');
    $lastname = sanitizeInput($_POST['lastname'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $expertise = sanitizeInput($_POST['expertise'] ?? '');
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
    if (!empty($currentPassword) || !empty($newPassword) || !empty($confirmPassword)) {
        // Validate current password
        if (empty($currentPassword) || !password_verify($currentPassword, $userData['password'])) {
            $errors[] = "Current encryption key (password) is incorrect";
        }
        
        if (empty($newPassword) || strlen($newPassword) < 6) {
            $errors[] = "New encryption key must be at least 6 characters";
        }
        
        if ($newPassword !== $confirmPassword) {
            $errors[] = "New encryption key and confirmation do not match";
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
                $user['expertise'] = $expertise;
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
            
            setFlashMessage('success', 'Profile updated successfully.');
            redirect('/MysteryHubProject/user/profile.php');
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
$pageTitle = "Agent Profile | Mystery Hub";
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
            max-width: 800px;
            margin: 50px auto;
            position: relative;
            border: 1px solid var(--faded-ink);
            background-color: var(--manila-folder);
            padding: 3rem 2rem 2rem;
            box-shadow: 5px 5px 15px rgba(0,0,0,0.2);
            color: var(--dark-ink);
        }
        
        .profile-container::before {
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
        
        .case-number {
            position: absolute;
            top: 10px;
            left: 10px;
            font-family: var(--typewriter-font);
            font-size: 0.8rem;
            color: var(--folder-red);
        }
        
        .stamp {
            position: absolute;
            top: 15px;
            right: 15px;
            transform: rotate(-10deg);
            color: var(--stamp-red);
            border: 2px solid var(--stamp-red);
            padding: 5px 12px;
            font-family: var(--typewriter-font);
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 1px;
        }
        
        .file-tab {
            position: absolute;
            top: 0;
            right: 50px;
            width: 120px;
            height: 30px;
            background-color: var(--file-tab);
            border: 1px solid var(--faded-ink);
            border-bottom: none;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: var(--typewriter-font);
            font-size: 0.9rem;
            text-transform: uppercase;
        }
        
        .paper-clip {
            position: absolute;
            top: -10px;
            left: 30px;
            width: 30px;
            height: 60px;
            border: 3px solid #a8a8a8;
            border-radius: 15px 15px 0 0;
            border-bottom: none;
            transform: rotate(-10deg);
            pointer-events: none;
            z-index: 5;
        }
        
        .profile-title {
            text-align: center;
            margin-bottom: 1.5rem;
            font-family: var(--typewriter-font);
            color: var(--folder-red);
            letter-spacing: 1px;
            border-bottom: 2px solid var(--folder-red);
            padding-bottom: 0.5rem;
        }
        
        .profile-section {
            margin-bottom: 30px;
        }
        
        .section-header {
            font-family: var(--typewriter-font);
            color: var(--folder-red);
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .section-header i {
            margin-right: 10px;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-family: var(--typewriter-font);
            color: var(--dark-ink);
        }
        
        .form-group input, .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--faded-ink);
            background-color: rgba(255, 255, 255, 0.5);
            font-family: var(--handwritten-font);
            color: var(--dark-ink);
            transition: all 0.3s ease;
        }
        
        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: var(--folder-red);
            box-shadow: 0 0 0 2px rgba(160, 44, 44, 0.1);
        }
        
        .form-note {
            font-size: 0.8rem;
            font-style: italic;
            color: var(--stamp-red);
            margin-top: 0.5rem;
        }
        
        .form-row {
            display: flex;
            gap: 15px;
        }
        
        .form-row .form-group {
            flex: 1;
        }
        
        .action-btn {
            background-color: var(--folder-red);
            color: var(--aged-paper);
            border: none;
            padding: 8px 15px;
            font-family: var(--typewriter-font);
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            position: relative;
            overflow: hidden;
            border: 1px solid var(--folder-red);
            margin-top: 1rem;
        }
        
        .action-btn:hover {
            background-color: transparent;
            color: var(--folder-red);
        }
        
        .back-btn {
            display: inline-flex;
            align-items: center;
            background-color: transparent;
            color: var(--folder-red);
            border: 1px solid var(--folder-red);
            padding: 8px 15px;
            font-family: var(--typewriter-font);
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            margin-right: 15px;
        }
        
        .back-btn:hover {
            background-color: var(--folder-red);
            color: var(--aged-paper);
        }
        
        .back-btn i {
            margin-right: 8px;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .thread-holes {
            position: absolute;
            top: 15px;
            left: 0;
            width: 100%;
            display: flex;
            justify-content: space-around;
            z-index: 1;
        }
        
        .hole {
            width: 8px;
            height: 8px;
            background-color: var(--aged-paper);
            border-radius: 50%;
            border: 1px solid var(--faded-ink);
        }
        
        .file-info {
            font-family: var(--typewriter-font);
            font-size: 0.8rem;
            color: var(--faded-ink);
            margin-top: 30px;
            text-align: center;
            border-top: 1px dashed var(--faded-ink);
            padding-top: 15px;
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
                <li><a href="dashboard.php" class="active">Agent Portal</a></li>
            </ul>
            <div class="menu-toggle">
                <i class="fas fa-bars"></i>
            </div>
        </nav>
    </header>

    <!-- Flash Messages -->
    <?php include_once dirname(__DIR__) . '/includes/flash_messages.php'; ?>

    <!-- Profile Content -->
    <section class="main-content">
        <div class="container">
            <div class="profile-container">
                <div class="case-number">PERSONNEL FILE #<?php echo substr($userId, 3, 8); ?></div>
                <div class="file-tab">Personnel</div>
                <div class="paper-clip"></div>
                <div class="stamp">Confidential</div>
                <div class="thread-holes">
                    <div class="hole"></div>
                    <div class="hole"></div>
                    <div class="hole"></div>
                    <div class="hole"></div>
                </div>
                
                <h2 class="profile-title">AGENT PROFILE</h2>
                
                <form action="" method="POST" class="profile-form">
                    <div class="profile-section">
                        <h3 class="section-header"><i class="fas fa-id-card"></i> Identification Data</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label class="typed-label" for="firstname">First Name:</label>
                                <input type="text" id="firstname" name="firstname" value="<?php echo htmlspecialchars($userData['firstname']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="typed-label" for="lastname">Last Name:</label>
                                <input type="text" id="lastname" name="lastname" value="<?php echo htmlspecialchars($userData['lastname']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="typed-label" for="email">Secure Contact (Email):</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($userData['email']); ?>" required>
                            <div class="form-note">* Used for secure communications and account recovery</div>
                        </div>
                        
                        <div class="form-group">
                            <label class="typed-label" for="username">Codename (Username):</label>
                            <input type="text" id="username" value="<?php echo htmlspecialchars($userData['username']); ?>" disabled>
                            <div class="form-note">* Codenames cannot be modified for security purposes</div>
                        </div>
                        
                        <div class="form-group">
                            <label class="typed-label" for="expertise">Field Expertise:</label>
                            <select id="expertise" name="expertise" required>
                                <option value="detective" <?php echo $userData['expertise'] === 'detective' ? 'selected' : ''; ?>>Field Detective</option>
                                <option value="forensics" <?php echo $userData['expertise'] === 'forensics' ? 'selected' : ''; ?>>Forensic Analysis</option>
                                <option value="profiler" <?php echo $userData['expertise'] === 'profiler' ? 'selected' : ''; ?>>Criminal Profiling</option>
                                <option value="cryptography" <?php echo $userData['expertise'] === 'cryptography' ? 'selected' : ''; ?>>Cryptography</option>
                                <option value="surveillance" <?php echo $userData['expertise'] === 'surveillance' ? 'selected' : ''; ?>>Surveillance Operations</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="profile-section">
                        <h3 class="section-header"><i class="fas fa-lock"></i> Security Credentials</h3>
                        
                        <div class="form-group">
                            <label class="typed-label" for="current_password">Current Encryption Key:</label>
                            <input type="password" id="current_password" name="current_password">
                            <div class="form-note">* Required only if changing your encryption key</div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label class="typed-label" for="new_password">New Encryption Key:</label>
                                <input type="password" id="new_password" name="new_password">
                            </div>
                            <div class="form-group">
                                <label class="typed-label" for="confirm_password">Verify New Key:</label>
                                <input type="password" id="confirm_password" name="confirm_password">
                            </div>
                        </div>
                    </div>
                    
                    <div class="action-buttons">
                        <a href="dashboard.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
                        <button type="submit" name="update_profile" class="action-btn"><i class="fas fa-save"></i> Update Profile</button>
                    </div>
                </form>
                
                <div class="file-info">
                    Agent Record Created: <?php echo date('F j, Y', strtotime($userData['created_at'])); ?><br>
                    Last Modified: <?php echo date('F j, Y', strtotime($userData['updated_at'])); ?>
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
</body>
</html>
