<?php
include './functions.php'; // Include the functions file

// Initialize variables to store error messages and success message
$errors = [];
$successMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    // Implement CSRF protection
    if (!hash_equals($_SESSION['token'], $_POST['token'])) {
        die('CSRF token validation failed');
    }
    
    $result = registerUser($conn); // Call the registerUser function

    if (is_array($result)) {
        $errors = $result; // Store error messages
    } else {
        $successMessage = $result; // Store success message
    }
}

$conn->close(); // Close the database connection
$_SESSION['token'] = bin2hex(random_bytes(32)); // Generate CSRF token
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./styles/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Erdon.NET | Register</title>
</head>
<body>
    <div class="container">
        <div class="form-box">
            <h2>Register</h2>
            <form method="post" action="register.php">
                <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">
                <div class="form-group">
                    <input type="text" name="username" placeholder="Username" required>
                    <?php if (isset($error_username)) { echo "<p class='error-message'>$error_username</p>"; } ?>
                </div>
                <div class="form-group password-container">
                    <input type="password" name="password" placeholder="Password" required>
                    <span class="toggle-password" onclick="togglePassword()">Show</span>
                    <?php if (isset($error_password)) { echo "<p class='error-message'>$error_password</p>"; } ?>
                </div>
                <div class="form-group">
                    <input type="email" name="useremail" placeholder="Email" required>
                    <?php if (isset($error_useremail)) { echo "<p class='error-message'>$error_useremail</p>"; } ?>
                </div>
                <input type="submit" name="register" value="Register">
            </form>
            <p>Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </div>
    <script src="./scripts/scripts.js"></script>
</body>
</html>
