<?php
/**
 * Mystery Hub User Dashboard
 * The main page users see after logging in
 */

// Include authentication check
require_once dirname(__DIR__) . '/php/auth/auth_check.php';

// Get user data
$userId = $_SESSION['user_id'];
$username = $_SESSION['username'];
$firstname = $_SESSION['firstname'];
$lastname = $_SESSION['lastname'];

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

// Page title
$pageTitle = "Agent Dashboard | Mystery Hub";
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
        
        .dashboard-container {
            max-width: 1000px;
            margin: 50px auto;
            position: relative;
            border: 1px solid var(--faded-ink);
            background-color: var(--manila-folder);
            padding: 3rem 2rem 2rem;
            box-shadow: 5px 5px 15px rgba(0,0,0,0.2);
            color: var(--dark-ink);
        }
        
        .dashboard-container::before {
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
        
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            border-bottom: 2px solid var(--folder-red);
            padding-bottom: 15px;
        }
        
        .dashboard-title {
            font-family: var(--typewriter-font);
            color: var(--folder-red);
            margin: 0;
        }
        
        .agent-info {
            display: flex;
            align-items: center;
            font-family: var(--typewriter-font);
        }
        
        .agent-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background-color: var(--faded-ink);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: var(--aged-paper);
            font-size: 24px;
        }
        
        .agent-details h3 {
            margin: 0;
            color: var(--dark-ink);
        }
        
        .agent-id {
            font-size: 0.8rem;
            color: var(--faded-ink);
        }
        
        .dashboard-sections {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        
        .dashboard-section {
            background-color: rgba(255, 255, 255, 0.5);
            border: 1px solid var(--faded-ink);
            padding: 20px;
            position: relative;
        }
        
        .section-header {
            font-family: var(--typewriter-font);
            color: var(--folder-red);
            border-bottom: 1px solid var(--faded-ink);
            padding-bottom: 10px;
            margin-top: 0;
            display: flex;
            align-items: center;
        }
        
        .section-header i {
            margin-right: 10px;
        }
        
        .stat-cards {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .stat-card {
            flex: 1;
            min-width: 120px;
            background-color: var(--manila-folder);
            border: 1px solid var(--faded-ink);
            padding: 15px;
            text-align: center;
            font-family: var(--typewriter-font);
        }
        
        .stat-value {
            font-size: 2rem;
            color: var(--folder-red);
            margin: 10px 0;
        }
        
        .stat-label {
            font-size: 0.8rem;
            color: var(--dark-ink);
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .action-btn {
            background-color: var(--folder-red);
            color: var(--aged-paper);
            border: none;
            padding: 8px 15px;
            font-family: var(--typewriter-font);
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
        }
        
        .action-btn i {
            margin-right: 5px;
        }
        
        .action-btn:hover {
            background-color: transparent;
            color: var(--folder-red);
            border: 1px solid var(--folder-red);
        }
        
        .recent-activity {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .activity-item {
            padding: 10px;
            border-bottom: 1px dashed var(--faded-ink);
            font-family: var(--handwritten-font);
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-date {
            font-size: 0.8rem;
            color: var(--faded-ink);
            margin-top: 5px;
            display: block;
        }
        
        .notification-badge {
            background-color: var(--folder-red);
            color: white;
            border-radius: 50%;
            padding: 3px 8px;
            font-size: 0.8rem;
            margin-left: 10px;
        }
        
        .logout-btn {
            background-color: transparent;
            color: var(--folder-red);
            border: 1px solid var(--folder-red);
            padding: 8px 15px;
            font-family: var(--typewriter-font);
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }
        
        .logout-btn:hover {
            background-color: var(--folder-red);
            color: var(--aged-paper);
        }
        
        .logout-btn i {
            margin-right: 8px;
        }
        
        .case-file-notice {
            font-family: var(--typewriter-font);
            font-size: 0.8rem;
            color: var(--faded-ink);
            text-align: center;
            margin-top: 30px;
            border-top: 1px dashed var(--faded-ink);
            padding-top: 15px;
        }
        
        .classified-stamp {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            opacity: 0.1;
            font-size: 5rem;
            color: var(--stamp-red);
            font-family: var(--typewriter-font);
            font-weight: bold;
            pointer-events: none;
            z-index: 0;
            letter-spacing: 5px;
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
                <li><a href="#" class="active">Agent Portal</a></li>
            </ul>
            <div class="menu-toggle">
                <i class="fas fa-bars"></i>
            </div>
        </nav>
    </header>

    <!-- Flash Messages -->
    <?php include_once dirname(__DIR__) . '/includes/flash_messages.php'; ?>

    <!-- Dashboard Content -->
    <section class="main-content">
        <div class="container">
            <div class="dashboard-container">
                <div class="classified-stamp">CLASSIFIED</div>
                
                <div class="dashboard-header">
                    <h2 class="dashboard-title">FIELD AGENT DASHBOARD</h2>
                    <div class="agent-info">
                        <div class="agent-avatar">
                            <i class="fas fa-user-secret"></i>
                        </div>
                        <div class="agent-details">
                            <h3><?php echo htmlspecialchars($firstname . ' ' . $lastname); ?></h3>
                            <div class="agent-id">Codename: <strong><?php echo htmlspecialchars($username); ?></strong></div>
                            <div class="agent-id">ID: <?php echo substr($userId, 0, 12); ?></div>
                        </div>
                    </div>
                </div>
                
                <a href="../php/auth/logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Debrief & Exit
                </a>
                
                <div class="dashboard-sections">
                    <div class="dashboard-section">
                        <h3 class="section-header"><i class="fas fa-chart-line"></i> Operation Statistics</h3>
                        <div class="stat-cards">
                            <div class="stat-card">
                                <div class="stat-value"><?php echo $completedGames; ?></div>
                                <div class="stat-label">Missions Completed</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-value"><?php echo $favoriteBlogs; ?></div>
                                <div class="stat-label">Case Files Saved</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-value"><?php echo $favoriteMovies; ?></div>
                                <div class="stat-label">Films Analyzed</div>
                            </div>
                        </div>
                        <div class="action-buttons">
                            <a href="../games/game1.html" class="action-btn"><i class="fas fa-gamepad"></i> Field Operations</a>
                            <a href="profile.php" class="action-btn"><i class="fas fa-id-card"></i> Agent Profile</a>
                        </div>
                    </div>
                    
                    <div class="dashboard-section">
                        <h3 class="section-header"><i class="fas fa-bell"></i> Recent Activity <span class="notification-badge">3</span></h3>
                        <ul class="recent-activity">
                            <li class="activity-item">
                                New case file added: "The Vanishing of Emily Parker"
                                <span class="activity-date">April 16, 2025 - 10:23 AM</span>
                            </li>
                            <li class="activity-item">
                                You successfully completed the "Who Killed Kayla?" investigation
                                <span class="activity-date">April 15, 2025 - 3:47 PM</span>
                            </li>
                            <li class="activity-item">
                                New evidence discovered in "The Stolen Manuscript" case
                                <span class="activity-date">April 14, 2025 - 9:12 AM</span>
                            </li>
                        </ul>
                        <div class="action-buttons">
                            <a href="../blog/blog.html" class="action-btn"><i class="fas fa-folder-open"></i> Browse Case Files</a>
                            <a href="../movies/movies.html" class="action-btn"><i class="fas fa-film"></i> Footage Archive</a>
                        </div>
                    </div>
                </div>
                
                <div class="case-file-notice">
                    NOTICE: All activity within this system is monitored and recorded per Mystery Hub security protocol MH-SEC-429. Unauthorized access or disclosure of information is strictly prohibited.
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
