<?php
/**
 * Mystery Hub Flash Messages Component
 * Displays flash messages to the user
 */

// Include configuration if not already included
if (!function_exists('getFlashMessages')) {
    require_once dirname(__DIR__) . '/php/config.php';
}

// Get all flash messages
$flashMessages = getFlashMessages();

// Define message types and their CSS classes
$messageTypes = [
    'success' => [
        'class' => 'flash-success',
        'icon' => 'fa-check-circle'
    ],
    'error' => [
        'class' => 'flash-error',
        'icon' => 'fa-exclamation-circle'
    ],
    'warning' => [
        'class' => 'flash-warning',
        'icon' => 'fa-exclamation-triangle'
    ],
    'info' => [
        'class' => 'flash-info',
        'icon' => 'fa-info-circle'
    ]
];

// Display flash messages if there are any
if (!empty($flashMessages)) {
    echo '<div class="flash-messages">';
    
    foreach ($flashMessages as $type => $messages) {
        if (!isset($messageTypes[$type])) {
            continue;
        }
        
        $class = $messageTypes[$type]['class'];
        $icon = $messageTypes[$type]['icon'];
        
        foreach ($messages as $message) {
            echo "<div class=\"flash-message {$class}\">";
            echo "<i class=\"fas {$icon}\"></i> ";
            echo htmlspecialchars($message);
            echo "<span class=\"flash-close\"><i class=\"fas fa-times\"></i></span>";
            echo "</div>";
        }
    }
    
    echo '</div>';
    
    // Add JavaScript to allow closing flash messages
    echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            const closeButtons = document.querySelectorAll(".flash-close");
            closeButtons.forEach(function(button) {
                button.addEventListener("click", function() {
                    this.parentElement.style.opacity = "0";
                    setTimeout(() => {
                        this.parentElement.style.display = "none";
                    }, 300);
                });
            });
            
            // Auto-hide messages after 5 seconds
            setTimeout(function() {
                const messages = document.querySelectorAll(".flash-message");
                messages.forEach(function(message) {
                    message.style.opacity = "0";
                    setTimeout(() => {
                        message.style.display = "none";
                    }, 300);
                });
            }, 5000);
        });
    </script>';
}
?>

<style>
.flash-messages {
    position: fixed;
    top: 20px;
    right: 20px;
    max-width: 350px;
    z-index: 9999;
    font-family: var(--typewriter-font, 'Courier New', monospace);
}

.flash-message {
    margin-bottom: 10px;
    padding: 12px 15px;
    border-radius: 4px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    position: relative;
    animation: slidein 0.3s ease-in-out;
    transition: opacity 0.3s ease;
}

.flash-success {
    background-color: #d4edda;
    border-left: 4px solid #28a745;
    color: #155724;
}

.flash-error {
    background-color: #f8d7da;
    border-left: 4px solid #dc3545;
    color: #721c24;
}

.flash-warning {
    background-color: #fff3cd;
    border-left: 4px solid #ffc107;
    color: #856404;
}

.flash-info {
    background-color: #d1ecf1;
    border-left: 4px solid #17a2b8;
    color: #0c5460;
}

.flash-close {
    position: absolute;
    top: 8px;
    right: 8px;
    cursor: pointer;
    opacity: 0.7;
    transition: opacity 0.2s;
}

.flash-close:hover {
    opacity: 1;
}

@keyframes slidein {
    from {
        transform: translateX(30px);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}
</style>
