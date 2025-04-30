<?php
session_start();

// Check if user is logged in and is an admin
$isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
$isLoggedIn = isset($_SESSION['user_id']);

// If not admin, display restricted access message
if (!$isAdmin) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Access Denied - Mystery Hub</title>
        <link rel="stylesheet" href="../../assets/css/style.css">
        <link rel="stylesheet" href="../../css/admin.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    </head>
    <body>
        <div class="access-denied-container">
            <div class="access-denied-message">
                <h1><i class="fas fa-exclamation-triangle"></i> HEY!!! THIS PAGE IS RESERVED FOR ADMINS! 😡</h1>
                <a href="../../index.html" class="back-home-btn">Back to Home</a>
            </div>
        </div>
        <style>
            .access-denied-container {
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                background-color: var(--aged-paper);
                background-image: url('https://www.transparenttextures.com/patterns/old-paper.png');
            }
            
            .access-denied-message {
                max-width: 600px;
                text-align: center;
                padding: 3rem;
                background-color: var(--manila-folder);
                border: 2px solid var(--folder-red);
                box-shadow: 4px 4px 10px rgba(0, 0, 0, 0.2);
                font-family: var(--typewriter-font);
            }
            
            .access-denied-message h1 {
                color: var(--folder-red);
                margin-bottom: 2rem;
                font-size: 1.8rem;
            }
            
            .access-denied-message i {
                margin-right: 0.5rem;
            }
            
            .back-home-btn {
                display: inline-block;
                padding: 0.75rem 1.5rem;
                background-color: var(--folder-red);
                color: white;
                text-decoration: none;
                font-family: var(--typewriter-font);
                font-weight: bold;
                border: none;
                cursor: pointer;
                transition: all 0.3s;
            }
            
            .back-home-btn:hover {
                background-color: #8a2525;
                transform: translateY(-2px);
            }
        </style>
    </body>
    </html>
    <?php
    exit;
}

// If admin, redirect to the actual admin.html page
header("Location: ../../Pages/admin.html");
exit;
?>
