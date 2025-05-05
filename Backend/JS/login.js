document.addEventListener('DOMContentLoaded', function() {
    // Check for flash messages when page loads
    fetchFlashMessages();
});

// Fetch flash messages from the session
function fetchFlashMessages() {
    fetch('../Backend/Php/get_flash_messages.php')
        .then(response => response.json())
        .then(data => {
            if (data.messages) {
                // Display each message
                for (const type in data.messages) {
                    if (data.messages[type] && data.messages[type].length > 0) {
                        data.messages[type].forEach(message => {
                            displayFlashMessage(type, message);
                        });
                    }
                }
            }
        })
        .catch(error => {
            console.error('Error fetching flash messages:', error);
        });
}

// Display flash messages
function displayFlashMessage(type, message) {
    // Create container if it doesn't exist
    let flashContainer = document.querySelector('.flash-messages');
    if (!flashContainer) {
        flashContainer = document.createElement('div');
        flashContainer.className = 'flash-messages';
        document.body.insertBefore(flashContainer, document.body.firstChild);
        
        // Add styles if not already present
        if (!document.getElementById('flash-message-styles')) {
            const style = document.createElement('style');
            style.id = 'flash-message-styles';
            style.textContent = `
                .flash-messages {
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    max-width: 350px;
                    z-index: 9999;
                    font-family: var(--typewriter-font, 'Special Elite', courier, monospace);
                }
                .flash-message {
                    margin-bottom: 10px;
                    padding: 12px 15px;
                    border-radius: 4px;
                    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
                    position: relative;
                    animation: slidein 0.3s ease-in-out;
                    transition: opacity 0.3s ease;
                    color: #1a1a1a;
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
            `;
            document.head.appendChild(style);
        }
    }
    
    // Create message element
    const messageElement = document.createElement('div');
    messageElement.className = `flash-message flash-${type}`;
    
    // Add icon based on message type
    let icon = '';
    switch (type) {
        case 'success':
            icon = '<i class="fas fa-check-circle"></i>';
            break;
        case 'error':
            icon = '<i class="fas fa-exclamation-circle"></i>';
            break;
        case 'info':
            icon = '<i class="fas fa-info-circle"></i>';
            break;
    }
    
    messageElement.innerHTML = `${icon} ${message}<span class="flash-close"><i class="fas fa-times"></i></span>`;
    flashContainer.appendChild(messageElement);
    
    // Add close button functionality
    const closeButton = messageElement.querySelector('.flash-close');
    closeButton.addEventListener('click', function() {
        messageElement.style.opacity = '0';
        setTimeout(() => {
            messageElement.remove();
        }, 300);
    });
    
    // Auto-hide after 5 seconds
    setTimeout(function() {
        if (messageElement.parentElement) {
            messageElement.style.opacity = '0';
            setTimeout(() => {
                if (messageElement.parentElement) {
                    messageElement.remove();
                }
            }, 300);
        }
    }, 5000);
    
    return messageElement;
}

// Check for login message in sessionStorage when the page loads
document.addEventListener('DOMContentLoaded', function() {
    const loginMessage = sessionStorage.getItem('loginMessage');
    if (loginMessage) {
        // Display the message
        displayFlashMessage('info', loginMessage);
        // Remove it from sessionStorage to not show it again on refresh
        sessionStorage.removeItem('loginMessage');
    }
});

// Function to display flash messages
function displayFlashMessage(type, message, autoHide = true) {
    // Create or get messages container
    let messagesContainer = document.querySelector('.flash-messages');
    if (!messagesContainer) {
        messagesContainer = document.createElement('div');
        messagesContainer.className = 'flash-messages';
        document.body.appendChild(messagesContainer);
    }
    
    // Create message element
    const messageElement = document.createElement('div');
    messageElement.className = `flash-message ${type}`;
    
    // Add icon based on message type
    let icon = 'info-circle';
    if (type === 'success') icon = 'check-circle';
    if (type === 'error') icon = 'exclamation-triangle';
    if (type === 'warning') icon = 'exclamation-circle';
    
    messageElement.innerHTML = `
        <i class="fas fa-${icon}"></i>
        <span>${message}</span>
        <button class="close-message"><i class="fas fa-times"></i></button>
    `;
    
    // Add to container
    messagesContainer.appendChild(messageElement);
    
    // Add close button functionality
    const closeBtn = messageElement.querySelector('.close-message');
    closeBtn.addEventListener('click', function() {
        messageElement.classList.add('hiding');
        setTimeout(() => {
            messageElement.remove();
            
            // If this was the last message, remove the container
            if (messagesContainer.querySelectorAll('.flash-message').length === 0) {
                messagesContainer.remove();
            }
        }, 300);
    });
    
    // Auto-hide after 5 seconds if enabled
    if (autoHide) {
        setTimeout(() => {
            if (messageElement.parentNode) {
                messageElement.classList.add('hiding');
                setTimeout(() => {
                    if (messageElement.parentNode) {
                        messageElement.remove();
                        
                        // If this was the last message, remove the container
                        if (messagesContainer.querySelectorAll('.flash-message').length === 0) {
                            messagesContainer.remove();
                        }
                    }
                }, 300);
            }
        }, 5000);
    }
    
    return messageElement;
}