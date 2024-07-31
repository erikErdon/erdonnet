<?php
session_start(); // Start the session

// Include the functions file for messaging
include './functions.php';

// Include the database connection file
include './db.php';

// Check if user is logged in, redirect to login page if not
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Fetch current user ID from the session
$current_user_id = $_SESSION['user_id'];

// Fetch receiver ID from query parameter
$receiver_id = isset($_GET['receiver_id']) ? intval($_GET['receiver_id']) : 0;

// Fetch messages for the chat
$messages = getMessages($conn, $current_user_id, $receiver_id);

// Fetch user list for new chat
$user_list = getUserList($conn, $current_user_id);

// Fetch receiver's username
$receiver_username = getUsernameById($conn, $receiver_id); // This should now work
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./styles/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Chat</title>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <button class="hamburger-menu" id="hamburger-menu">&#9776;</button>
        <h1>Chat</h1>
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
                <li><a href="#">Profile</a></li>
                <li><a href="#">Settings</a></li>
                <li><a href="messages.php">Messages</a></li>
                <li><a href="logout.php">Logout</a></li>
                <button id="theme-toggle">Toggle Theme</button>
            </ul>
        </nav>

        <!-- Content Area -->
        <div class="content">
            <div class="chat-container">
                <!-- Display recipient's username -->
                <div class="chat-header">
                    <h2>Chat with <?= htmlspecialchars($receiver_username) ?></h2>
                </div>
                
                <!-- Messages Container -->
                <div class="messages" id="messages">
                    <?php foreach ($messages as $message): ?>
                        <div class="message <?= $message['sender_id'] == $current_user_id ? 'sent' : 'received' ?>">
                            <p><?= htmlspecialchars($message['message']) ?></p>
                            <div class="message-time"><?= date("H:i", strtotime($message['timestamp'])) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Message Input Area -->
                <div class="message-input-container">
                    <input type="text" id="message-input" placeholder="Type your message...">
                    <button id="send-button">Send</button>
                </div>
            </div>
        </div>
    </div>

    <script src="./scripts.js"></script>
</body>
</html>
