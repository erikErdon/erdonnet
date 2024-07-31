<?php
session_start(); // Start the session

// Include the configuration file to define BASE_DIR
// Include the functions file for messaging
include './functions.php';

// Include the database connection file
include './db.php';

// Check if user is logged in, redirect to login page if not
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./styles/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Dashboard</title>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <button class="hamburger-menu" id="hamburger-menu">&#9776;</button>
        <h1>Dashboard</h1>
        <!-- Search Bar in Header -->
        <div class="search-container">
            <input type="text" id="search-bar" class="search-bar" placeholder="Search users...">
            <button id="search-icon" class="search-icon"><i class="fas fa-search"></i></button>
            <div id="search-results" class="search-results"></div> <!-- Added search results container -->
        </div>
    </header>

    <!-- Main Container -->
    <div class="main-container">
        <!-- Side Navigation -->
        <nav class="side-nav" id="side-nav">
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="#">Settings</a></li>
                <li><a href="messages.php">Messages</a></li>
                <li><a href="logout.php">Logout</a></li>
                <button id="theme-toggle">Toggle Theme</button>
            </ul>
        </nav>

        <!-- Content Area -->
        <div class="content">
            <h2>Welcome to Your Dashboard</h2>
            <p>This is your main content area.</p>
        </div>
    </div>

    <script src="./scripts.js"></script>
</body>
</html>
