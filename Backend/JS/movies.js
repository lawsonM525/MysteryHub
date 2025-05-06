document.addEventListener('DOMContentLoaded', function() {
    let slides = [];
    let dots = [];
    let currentSlide = 0;
    const slideshowContent = document.getElementById('slideshow-content');
    const slideshowDots = document.getElementById('slideshow-dots');
    const prevArrow = document.querySelector('.prev-arrow');
    const nextArrow = document.querySelector('.next-arrow');
    
    // Fetch movies data from the server
    fetch('../../Backend/Php/get_movies.php')
        .then(response => response.json())
        .then(result => {
            if (result.success && result.data.length > 0) {
                // Clear loading placeholder
                slideshowContent.innerHTML = '';
                slideshowDots.innerHTML = '';
                
                // Generate slides and dots for each movie
                result.data.forEach((movie, index) => {
                    // Create slide
                    const slideHTML = `
                        <div class="slideshow-slide ${index === 0 ? 'active' : ''}" data-index="${index}" 
                             style="background-image: url('../../assets/images/${movie.image}')">
                            <div class="movie-info-overlay">
                                <div class="movie-title-overlay">${movie.title}</div>
                                <div class="movie-year-overlay">Released: ${movie.year}</div>
                                <div class="slide-caption-overlay">${movie.description}</div>
                            </div>
                        </div>
                    `;
                    slideshowContent.insertAdjacentHTML('beforeend', slideHTML);
                    
                    // Create dot
                    const dotHTML = `<div class="slideshow-dot ${index === 0 ? 'active' : ''}" data-index="${index}"></div>`;
                    slideshowDots.insertAdjacentHTML('beforeend', dotHTML);
                });
                
                // Update slides and dots after DOM is updated
                slides = document.querySelectorAll('.slideshow-slide');
                dots = document.querySelectorAll('.slideshow-dot');
                
                // Add click event to dots
                dots.forEach(dot => {
                    dot.addEventListener('click', function() {
                        const index = parseInt(this.getAttribute('data-index'));
                        showSlide(index);
                    });
                });
                
                // Initialize slideshow controls
                prevArrow.addEventListener('click', prevSlide);
                nextArrow.addEventListener('click', nextSlide);
            } else {
                // Show error if no movies found
                slideshowContent.innerHTML = `
                    <div class="error-message">
                        <i class="fas fa-exclamation-triangle"></i>
                        <p>Error loading surveillance footage. File may be classified or missing.</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error fetching movies:', error);
            slideshowContent.innerHTML = `
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>Error loading surveillance footage. Connection may be compromised.</p>
                </div>
            `;
        });
    
    function showSlide(index) {
        if (index < 0) index = slides.length - 1;
        if (index >= slides.length) index = 0;
        
        slides.forEach(slide => slide.classList.remove('active'));
        dots.forEach(dot => dot.classList.remove('active'));
        
        slides[index].classList.add('active');
        dots[index].classList.add('active');
        currentSlide = index;
    }
    
    function nextSlide() {
        showSlide(currentSlide + 1);
    }
    
    function prevSlide() {
        showSlide(currentSlide - 1);
    }
    
    // Auto slideshow
    setInterval(nextSlide, 7000);
});