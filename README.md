# MysteryHub

MysteryHub is a web application that contains interactive games, real-life cold case blogs, and suggestions for mystery films to give mystery fans a space to explore the worl of mystery!!! 

## 🕵️ Project Overview

This project was built using **JavaScript, HTML, CSS** for the frontend and **PHP** for the backend. Instead of a traditional database, we used **JSON files** to manage and store data in the application.

The decision to use JSON files rather than a traditional database was made to focus on core web development concepts without the added complexity of database management systems. This approach allowed us to concentrate on creating a good user experience while still having reliable data storage.

## 🎯 Features

- **Mini Mystery Games**: Interactive detective games where players can solve mysteries through clues and deduction.
-  **True Crime & Cold Case Blog**: Users can read, comment on, save, and upload real mystery stories.
- **Mystery Movie Recommendations**: A slideshow-style feature where users can browse movie recommendations.
- **User Interactions**:
	- Save/favorite blog stories for later reading
	- Comment and like under blog posts to engage with other mystery enthusiasts
	- Tooltip that gives user information on how to play the game
	- Play hangman with virtual hints and clues for an immersive mystery experience
- **User Authentication System**:
    - Secure registration and login functionality
    - Profile management with customizable details
    - Profile picture upload and management
    - Secure logout functionality with session management
- **Admin Controls**:
    - Special admin panel for content and user management
    - User oversight capabilities
    - Content moderation tools

## 🔍 How the Project Works

Users can register and log in to access the site. Once logged in, they can play mystery games, read and interact with blog posts, and explore movie recommendations. All user actions, like posting a story, liking, commenting, or updating their profile, are handled through forms and buttons that use JavaScript on the frontend and PHP on the backend. 

Data is stored in JSON files, with separate files for:
- User accounts and profile information
- Blog posts and comments
- User preferences and saved content

Admins have access to a special page where they can manage users and blog content, ensuring the platform remains appropriate and engaging.

The application implements a role-based access control system, where regular users have standard privileges while administrators have expanded capabilities for content and user management.

## 🧠 Team Contributions

### Bintu Jalloh 
- Site Design Planning and Brainstorming 
- Initial backend development [Php setup, register and login functionality, initial json data setup]
- Implemented the Blog feature where users can write, update, save, like, and comment on blog posts.
- Built the Register and Profile features, allowing users to sign up, upload a profile picture, write a bio, and update their information.
- Set up backend PHP scripts to handle user authentication, form validation, add comments, create/update blog posts.
- Implemented proper file path handling for uploaded images and content

### Michelle Lawson
- Site Design Planning and Brainstorming 
- Initial frontend Development [html pages and css]
- Login functionality with session management
- Game 1: Hangman with graphics and JavaScript implementation
- Movies slideshow functionality with dynamic content loading
- Fixed bugs in blog update functionality for improved reliability
- Added functionality that allows a user to see their favorited articles displayed in their profile
- Designed and implemented the vintage detective aesthetic throughout the site

## 🛠️ Technologies Used

- **HTML5**: For structured content and semantic markup
- **CSS3**: For styling and responsive design, including custom animations and transitions
- **JavaScript (Vanilla)**: For client-side interactivity and dynamic content
- **PHP 7+**: For server-side processing and session management
- **JSON**: As a database substitute for flexible data storage and retrieval
- **File System Management**: For handling user-uploaded content and profile pictures
- **Session Management**: For maintaining user state across the application

## 🧪 Notes & Challenges

Since we used JSON files instead of a real database, we had to handle things like generating IDs and keeping the data consistent ourselves. This required custom implementation of:
- Unique ID generation for users, blog posts, and comments
- Data integrity checks to prevent corruption or conflicts
- Custom query-like functionality for searching and filtering data

We had to figure out a way to use save the user's information temporally through session data, balancing security with user experience and persistent login states.

Using PHP was quite difficult because getting real-time updates without full page refresh was a big learning moment for us. We implemented techniques to:
- Use AJAX for data updates without page refreshes
- Structure backend responses for JSON consumption by the frontend
- Manage asynchronous updates to maintain data consistency

We also had to be really intentional about how we named things and organized routes so the frontend and PHP backend could communicate smoothly. This involved:
- Consistent naming conventions across files and functions
- Structured API-like endpoints for frontend-backend communication
- Thoughtful organization of files and directories

Getting the file paths right was a bit tricky, especially when it came to making sure image paths were correctly linked and displayed across different pages. We solved this by:
- Using both physical file paths for server operations and web-relative paths for client display
- Implementing path translation functions for consistent referencing
- Careful management of uploaded files and their associated references

## ✅ Future Improvements

- Add real authentication with session or JWT-based login for enhanced security
- Integrate local storage or a lightweight database (like SQLite) for improved data management
- Expand mystery game library with more levels and hints for greater user engagement
- Allow users to edit or delete their submitted stories for better content management
- Implement a rating system for movies and blogs for enhanced community features
- Add more interactive elements to increase user engagement
- Develop a notification system for users to track interactions with their content

# Requirement Checklist:

[✅] **Must be a full web app, with html, css, javascript, php.**
Focusing only on the html and css would not suffice.
- We implemented a complete application stack using HTML for structure, CSS for styling, JavaScript for client-side functionality, and PHP for server-side processing, and JSON files for data storage, creating a fully functioning web application.

[✅] **Must work with files, preferably json-formatted ones. You may NOT use databases for your project.**
- We added a data folder that contains all the json files. We have a users.json file that contains each user's info from when they register (ex: username, encrypted password, name, expertise, profile_pic, etc). There's a blogs.json file that includes a unique id of the blog post, title, category, content, readTime, comments, likes, etc). Additionally each user has a unique separate json file that contains information about what blogs, and movies that they've saved.
- We also have a profile_pics folder that contains all the profile pictures that users upload... and the paths to these profile pictures are stored in the users.json file.

[✅] **Must have a coherent theme**
- Theme: An online spot for all things mystery: from games to real mystery stories to movie recommendations.  
- This theme is consistently applied throughout the application as we made sure to use detective-themed terminology, vintage aesthetic elements, and mystery-focused content.

[✅] **Must have users (with Register and Login menu options, and an Admin site available only to the system administrator).**
- We created a comprehensive user authentication system with registration, login, and profile management
- We also created an admin page that only the system administrator can access. From there, the admin can see all registered users and their blog posts, and they have the ability to delete users or remove blog posts if needed.
- The admin authentication is handled securely through session management with special privilege flags.

[✅] **Must store data for each user (could be files the user uploaded, comments the user has made, personal information about the user, etc) and each user must be able to manage/edit their data in some way.**
- Users can update their profile dynamically including personal information, biography, and profile pictures
- User-generated content like comments, liked posts, and saved articles are all stored in json files and manageable/editable
- All data is persistently stored in JSON files with proper data structure and relationships

[✅] **Must be dynamic: pages must change via interactivity with the user or when changes happen on the server.**
- The site features numerous interactive elements that update content once it is changed, eg Updating user profile / adding a new blog post / liking a blog post / commenting on a blog post / saving a blog post / deleting a blog post / deleting a user
- We use JS for the dynamic content loading and modification
- Real-time display changes for user actions like liking, commenting, and saving content

[✅] **Must have parts that are constructed dynamically without hardwired limitations.**
- Blog posts, user profiles (the section that shows saved and created cases/posts), and the hangman game are all dynamically generated  
- Content displays, especially in the Case Files page, adjust based on available data rather than fixed layouts
- Adaptive interfaces that respond to user actions and content changes

[✅] **Must include a feature (e.g from W3schools) or optional topic in Web programming that you researched on your own, understood it and integrated it.**
- We included a real time clock built using JavaScript, here is the resourced we used: https://www.youtube.com/watch?v=-v36WcbDs9k 
- We implemented this feature by understanding the JavaScript Date object and DOM manipulation techniques

[✅] **Must include some original code in javascript and php, or code that was adapted constructively. You must acknowledge all code that you borrowed or adapted from other sources.**
- All core functionality was written from scratch, with inspiration from standard web development practices
- External resources were properly cited and adapted to fit our specific needs

[✅] **Must have some kind of Javascript graphics (besides included images).**
- We used javascript canvas to create the hangman game
- Custom animations and transitions enhance the user experience
- Dynamic visual feedback for user interactions

[✅] **Must have some interactive features (buttons, forms, etc.) which implement some local communication (on the client, in javascript, e.g. for changing the way something is visualized in the browser) and others that implement communication with the server (php, e.g. those that send to or request some info from the server).**
- We used JavaScript for things like buttons, modals, and dynamic content updates. For example, users updating their profile, updating a blog post, updating the UI in real time. 
- We used PHP to handle form submissions, login/register, saving blog posts, and interacting with JSON data on the server.
- The application features a balanced mix of client-side enhancements and server-side processing

[✅] **At most 2 students can work together on the same project. The project must have a documentation file that explains what your project is, how it works, how it satisfies each of the requirements, lists sources and how they were used as well as homeworks that were referenced, and explains who worked on the project and what the contribution of each person is, etc.**
- This README.md document serves as our comprehensive project documentation
- Contributions are clearly outlined in the "Team Contributions" section
- All requirements are detailed in the "Requirement Checklist" section

[✅] **Clean and well documented code, both in terms of organization of files (separate folders for css, js, php, organized in possibly several files with functions that are logically grouped together)**
- The project is organized into logical directories:
  - /Backend: Contains server-side logic
  - /Backend/PHP: PHP scripts for server operations
  - /Backend/JS: JavaScript files for client-side functionality
  - /Backend/Data: JSON files for data storage
  - /Pages: HTML templates and page structures
  - /Assets: CSS, images, and other static resources
- Code includes comments and follows consistent naming conventions

[✅] **Submit your current status of your project by the usual homework deadline.**
- Project was submitted on time with all core functionality implemented

[✅] **Your final version of your final project must work.**
- All implemented features are functional and work as expected
- The application has been thoroughly tested for usability and functionality

[✅] **Have a well crafted "story" that your web app is telling, i.e. a coherent theme or application domain.**
- The mystery/detective agency theme is consistently applied throughout the application
- User roles (agents), content types (case files), and functionality all align with the theme
- Visuals and terminology reinforce the immersive mystery experience

[✅] **Have a creative integration of the required technical requirements**
- Technical requirements were implemented in ways that enhance the mystery theme
- For example, the login system is presented as "agent authentication" and the admin area as a "command center"
- Technical implementations serve both functional and thematic purposes

[✅] **Have an aesthetically appealing look**
- The vintage detective aesthetic is visually appealing and cohesive
- Custom styling creates an immersive experience with aged paper textures, typewriter fonts, and agency-style elements
- Color scheme and visual hierarchy enhance usability while maintaining the theme

[✅] **Have a user-friendly user interface**
- Navigation is intuitive and consistent throughout the application
- Error messages and feedback are clear and helpful
- Forms include validation and user guidance
- Responsive design ensures usability across different devices

## 📌 Planned Features

- Users will be able to play **three fun mini mystery games**, each with animations and graphics for an immersive experience.
- Users will be able to read **real-life mystery stories**, cold cases, and true crime write-ups.
  - Comment on blog posts
  - Upload their own stories (as markdown files)
  - Favorite or save posts to their profile
- A **slideshow page** will showcase recommended mystery movies.
  - Rate movies (1 to 5 stars)
  - Save favorites
  - View average ratings
  - See rated movies on their profile
- **Register/Login system** with secure PHP sessions
- **Personal profile page**
  - View/edit personal info
  - View saved games, stories, and movies
  - View movie ratings they've given
- **Admin dashboard** (admin-only access)
  - View all registered users
  - Add new movies to the slideshow
  - Delete inappropriate blog posts or stories
  - Feature selected blog posts
- A **real-time clock** built using JavaScript

---

## 🌟 What We Actually Implemented

- One **Hangman-style mystery game** with custom graphics and gameplay logic
- Users can read **real-life mystery stories**, cold cases, and true crime write-ups.
  - Comment on blog posts with real-time updates
  - Upload their own stories (as markdown files) with formatting support
  - Save posts to their profile for later reading
  - Like blog posts to show appreciation for content
- A **slideshow page** showcasing recommended mystery movies with custom transitions
- **Register/Login system** using secure PHP sessions
  - Password hashing and secure authentication
  - Session management with proper security practices
  - Profile picture upload and management
  - Logout functionality with proper session destruction
- **Personal profile page**
  - View/edit personal info with real-time updates
  - View saved blog posts in a personalized interface
  - Custom styling to match the "agent dossier" theme
- **Admin dashboard**
  - View all registered users with detailed information
  - View user-submitted blog posts for moderation
  - Delete inappropriate stories/blogs to maintain community standards
  - Mark selected blog posts as "featured" to highlight quality content
- A **real-time clock** using JavaScript with custom styling that matches the theme

## ❗ Why We Didn't Implement All Planned Features

Although we had a strong vision for MysteryHub at the beginning, we quickly realized that some of the features we planned were more time-consuming than expected. 

We originally planned to include three mystery games, but after developing the first one, we realized how much time and detail went into designing the gameplay logic, writing the story, and handling the logic with JavaScript. For example, in our current game, we had to build a clue system where players could click on different objects in a room to gather hints and piece together what happened. All of this required careful planning and testing. Rather than rush through the others, we decided to focus on polishing one game to make it more complete and enjoyable. 

We were able to create the slideshow to display mystery movie recommendations, but we didn't have enough time to implement the rating system and saving functionality. These features would have required additional backend logic to store ratings per user and update averages, which we weren't able to complete before the deadline.

While we got the basic profile and blog-saving features working, we didn't have time to extend the profile functionality to include saved games or show movies a user rated. We couldn't add the option for Admins to add movies through the dashboard also.

Overall, we focused on building core features that would make the site usable, interactive, and visually complete. We prioritized getting a working blog system, game, and admin controls up and running. The main reason we couldn't add all the features we originally planned was due to time constraints and the extra logic required for each feature, along with the time needed to test and make sure everything worked properly. However, we're proud of how much we accomplished with the time we had.
