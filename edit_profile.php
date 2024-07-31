<?php
session_start();
include './functions.php';
include './db.php';

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Get the current logged-in user's ID
$user_id = $_SESSION['user_id'];

// Get user profile data
$profile = getUserProfile($conn, $user_id);

if (!$profile) {
    // Handle case when profile is not found, e.g., show an error message or redirect
    header("Location: index.php");
    exit();
}

// Update profile data if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_username = $_POST['username'];
    $new_useremail = $_POST['useremail'];

    $stmt = $conn->prepare("UPDATE users SET username = ?, useremail = ? WHERE id = ?");
    $stmt->bind_param("ssi", $new_username, $new_useremail, $user_id);

    if ($stmt->execute()) {
        $_SESSION['username'] = $new_username; // Update session username
        header("Location: profile.php");
        exit();
    } else {
        $error_message = "Failed to update profile. Please try again.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./styles/style.css">
    <title>Edit Profile</title>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <button class="hamburger-menu" id="hamburger-menu">&#9776;</button>
        <h1>Edit Profile</h1>
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
                <h2>Edit Profile</h2>

                <?php if (isset($error_message)): ?>
                    <p class="error-message"><?= htmlspecialchars($error_message) ?></p>
                <?php endif; ?>

                <form method="post" action="edit_profile.php">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" value="<?= htmlspecialchars($profile['username']) ?>" required>

                    <label for="useremail">Email:</label>
                    <input type="email" id="useremail" name="useremail" value="<?= htmlspecialchars($profile['useremail']) ?>" required>

                    <input type="submit" value="Update Profile">
                </form>
            </div>
        </div>
    </div>

    <script src="./scripts.js"></script>
</body>
</html>
