document.addEventListener('DOMContentLoaded', function() {
    // Get post ID from URL
    const urlParams = new URLSearchParams(window.location.search);
    const postId = urlParams.get('id');
    console.log('Post ID:', postId);
    if (!postId) {
        window.location.href = 'blog.html';
        return;
    }

    // Fetch post data
    fetch(`../../Backend/Php/get_stories.php?id=${postId}`)
        .then(response => response.json())
        .then(posts => {
            // Hide loading spinner
            const post = Array.isArray(posts) ? posts[0] : posts
            console.log('Post:', post);
            console.log('Post ID:', post.id);
            console.log('Author Avatar:', post.authorAvatar);
            document.getElementById('loading-spinner').style.display = 'none';
            

            const baseImagePath = '../../Assets/uploads/';
            
            // Show post content
            const postContent = document.getElementById('post-content');
            postContent.style.display = 'block';
            
            // Update page title
            document.title = `${post.title} - Mystery Hub`;
            console.log('Post author:', post.author);
            
            // Generate post HTML
            postContent.innerHTML = `
                <div class="post-header">
                    <h1 class="post-title">${post.title}</h1>
                    <span class="post-category"> Category: ${post.category}</span>
                    <div class="post-meta">
                        <div class="post-date">
                            <i class="far fa-calendar"></i> ${new Date(post.createdAt).toLocaleDateString()}
                        </div>
                     
                    </div>
                </div>
                
                <div class="post-featured-image">
                    <img src="${baseImagePath + post.featuredImage}" alt="${post.title}">
                </div>
                
                <div class="post-content">
                    ${post.content}
                </div>
                
                <div class="post-actions">
                    <div class="post-actions-left">
                        <button class="like-btn" id="like-btn" data-post-id="${post.id}">
                            <i class="far fa-heart"></i> <span id="like-count">${post.likes || 0}</span> Likes
                        </button>
                        <button class="save-btn" id="save-btn" data-post-id="${post.id}">
                            <i class="far fa-bookmark"></i> Save
                        </button>
                    </div> 
                </div>
                
                <div class="post-author-bio">
                    <div class="author-avatar-large">
                        <img src="${post.authorAvatar || '../../assets/images/default-avatar.jpg'}" alt="Author" onerror="this.onerror=null; this.src='../../assets/images/default-avatar.jpg';">
                    </div>
                    <div class="author-info">
                        <h3 class="author-name">${post.author}</h3>
                        <p class="author-bio">Mystery Hub Contributor</p>
                    </div>
                </div>
                
                <div class="comments-section">
                    <h2 class="section-title">Comments (${post.commentCount || 0})</h2>
                    
                    <div class="comment-form">
                        <textarea class="comment-input" placeholder="Share your thoughts..."></textarea>
                        <button class="comment-submit" id="comment-submit">Post Comment</button>
                    </div>
                    
                    <div class="comments-list" id="comments-list">
                        <!-- Comments will be loaded here -->
                    </div>
                </div>
            `;
            const commentSubmitBtn = document.getElementById('comment-submit');
            const commentInput = document.querySelector('.comment-input');
            commentSubmitBtn.addEventListener('click', function() {
                console.log('Comment Submit Button Clicked');
                const commentText = commentInput.value.trim();
                console.log('Comment Text:', commentText);
                if (commentText) {
                    fetch(`../../Backend/Php/add_comment.php`, {
                        method: 'POST', 
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({
                            postId: postId,
                            content: commentText,
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log('Data:', data);
                        if (data.success) {
                            commentInput.value = '';
                            loadComments(postId);
                        } else {
                            alert('Failed to add comment. Please try again.');
                        }
                    })
                    .catch(error => {
                        console.error('Error adding comment:', error);
                        alert('An error occurred while adding your comment. Please try again later.');
                    })
                }
            });
            // like post
            document.getElementById('like-btn').addEventListener('click', function() {
                fetch('../../Backend/Php/like_post.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ postId: post.id })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update the number displayed
                        const likeCountSpan = document.getElementById('like-count');
                        likeCountSpan.textContent = parseInt(likeCountSpan.textContent) + 1;
                    } else {
                        alert('Failed to like the post. Please try again.');
                    }
                })
                .catch(error => {
                    console.error('Error liking post:', error);
                });
            });
            
            // save post to bookmarks
            document.getElementById('save-btn').addEventListener('click', function() {
                const saveBtn = this;
                const savedIcon = '<i class="fas fa-bookmark"></i> Saved';
                const unsavedIcon = '<i class="far fa-bookmark"></i> Save';
                
                fetch('../../Backend/Php/save_post.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ postId: post.id })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update the button to show saved state
                        saveBtn.innerHTML = savedIcon;
                        saveBtn.classList.add('saved');
                        
                        // Show a brief confirmation message
                        const message = data.alreadySaved ? 'Already in your bookmarks!' : 'Added to your bookmarks!';
                        alert(message);
                    } else {
                        if (data.message === 'User not logged in') {
                            alert('Please log in to bookmark posts');
                            window.location.href = '../../Pages/login.html';
                        } else {
                            alert('Failed to save the post. Please try again.');
                        }
                    }
                })
                .catch(error => {
                    console.error('Error saving post:', error);
                    alert('An error occurred. Please try again later.');
                });
            });
            
            // load comments
            loadComments(postId);
        })
        .catch(error => {
            console.error('Error loading post:', error);
            document.getElementById('loading-spinner').innerHTML = `
                <i class="fas fa-exclamation-circle"></i>
                <p>Error loading case file. Please try again later.</p>
            `;
        });
    });

    // function to load comments
    function loadComments(postId) {
        fetch(`../../Backend/Php/get_comments.php?postId=${postId}`)
        .then(response => response.json())
        .then(comments => {
            const commentsList = document.getElementById('comments-list');
            commentsList.innerHTML = comments.map(comment => `
                <div class="comment">
                    <div class="comment-header">
                        <img src="${comment.userAvatar || '../../assets/images/default-avatar.jpg'}" alt="${comment.username}">
                        <h4>${comment.username}</h4>
                    </div>
                    <div class="comment-content">
                        <div class="comment-text">${comment.content}</div>
                    </div>
                    <div class="comment-date">
                        <i class="far fa-clock"></i> ${new Date(comment.createdAt).toLocaleDateString()}
                    </div>
                </div>  
            `).join('');
            })
            .catch(error => {
                console.error('Error loading comments:', error);
            })
    }