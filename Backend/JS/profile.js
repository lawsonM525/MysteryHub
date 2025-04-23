document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.dossier-tab');
    const tabContents = document.querySelectorAll('.tab-content');
    
    // Tab switching functionality
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
    
    // Load user profile data from backend
    fetchProfileData();
    
    // Modal functionality
    const modal = document.getElementById('editProfileModal');
    const editBtn = document.getElementById('edit-profile-btn');
    const closeBtn = document.querySelector('.close-modal');
    const cancelBtn = document.getElementById('cancel-edit-btn');
    
    // Open modal when edit button is clicked
    editBtn.addEventListener('click', function() {
        modal.style.display = 'block';
        // Ensure the profile picture in modal is updated
        const profilePic = document.getElementById('profile-picture-display');
        const modalPic = document.getElementById('profile-picture');
        modalPic.src = profilePic.src;
    });
    
    // Close modal when X is clicked
    closeBtn.addEventListener('click', function() {
        modal.style.display = 'none';
    });
    
    // Close modal when Cancel button is clicked
    cancelBtn.addEventListener('click', function() {
        modal.style.display = 'none';
    });
    
    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
    
    // Preview uploaded profile picture
    const profilePicUpload = document.getElementById('profile-picture-upload');
    profilePicUpload.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('profile-picture').src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });
    
    // Handle profile form submission
    const profileForm = document.getElementById('profile-settings-form');
    if (profileForm) {
        profileForm.addEventListener('submit', function(e) {
            e.preventDefault();
            updateProfile(this);
        });
    }
});

// Fetch user profile data from the backend API
function fetchProfileData() {
    // Display loading state
    displayFlashMessage('info', 'Loading agent data...', false);
    
    fetch('../Backend/Php/get_profile_data.php')
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                // If not logged in, redirect to login page
                if (data.redirect) {
                    window.location.href = data.redirect;
                    return;
                }
                displayFlashMessage('error', data.message || 'Failed to load profile data.');
                return;
            }
            
            // Update profile data in the UI
            updateProfileUI(data);
            
            // Display any flash messages
            if (data.flash_messages) {
                for (const type in data.flash_messages) {
                    for (const message of data.flash_messages[type]) {
                        displayFlashMessage(type, message);
                    }
                }
            }
        })
        .catch(error => {
            console.error('Error fetching profile data:', error);
            displayFlashMessage('error', 'Failed to connect to the server. Please try again later.');
        });
}

// Update the UI with profile data
function updateProfileUI(data) {
    // User name and codename
    document.querySelector('.agent-code-name').textContent = `Agent ${data.user.firstname.charAt(0)}. ${data.user.lastname.charAt(0)}.`;
    document.querySelector('.agent-codename').textContent = `Codename: "${data.user.username}"`;
    
    // File number and clearance
    const fileId = data.user.id.substring(3, 7); // Extract part of unique ID for file number
    document.getElementById('agent-file-number').textContent = `AGENT FILE #MH-${new Date().getFullYear()}-${fileId}`;
    
    // Set clearance level based on join date (just for visual effect)
    const joinDate = new Date(data.user.created_at);
    const now = new Date();
    const monthsActive = (now.getFullYear() - joinDate.getFullYear()) * 12 + now.getMonth() - joinDate.getMonth();
    let clearanceLevel = 1;
    if (monthsActive > 3) clearanceLevel = 2;
    if (monthsActive > 6) clearanceLevel = 3;
    document.getElementById('clearance-level').textContent = `LEVEL ${clearanceLevel} CLEARANCE`;
    
    // Agent photo - if user has a profile picture, use it, otherwise default
    const profilePic = document.getElementById('profile-picture-display');
    if (data.user.profile_picture) {
        profilePic.src = '../' + data.user.profile_picture;
    } else {
        profilePic.src = '../img/default-profile.png';
    }
    
    // Agent details
    document.querySelectorAll('.agent-detail').forEach(detail => {
        const label = detail.querySelector('.detail-label');
        if (label) {
            const value = detail.querySelector('.detail-value');
            if (label.textContent === 'Joined:') {
                value.textContent = data.display.joinDate;
            } else if (label.textContent === 'Division:') {
                value.textContent = data.user.expertise || 'Investigation & Analysis';
            } else if (label.textContent === 'Status:') {
                value.textContent = 'Active Field Agent';
            } else if (label.textContent === 'Bio:') {
                value.textContent = data.user.bio || 'No field notes available.';
            }
        }
    });
    
    // Stats
    const statsElements = document.querySelectorAll('.agent-stat');
    statsElements.forEach(stat => {
        const icon = stat.querySelector('i');
        if (icon) {
            if (icon.classList.contains('fa-gamepad')) {
                stat.innerHTML = `<i class="fas fa-gamepad"></i> ${data.stats.completedGames} Field Operations`;
            } else if (icon.classList.contains('fa-book-open')) {
                stat.innerHTML = `<i class="fas fa-book-open"></i> ${data.stats.favoriteBlogs} Case Files`;
            } else if (icon.classList.contains('fa-film')) {
                stat.innerHTML = `<i class="fas fa-film"></i> ${data.stats.favoriteMovies} Evidence Logs`;
            }
        }
    });
    
    // Profile form - populate form fields with user data
    const profileForm = document.getElementById('profile-settings-form');
    if (profileForm) {
        const firstnameInput = profileForm.querySelector('#firstname');
        if (firstnameInput) {
            firstnameInput.value = data.user.firstname;
        }
        
        const lastnameInput = profileForm.querySelector('#lastname');
        if (lastnameInput) {
            lastnameInput.value = data.user.lastname;
        }
        
        const usernameInput = profileForm.querySelector('#username');
        if (usernameInput) {
            usernameInput.value = data.user.username;
        }
        
        const emailInput = profileForm.querySelector('#email');
        if (emailInput) {
            emailInput.value = data.user.email;
        }
        
        const expertiseInput = profileForm.querySelector('#expertise');
        if (expertiseInput) {
            expertiseInput.value = data.user.expertise || '';
        }
        
        const bioInput = profileForm.querySelector('#bio');
        if (bioInput) {
            bioInput.value = data.user.bio || '';
        }
        
        // Update profile picture in form
        const profilePicture = document.getElementById('profile-picture');
        if (profilePicture && data.user.profile_picture) {
            profilePicture.src = '../' + data.user.profile_picture;
        }
    }
}

// Update profile via API
function updateProfile(form) {
    const formData = new FormData(form);
    
    // Display loading state
    displayFlashMessage('info', 'Updating profile...', false);
    
    fetch('../Backend/Php/update_profile.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayFlashMessage('success', data.message || 'Profile updated successfully!');
            
            // Close the modal
            document.getElementById('editProfileModal').style.display = 'none';
            
            // Reload profile data to show updated information
            fetchProfileData();
        } else {
            displayFlashMessage('error', data.message || 'Failed to update profile.');
            
            // Display individual validation errors
            if (data.errors && Array.isArray(data.errors)) {
                data.errors.forEach(error => {
                    displayFlashMessage('error', error);
                });
            }
        }
    })
    .catch(error => {
        console.error('Error updating profile:', error);
        displayFlashMessage('error', 'Failed to connect to the server. Please try again later.');
    });
}

// Display flash messages
function displayFlashMessage(type, message, autoHide = true) {
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
    if (autoHide) {
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
    }
    
    return messageElement;
}