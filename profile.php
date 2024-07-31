<?php
session_start();
include './functions.php';
include './db.php';

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Get the profile ID from the GET parameters or use the current logged-in user's ID
$profile_id = isset($_GET['id']) && (int)$_GET['id'] > 0 ? (int)$_GET['id'] : $_SESSION['user_id'];

// Get profile data
$profile = getUserProfile($conn, $profile_id);

if (!$profile) {
    // Handle case when profile is not found, e.g., show an error message or redirect
    header("Location: index.php");
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
    <title>Profile</title>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <button class="hamburger-menu" id="hamburger-menu">&#9776;</button>
        <h1>Profile</h1>
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
            <div class="profile-container">
                <h2><?= htmlspecialchars($profile['username']) ?></h2>
                <p>Email: <?= htmlspecialchars($profile['useremail']) ?></p>

                <?php if ($profile_id == $_SESSION['user_id']): ?>
                    <!-- Edit Profile Button -->
                    <a href="edit_profile.php" class="edit-profile-button">Edit Profile</a>
                <?php else: ?>
                    <!-- Send Message Button -->
                    <a href="chat.php?receiver_id=<?= htmlspecialchars($profile_id) ?>" class="send-message-button">Send Message</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="./scripts.js"></script>
</body>
</html>
