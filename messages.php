<?php
session_start(); // Start the session
include './functions.php'; // Include the functions file for messaging
include './db.php'; // Include the database connection file

// Check if user is logged in, redirect to login page if not
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Fetch current user ID from the session
$current_user_id = $_SESSION['user_id'];

// Fetch users with whom the current user has messages
$users = getUsersWithMessages($conn, $current_user_id);

// Fetch the total count of unread messages for the current user
$stmt = $conn->prepare("SELECT COUNT(*) AS unread_count FROM messages WHERE receiver_id = ? AND is_read = 0");
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$result = $stmt->get_result();
$unread_data = $result->fetch_assoc();
$unread_count = $unread_data['unread_count'];
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./styles/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Messages</title>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <button class="hamburger-menu" id="hamburger-menu">&#9776;</button>
        <h1>Messages</h1>
        <!-- Search Bar in Header -->
        <div class="search-container">
            <input type="text" id="search-bar" class="search-bar" placeholder="Search users...">
            <button id="search-icon" class="search-icon"><i class="fas fa-search"></i></button>
            <div id="search-results" class="search-results"></div>
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
                <li class="messages-link">
                    <a href="messages.php">Messages</a>
                    <?php if ($unread_count > 0): ?>
                        <span class="unread-count"><?= $unread_count ?></span>
                    <?php endif; ?>
                </li>
                <li><a href="logout.php">Logout</a></li>
                <button id="theme-toggle">Toggle Theme</button>
            </ul>
        </nav>

        <!-- Content Area -->
        <div class="content">
            <ul>
                <?php foreach ($users as $user): ?>
    <?php
    // Determine the class based on the message's read status and type
    $message_status_class = ($user['is_read'] == 0 && $user['sender_id'] != $current_user_id) ? 'unread' : 'read';
    $message_type_class = $user['sender_id'] == $current_user_id ? 'sent' : 'received';
    ?>
    <li>
        <a href="chat.php?receiver_id=<?= htmlspecialchars($user['id']) ?>">
            <div class="user-message <?= $message_status_class ?> <?= $message_type_class ?>">
                <div class="message-header">
                    <span class="username"><?= htmlspecialchars($user['username']) ?></span>
                    <span class="message-time"><?= htmlspecialchars($user['timestamp']) ?></span>
                </div>
                <span class="last-message"><?= htmlspecialchars($user['message']) ?></span>
            </div>
        </a>
    </li>
<?php endforeach; ?>
            </ul>
        </div>
    </div>

    <script src="./scripts.js"></script>
</body>
</html>
