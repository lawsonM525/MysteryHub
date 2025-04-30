/**
 * Mystery Hub Real-Time Clock
 * Based on tutorial: https://www.youtube.com/watch?v=-v36WcbDs9k
 * 
 * This script provides a real-time clock that can be displayed
 * in the navbar or any other part of the website.
 * 
 * Created by: Bintu Jalloh & Michelle Lawson
 */

// Clock initialization function
function initClock(elementId) {
    // If no element ID is provided, use the default 'clock' ID
    const clockElement = document.getElementById(elementId || 'clock');
    
    if (!clockElement) {
        console.error(`Clock element with ID "${elementId || 'clock'}" not found.`);
        return;
    }
    
    // Set up the initial clock face
    setupClockFace(clockElement);
    
    // Start the clock
    updateClock(clockElement);
    
    // Update the clock every second
    setInterval(() => {
        updateClock(clockElement);
    }, 1000);
}

// Set up the initial clock face with HTML structure
function setupClockFace(element) {
    // Create the clock HTML structure
    element.classList.add('mystery-clock');
    element.innerHTML = `
        <div class="clock-container">
            <div class="time-display">
                <span class="hours">00</span>
                <span class="colon">:</span>
                <span class="minutes">00</span>
                <span class="colon">:</span>
                <span class="seconds">00</span>
                <span class="period">AM</span>
            </div>
            <div class="date-display">
                <span class="day-name">Day</span>,
                <span class="month">Month</span>
                <span class="day">00</span>,
                <span class="year">0000</span>
            </div>
        </div>
    `;
    
    // Add the necessary CSS styles
    addClockStyles();
}

// Add CSS styles for the clock
function addClockStyles() {
    // Only add styles if they haven't been added already
    if (!document.getElementById('clock-styles')) {
        const styleElement = document.createElement('style');
        styleElement.id = 'clock-styles';
        
        styleElement.textContent = `
            .mystery-clock {
                font-family: 'Courier New', monospace;
                color: #ff4d94;
                text-align: center;
            }
            
            .clock-container {
                background-color: rgba(255, 77, 148, 0.1);
                border-radius: 8px;
                padding: 10px 15px;
                display: inline-block;
                border: 1px solid rgba(255, 77, 148, 0.3);
            }
            
            .time-display {
                font-size: 1.5rem;
                font-weight: bold;
                margin-bottom: 2px;
            }
            
            .date-display {
                font-size: 0.9rem;
                color: rgba(255, 255, 255, 0.8);
            }
            
            .colon {
                animation: blink 1s infinite;
            }
            
            @keyframes blink {
                0%, 100% { opacity: 1; }
                50% { opacity: 0.5; }
            }
            
            /* Responsive adjustments */
            @media (max-width: 768px) {
                .mystery-clock {
                    font-size: 0.9rem;
                }
                
                .clock-container {
                    padding: 8px 12px;
                }
                
                .time-display {
                    font-size: 1.2rem;
                }
                
                .date-display {
                    font-size: 0.75rem;
                }
            }
        `;
        
        document.head.appendChild(styleElement);
    }
}

// Update the clock with the current time
function updateClock(element) {
    // Get the current date and time
    const now = new Date();
    
    // Time components
    let hours = now.getHours();
    const minutes = now.getMinutes();
    const seconds = now.getSeconds();
    
    // Date components
    const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    
    const dayName = days[now.getDay()];
    const monthName = months[now.getMonth()];
    const dayOfMonth = now.getDate();
    const year = now.getFullYear();
    
    // Determine the period (AM/PM)
    const period = hours >= 12 ? 'PM' : 'AM';
    
    // Convert to 12-hour format
    hours = hours % 12;
    hours = hours ? hours : 12; // 0 should be displayed as 12
    
    // Update the time display
    element.querySelector('.hours').textContent = hours.toString().padStart(2, '0');
    element.querySelector('.minutes').textContent = minutes.toString().padStart(2, '0');
    element.querySelector('.seconds').textContent = seconds.toString().padStart(2, '0');
    element.querySelector('.period').textContent = period;
    
    // Update the date display
    element.querySelector('.day-name').textContent = dayName;
    element.querySelector('.month').textContent = monthName;
    element.querySelector('.day').textContent = dayOfMonth;
    element.querySelector('.year').textContent = year;
}

// Function to add the clock to the navbar
function addClockToNavbar() {
    // Create a new list item for the clock
    const clockItem = document.createElement('li');
    clockItem.id = 'navbar-clock';
    clockItem.classList.add('nav-clock');
    
    // Find the navigation links container
    const navLinks = document.querySelector('.nav-links');
    
    if (navLinks) {
        // Insert the clock before the login/profile element
        navLinks.insertBefore(clockItem, navLinks.querySelector('.profile-pic') || navLinks.lastElementChild);
        
        // Initialize the clock
        initClock('navbar-clock');
        
        // Add specific styles for the navbar clock
        const styleElement = document.createElement('style');
        styleElement.textContent = `
            .nav-clock {
                margin-left: 15px;
            }
            
            .nav-clock .clock-container {
                padding: 5px 10px;
                background-color: rgba(255, 77, 148, 0.1);
            }
            
            .nav-clock .time-display {
                font-size: 1rem;
                margin-bottom: 0;
            }
            
            .nav-clock .date-display {
                display: none;
            }
            
            @media (max-width: 992px) {
                .nav-clock {
                    display: none;
                }
            }
        `;
        
        document.head.appendChild(styleElement);
    } else {
        console.error('Navigation links container not found.');
    }
}

// Check if the document is ready
document.addEventListener('DOMContentLoaded', function() {
    // Check if an element with ID 'clock' exists
    if (document.getElementById('clock')) {
        initClock('clock');
    }
    
    // Add the clock to the navbar if the setting is enabled
    if (window.MYSTERY_HUB_CONFIG && window.MYSTERY_HUB_CONFIG.navbarClock) {
        addClockToNavbar();
    }
});

// Export functions for use in other scripts
window.initClock = initClock;
window.addClockToNavbar = addClockToNavbar;
