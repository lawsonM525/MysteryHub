//////  Hangman Game Logic
document.addEventListener('DOMContentLoaded', function() {
    
    /// VARIABLES
    const canvas = document.getElementById('canvas');
    const ctx = canvas.getContext('2d');
    const wordDisplay = document.getElementById('wordDisplay');
    const keyboard = document.getElementById('keyboard');
    const gameStatus = document.getElementById('gameStatus');
    const hintBtn = document.getElementById('hintBtn');
    const newGameBtn = document.getElementById('newGameBtn');
    const clueBoxes = document.querySelectorAll('.clue-box');
    
    let words = ['JACKSON', 'JAMESON', 'JENKINS', 'JOHNSON', 'JACOBSON'];
    let selectedWord = '';
    let guessedLetters = [];
    let wrongGuesses = 0;
    let maxWrongGuesses = 6;
    let currentClueIndex = 0;
    let gameOver = false;
    
    // initialize the game
    function initGame() {
        selectedWord = words[Math.floor(Math.random() * words.length)];
        guessedLetters = [];
        wrongGuesses = 0;
        gameOver = false;
        
        // Hide all clue boxes
        clueBoxes.forEach(box => {
            box.classList.remove('visible');
        });
        currentClueIndex = 0;
        
        // Create word display
        createWordDisplay();
        
        // Create keyboard
        createKeyboard();
        
        // Draw empty gallows
        drawHangman();
        
        gameStatus.textContent = 'Start guessing letters to identify the suspect...';
        gameStatus.style.color = 'var(--ink-color)';
    }
    
    // Create word display with HTML letterbox
    function createWordDisplay() {
        wordDisplay.innerHTML = '';
        for (let i = 0; i < selectedWord.length; i++) {
            const letterBox = document.createElement('div');
            letterBox.classList.add('letter-box');
            letterBox.textContent = guessedLetters.includes(selectedWord[i]) ? selectedWord[i] : '';
            wordDisplay.appendChild(letterBox);
        }
    }
    
    // Create keyboard with HTML buttons
    function createKeyboard() {
        keyboard.innerHTML = '';
        for (let i = 65; i <= 90; i++) {
            const letter = String.fromCharCode(i);
            const button = document.createElement('button');
            button.classList.add('keyboard-btn');
            button.textContent = letter;
            
            if (guessedLetters.includes(letter)) {
                button.classList.add('disabled');
                button.disabled = true;
            }
            
            button.addEventListener('click', function() {
                if (!gameOver && !guessedLetters.includes(letter)) {
                    guessLetter(letter);
                    button.classList.add('disabled');
                    button.disabled = true;
                }
            });
            
            keyboard.appendChild(button);
        }
    }
    
    // Draw hangman on canvas
    function drawHangman() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.lineWidth = 3;
        ctx.strokeStyle = '#333';
        ctx.beginPath();
        
        // Draw gallows
        ctx.moveTo(50, 270);
        ctx.lineTo(250, 270);
        ctx.moveTo(100, 270);
        ctx.lineTo(100, 50);
        ctx.lineTo(200, 50);
        ctx.lineTo(200, 80);
        ctx.stroke();
        
        // Draw hangman parts based on wrong guesses
        if (wrongGuesses >= 1) {
            // Head
            ctx.beginPath();
            ctx.arc(200, 110, 30, 0, Math.PI * 2);
            ctx.stroke();
        }
        
        if (wrongGuesses >= 2) {
            // Body
            ctx.beginPath();
            ctx.moveTo(200, 140);
            ctx.lineTo(200, 210);
            ctx.stroke();
        }
        
        if (wrongGuesses >= 3) {
            // Left arm
            ctx.beginPath();
            ctx.moveTo(200, 160);
            ctx.lineTo(150, 180);
            ctx.stroke();
        }
        
        if (wrongGuesses >= 4) {
            // Right arm
            ctx.beginPath();
            ctx.moveTo(200, 160);
            ctx.lineTo(250, 180);
            ctx.stroke();
        }
        
        if (wrongGuesses >= 5) {
            // Left leg
            ctx.beginPath();
            ctx.moveTo(200, 210);
            ctx.lineTo(150, 250);
            ctx.stroke();
        }
        
        if (wrongGuesses >= 6) {
            // Right leg
            ctx.beginPath();
            ctx.moveTo(200, 210);
            ctx.lineTo(250, 250);
            ctx.stroke();
            
            // Draw X's for eyes
            ctx.beginPath();
            ctx.moveTo(190, 100);
            ctx.lineTo(180, 110);
            ctx.moveTo(180, 100);
            ctx.lineTo(190, 110);
            
            ctx.moveTo(220, 100);
            ctx.lineTo(210, 110);
            ctx.moveTo(210, 100);
            ctx.lineTo(220, 110);
            ctx.stroke();
        }
    }
    
    // Guess letter
    function guessLetter(letter) {
        guessedLetters.push(letter);
        
        if (selectedWord.includes(letter)) {
            // Correct guess
            createWordDisplay();
            
            // Check if word is complete
            const isWordComplete = selectedWord.split('').every(char => guessedLetters.includes(char));
            
            if (isWordComplete) {
                gameOver = true;
                gameStatus.textContent = `Case solved! The killer is ${selectedWord}. Arrest warrant issued.`;
                gameStatus.style.color = 'green';
                
                // Show all clues
                clueBoxes.forEach(box => {
                    box.classList.add('visible');
                });
            }
        } else {
            // Incorrect guess
            wrongGuesses++;
            drawHangman();
            showNextClue();
            
            if (wrongGuesses >= maxWrongGuesses) {
                gameOver = true;
                gameStatus.textContent = `Investigation failed. The killer was ${selectedWord}.`;
                gameStatus.style.color = 'var(--stamp-red)';
                
                // Show all clues
                clueBoxes.forEach(box => {
                    box.classList.add('visible');
                });
            } else {
                gameStatus.textContent = `Wrong guess! ${maxWrongGuesses - wrongGuesses} attempts remaining.`;
            }
        }
    }
    
    // Show next clue
    function showNextClue() {
        if (currentClueIndex < clueBoxes.length) {
            clueBoxes[currentClueIndex].classList.add('visible');
            currentClueIndex++;
        }
    }
    
    // Event listeners
    hintBtn.addEventListener('click', function() {
        if (!gameOver && currentClueIndex < clueBoxes.length) {
            showNextClue();
        }
    });
    
    newGameBtn.addEventListener('click', function() {
        initGame();
    });
    
    // Start the game
    initGame();
});