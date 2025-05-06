document.addEventListener('DOMContentLoaded', function() {
    // Variables for elements
    const menuItems = document.querySelectorAll('.menu-item');
    const tabContents = document.querySelectorAll('.tab-content');
    const addUserBtn = document.getElementById('add-user-btn');
    const addUserModal = document.getElementById('add-user-modal');
    const editUserModal = document.getElementById('edit-user-modal');
    const deleteUserModal = document.getElementById('delete-user-modal');
    const deleteBlogModal = document.getElementById('delete-blog-modal');
    const closeModals = document.querySelectorAll('.close-modal');
    const cancelAddBtn = document.getElementById('cancel-add-btn');
    const cancelEditBtn = document.getElementById('cancel-edit-btn');
    const cancelDeleteBtn = document.getElementById('cancel-delete-btn');
    const cancelDeleteBlogBtn = document.getElementById('cancel-delete-blog-btn');
    const confirmDeleteBtn = document.getElementById('confirm-delete-btn');
    const confirmDeleteBlogBtn = document.getElementById('confirm-delete-blog-btn');
    const addUserForm = document.getElementById('add-user-form');
    const editUserForm = document.getElementById('edit-user-form');
    const userTableBody = document.getElementById('user-table-body');
    const userSearch = document.getElementById('user-search');
    const userFilter = document.getElementById('user-filter');
    const blogSearch = document.getElementById('blog-search');
    const blogFilter = document.getElementById('blog-filter');
    const blogsTableBody = document.getElementById('blogs-table-body');
    const blogsLoading = document.getElementById('blogs-loading');
    const noBlogsMessage = document.getElementById('no-blogs-message');
    const usersLoading = document.getElementById('users-loading');
    const noUsersMessage = document.getElementById('no-users-message');
    
    // Tab switching
    menuItems.forEach(item => {
        item.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            
            // Remove active class from all tabs and contents
            menuItems.forEach(i => i.classList.remove('active'));
            tabContents.forEach(c => c.classList.remove('active'));
            
            // Add active class to current tab and content
            this.classList.add('active');
            document.getElementById(tabId).classList.add('active');
        });
    });
    
    // Modal functions
    function openModal(modal) {
        modal.style.display = 'block';
    }
    
    function closeModal(modal) {
        modal.style.display = 'none';
    }
    
    // Open modals
    addUserBtn.addEventListener('click', function() {
        openModal(addUserModal);
    });
    
    // Close modals with X button
    closeModals.forEach(btn => {
        btn.addEventListener('click', function() {
            const modal = this.closest('.modal');
            closeModal(modal);
        });
    });
    
    // Close modals with cancel buttons
    cancelAddBtn.addEventListener('click', function() {
        closeModal(addUserModal);
    });
    
    cancelEditBtn.addEventListener('click', function() {
        closeModal(editUserModal);
    });
    
    cancelDeleteBtn.addEventListener('click', function() {
        closeModal(deleteUserModal);
    });
    
    cancelDeleteBlogBtn.addEventListener('click', function() {
        closeModal(deleteBlogModal);
    });
    
    // Close modals when clicking outside
    window.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal')) {
            closeModal(e.target);
        }
    });
    
    // Load users data
    function loadUsers() {
        usersLoading.style.display = 'block';
        noUsersMessage.style.display = 'none';
        
        fetch('../Backend/Php/admin_users.php?action=get_all')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayUsers(data.data.users);
                } else {
                    displayFlashMessage('error', data.message || 'Failed to load users');
                    noUsersMessage.style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Error loading users:', error);
                displayFlashMessage('error', 'Failed to connect to the server');
                noUsersMessage.style.display = 'block';
            })
            .finally(() => {
                usersLoading.style.display = 'none';
            });
    }
    
    // Display users in the table
    function displayUsers(users) {
        userTableBody.innerHTML = '';
        
        if (!users || users.length === 0) {
            noUsersMessage.style.display = 'block';
            return;
        }
        
        users.forEach(user => {
            const row = document.createElement('tr');
            
            // Format date
            const date = new Date(user.created_at);
            const formattedDate = date.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
             
            // Create row
            row.innerHTML = `
                <td>${user.id.substring(0, 10)}...</td>
                <td>${user.firstname} ${user.lastname}</td>
                <td>${user.username}</td>
                <td>${user.email}</td>
                <td>${user.expertise}</td>
                <td>${formattedDate}</td>
                <td>
                    <div class="action-buttons">
                        <button class="view-btn" data-id="${user.id}">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="delete-btn" data-id="${user.id}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            `;
            
            userTableBody.appendChild(row);
        });
        
        // Add event listeners to buttons
        document.querySelectorAll('#user-table-body .view-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const userId = this.getAttribute('data-id');
                window.location.href = `profile.html?id=${userId}`;
            });
        });
        
        document.querySelectorAll('#user-table-body .edit-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const userId = this.getAttribute('data-id');
                loadUserForEdit(userId);
            });
        });
        
        document.querySelectorAll('#user-table-body .delete-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const userId = this.getAttribute('data-id');
                openDeleteUserModal(userId);
            });
        });
    }
    
    // Delete user
    let userToDelete = null;
    
    function openDeleteUserModal(userId) {
        userToDelete = userId;
        openModal(deleteUserModal);
    }
    
    function deleteUser(userId) {
        const formData = new FormData();
        formData.append('id', userId);
        
        fetch('../Backend/Php/admin_users.php?action=delete', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayFlashMessage('success', 'Agent deleted successfully');
                loadUsers(); // Reload user list
            } else {
                displayFlashMessage('error', data.message || 'Failed to delete agent');
            }
        })
        .catch(error => {
            console.error('Error deleting user:', error);
            displayFlashMessage('error', 'Failed to connect to the server');
        });
    }
    
    // Confirm delete user
    confirmDeleteBtn.addEventListener('click', function() {
        if (userToDelete) {
            deleteUser(userToDelete);
            closeModal(deleteUserModal);
            userToDelete = null;
        }
    });
    
    // Load blogs data
    function loadBlogs() {
        blogsLoading.style.display = 'block';
        noBlogsMessage.style.display = 'none';
        
        fetch('../Backend/Php/admin_blogs.php?action=get_all')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayBlogs(data.data.blogs);
                } else {
                    displayFlashMessage('error', data.message || 'Failed to load blogs');
                    noBlogsMessage.style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Error loading blogs:', error);
                displayFlashMessage('error', 'Failed to connect to the server');
                noBlogsMessage.style.display = 'block';
            })
            .finally(() => {
                blogsLoading.style.display = 'none';
            });
    }
    
    // Display blogs in the table
    function displayBlogs(blogs) {
        blogsTableBody.innerHTML = '';
        
        if (!blogs || blogs.length === 0) {
            noBlogsMessage.style.display = 'block';
            return;
        }
        
        blogs.forEach(blog => {
            const row = document.createElement('tr');
            
            // Format date
            const date = new Date(blog.createdAt);
            const formattedDate = date.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
            
            // Featured image path
            let featuredImage = blog.featuredImage;
            if (!featuredImage) {
                featuredImage = '../assets/images/default-blog-image.jpg';
            } else if (featuredImage.indexOf('/') !== 0 && featuredImage.indexOf('http') !== 0) {
                featuredImage = '../' + featuredImage;
            }
            
            // Format category
            const category = blog.category.replace(/-/g, ' ');
            const categoryFormatted = category.charAt(0).toUpperCase() + category.slice(1);
            
            // Create row
            row.innerHTML = `
                <td>${blog.id.substring(0, 10)}...</td>
               
                <td>${blog.title}</td>
                <td>${categoryFormatted}</td>
                <td>${blog.author || 'Unknown'}</td>
                <td>${formattedDate}</td>
                <td><span class="status-badge status-published">${blog.status || 'Published'}</span></td>
                <td>
                    <div class="action-buttons">
                        <a href="blog/post.html?id=${blog.id}" class="view-btn">
                            <i class="fas fa-eye"></i>
                        </a>
                        <button class="delete-btn" data-id="${blog.id}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            `;
            
            blogsTableBody.appendChild(row);
        });
        
        // Add event listeners to delete buttons
        document.querySelectorAll('#blogs-table-body .delete-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const blogId = this.getAttribute('data-id');
                openDeleteBlogModal(blogId);
            });
        });
    }
    
    // Delete blog
    function deleteBlog(blogId) {
        const formData = new FormData();
        formData.append('id', blogId);
        
        fetch('../Backend/Php/admin_blogs.php?action=delete', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayFlashMessage('success', 'Case file deleted successfully');
                loadBlogs(); // Reload blog list
            } else {
                displayFlashMessage('error', data.message || 'Failed to delete case file');
            }
        })
        .catch(error => {
            console.error('Error deleting blog:', error);
            displayFlashMessage('error', 'Failed to connect to the server');
        });
    }
    
    // Delete blog confirmation
    let blogToDelete = null;
    
    function openDeleteBlogModal(blogId) {
        blogToDelete = blogId;
        document.getElementById('delete-blog-id').value = blogId;
        openModal(deleteBlogModal);
    }
    
    confirmDeleteBlogBtn.addEventListener('click', function() {
        if (blogToDelete) {
            deleteBlog(blogToDelete);
            closeModal(deleteBlogModal);
            blogToDelete = null;
        }
    });
    
    cancelDeleteBlogBtn.addEventListener('click', function() {
        closeModal(deleteBlogModal);
        blogToDelete = null;
    });
    
    // Load blogs on page load
    loadBlogs();
    
    // Load users on page load
    loadUsers();
    
    // Flash messages
    function displayFlashMessage(type, message, autoHide = true) {
        const flashContainer = document.querySelector('.flash-messages');
        
        const messageElement = document.createElement('div');
        messageElement.className = `flash-message flash-${type}`;
        
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
        
        const closeButton = messageElement.querySelector('.flash-close');
        closeButton.addEventListener('click', function() {
            messageElement.style.opacity = '0';
            setTimeout(() => {
                messageElement.remove();
            }, 300);
        });
        
        if (autoHide) {
            setTimeout(function() {
                messageElement.style.opacity = '0';
                setTimeout(() => {
                    messageElement.remove();
                }, 300);
            }, 5000);
        }
        
        return messageElement;
    }
});