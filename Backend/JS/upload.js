document.addEventListener('DOMContentLoaded', function() {
    // Image upload functionality
    const imageUploadArea = document.getElementById('image-upload-area');
    const imageInput = document.getElementById('image-input');
    const imagePreview = document.getElementById('image-preview');
    const previewImg = document.getElementById('preview-img');
    
    imageUploadArea.addEventListener('click', function() {
        imageInput.click();
    });
    
    imageUploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.style.borderColor = '#ff4d94';
        this.style.backgroundColor = 'rgba(255, 77, 148, 0.05)';
    });
    
    imageUploadArea.addEventListener('dragleave', function() {
        this.style.borderColor = '#333';
        this.style.backgroundColor = 'transparent';
    });
    
    imageUploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        this.style.borderColor = '#333';
        this.style.backgroundColor = 'transparent';
        
        if (e.dataTransfer.files.length) {
            imageInput.files = e.dataTransfer.files;
            displayImagePreview();
        }
    });
    
    imageInput.addEventListener('change', displayImagePreview);
    
    function displayImagePreview() {
        if (imageInput.files && imageInput.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                imagePreview.style.display = 'block';
                imageUploadArea.style.display = 'none';
            }
            
            reader.readAsDataURL(imageInput.files[0]);
        }
    }
    
    // Tags functionality
    const tagsContainer = document.getElementById('tags-container');
    const tagInput = document.getElementById('tag-input');
    const postTagsInput = document.getElementById('post-tags');
    let tags = [];
    
    tagInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ',') {
            e.preventDefault();
            
            const tagText = this.value.trim();
            if (tagText && !tags.includes(tagText)) {
                addTag(tagText);
                this.value = '';
                updateHiddenInput();
            }
        }
    });
    
    function addTag(text) {
        tags.push(text);
        
        const tag = document.createElement('div');
        tag.className = 'tag';
        tag.innerHTML = text + '<i class="fas fa-times"></i>';
        
        tag.querySelector('i').addEventListener('click', function() {
            tag.remove();
            tags = tags.filter(t => t !== text);
            updateHiddenInput();
        });
        
        tagsContainer.insertBefore(tag, tagInput);
    }
    
    function updateHiddenInput() {
        postTagsInput.value = JSON.stringify(tags);
    }
    
    // Editor toolbar functionality (simplified demo)
    const editorButtons = document.querySelectorAll('.editor-btn');
    const contentTextarea = document.getElementById('post-content');
    
    editorButtons.forEach(button => {
        button.addEventListener('click', function() {
            // This is a simplified demo. In a real app, this would implement formatting
            const action = this.getAttribute('title').toLowerCase();
            alert(`${action} formatting would be applied in a real rich text editor.`);
        });
    });
    
    // Form submission
    const uploadForm = document.getElementById('upload-form');
    
    uploadForm.addEventListener('submit', function(e) {
        e.preventDefault(); 

        const title = document.getElementById('post-title').value;
        const category = document.getElementById('post-category').value;
        const readTime = document.getElementById('read-time').value;
        const excerpt = document.getElementById('post-excerpt').value;
        const content = document.getElementById('post-content').value;
        const imageInput = document.getElementById('image-input');
        const postSources = document.getElementById('post-sources'); 
        const postTagsInput = document.getElementById('post-tags');  
        const tags = postTagsInput.value; 

        if (!title || !category || !readTime || !excerpt || !content) {
            alert('Please fill in all required fields.');
            return; 
        }
        
        if (editId) {
            // ✏️ Editing an existing blog
            const formData = new FormData();
            formData.append('id', editId);
            formData.append('title', title);
            formData.append('category', category);
            formData.append('readTime', readTime);
            formData.append('excerpt', excerpt);
            formData.append('content', content);
            formData.append('tags', tags);
            formData.append('sources', postSources.value);

            if (imageInput.files.length > 0) {
                formData.append('image', imageInput.files[0]);
            }

            fetch(`../../Backend/Php/update_stories.php?id=${editId}`, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Story updated successfully!');
                    window.location.href = 'blog.html';
                } else {
                    alert('Failed to update story. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error updating story:', error);
                alert('An error occurred while updating the story. Please try again later.');
            });
        } else {
           
            uploadForm.submit(); 
        }
    });

    
    // Save as draft
    const saveDraftBtn = document.getElementById('save-draft-btn');
    
    saveDraftBtn.addEventListener('click', function() {
        // For draft functionality, we would need to modify the form action
        // and add a status field to indicate it's a draft
        alert('Story saved as draft! You can access it from your profile page.');
    });

    // Edit a story functionality
    const urlParams = new URLSearchParams(window.location.search);
    const editId = urlParams.get('edit');
    console.log('Editing story with ID:', editId);

    if(editId) {
        fetch(`../../Backend/Php/get_stories.php?id=${editId}`)
        .then(response => response.json())
        .then(data => {
            const blog = Array.isArray(data) ? data[0] : data;

            console.log('Loaded blog:', blog);

            let existingTags = [];

            if (typeof blog.tags === 'string') {
                try {
                    existingTags = JSON.parse(blog.tags); //convert the string into a javascript array 
                } catch (e) {
                    console.error('Error parsing tags:', e);
                }
            }

            existingTags.forEach(tag => addTag(tag));
            updateHiddenInput();

            

            document.getElementById('post-title').value = blog.title;
            document.getElementById('post-category').value = blog.category;
            document.getElementById('read-time').value = blog.readTime;
            document.getElementById('post-excerpt').value = blog.excerpt;
            document.getElementById('post-content').value = blog.content;
            document.getElementById('post-sources').value = blog.sources;    
            
            console.log('Form fields prefilled!');
        })
        .catch(error => {
            console.error('Error loading story:', error);
        });
    }
});