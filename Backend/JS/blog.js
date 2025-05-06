document.addEventListener('DOMContentLoaded', function() {
    // Fetch posts from the backend
    fetch('../../Backend/Php/get_stories.php')
        .then(response => response.json())
        .then(data => {
            console.log("Fetched posts:", data);
            const blogGrid = document.getElementById('blog-grid');
            

            const baseImagePath = '../../assets/uploads/';

            // Clear any existing content
            blogGrid.innerHTML = '';
            
            // Add each post to the grid
            data.forEach(post => {
                const postCard = `
                    <div class="blog-card">
                        <div class="case-number">CASE #MH-${post.id}</div>
                        <div class="paperclip"></div>
                        <div class="blog-card-image">
                            <img src="${baseImagePath + post.featuredImage}" alt="${post.title}">
                        </div>
                        <div class="blog-card-content">
                            <div class="blog-card-category">${post.category}</div>
                            <h3 class="blog-card-title">${post.title}</h3>
                            <p class="blog-card-excerpt">${post.excerpt}</p>
                            <div class="blog-card-meta">
                                <div class="blog-card-date">
                                    <i class="far fa-calendar-alt"></i> ${new Date(post.createdAt).toLocaleDateString()}
                                </div>
                                <div class="blog-card-author">
                                    <i class="fas fa-user-secret"></i> ${post.author}
                                </div>
                            </div>
                            <a href="post.html?id=${post.id}" class="read-more">VIEW FULL FILE</a>
                        </div>
                    </div>
                `;
                blogGrid.insertAdjacentHTML('beforeend', postCard);
            });
        })
        .catch(error => {
            console.error('Error fetching posts:', error);
            const blogGrid = document.getElementById('blog-grid');
            blogGrid.innerHTML = `
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <p>Error loading case files. Please try again later.</p>
                </div>
            `;
        });
        
    // Category filter functionality
    const categoryTags = document.querySelectorAll('.category-tag');
    categoryTags.forEach(tag => {
        tag.addEventListener('click', function() {
            // Remove active class from all tags
            categoryTags.forEach(t => t.classList.remove('active'));
            // Add active class to clicked tag
            this.classList.add('active');
            
            const category = this.textContent.trim();
            filterPosts(category);
        });
    });
    
    function filterPosts(category) {
        const categoryMap = {
            'Unsolved Cases': 'unsolved-mysteries',
            'Cold Cases': 'true-crime',
            'Serial Killers': 'serial-killers',
            'Historical Mysteries': 'historical-mysteries',
            'Conspiracy': 'conspiracies',
            'Paranormal': 'paranormal'
        };
        
        const blogCards = document.querySelectorAll('.blog-card');
        blogCards.forEach(card => {
            const cardCategory = card.querySelector('.blog-card-category').textContent;
            if (category === 'All Files' || cardCategory === categoryMap[category]) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }
    
    // Search functionality
    const searchInput = document.querySelector('.blog-search input');
    const searchButton = document.querySelector('.blog-search button');
    
    function performSearch() {
        const searchTerm = searchInput.value.toLowerCase();
        const blogCards = document.querySelectorAll('.blog-card');
        
        blogCards.forEach(card => {
            const title = card.querySelector('.blog-card-title').textContent.toLowerCase();
            const excerpt = card.querySelector('.blog-card-excerpt').textContent.toLowerCase();
            const category = card.querySelector('.blog-card-category').textContent.toLowerCase();
            
            if (title.includes(searchTerm) || excerpt.includes(searchTerm) || category.includes(searchTerm)) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }
    
    searchButton.addEventListener('click', performSearch);
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            performSearch();
        }
    });
});