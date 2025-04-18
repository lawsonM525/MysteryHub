<?php
/**
 * Mystery Hub Admin Authentication Check
 * Include this file on pages that require admin authentication
 */

// Include configuration
require_once dirname(__DIR__) . '/config.php';

// First check if user is logged in
require_once dirname(__DIR__) . '/auth/auth_check.php';

// Then check if user is an admin
if (!isAdmin()) {
    // Set error message
    setFlashMessage('error', 'You do not have permission to access this page.');
    
    // Redirect to user dashboard
    redirect('/MysteryHubProject/user/dashboard.php');
    exit;
}
