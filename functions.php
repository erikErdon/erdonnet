<?php
include './db.php'; // Include the db file

// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Function to handle user login
function loginUser($conn) {
    $errors = [];
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];

        // Retrieve user data
        $stmt = $conn->prepare("SELECT id, userpassword FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['userpassword'])) {
                // Set session variables
                $_SESSION['username'] = $username;
                $_SESSION['user_id'] = $user['id']; // Set user_id in session
                header("Location: index.php");
                exit();
            } else {
                $errors[] = "Invalid password!";
            }
        } else {
            $errors[] = "Username not found!";
        }

        $stmt->close();
    }

    return $errors;
}

// Function to handle user registration
function registerUser($conn) {
    $errors = [];
    $successMessage = "";

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password
        $useremail = $_POST['useremail'];

        // Check for existing username
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $errors['username'] = "Username already exists!";
        } else {
            // Insert new user
            $stmt = $conn->prepare("INSERT INTO users (username, userpassword, useremail) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $password, $useremail);

            if ($stmt->execute()) {
                $successMessage = "Registration successful!";
            } else {
                $errors['general'] = "Registration failed!";
            }

            $stmt->close();
        }
    }

    return !empty($errors) ? $errors : $successMessage;
}

// Function to handle user logout
function logoutUser() {
    session_start();
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

// Function to search users
function searchUsers($conn, $query) {
    $profiles = [];
    if ($query) {
        $stmt = $conn->prepare("SELECT id, username AS name FROM users WHERE username LIKE ?");
        $searchQuery = "%{$query}%";
        $stmt->bind_param("s", $searchQuery);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $profiles[] = $row;
        }

        $stmt->close();
    }
    return $profiles;
}

// Function to get user profile
function getUserProfile($conn, $profileId) {
    $stmt = $conn->prepare("SELECT id, username, useremail FROM users WHERE id = ?");
    $stmt->bind_param("i", $profileId);
    $stmt->execute();
    $result = $stmt->get_result();
    $profile = $result->fetch_assoc();
    $stmt->close();
    return $profile;
}

// Function to send a message
function sendMessage($conn, $sender_id, $receiver_id, $message) {
    $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message, timestamp, is_read) VALUES (?, ?, ?, NOW(), 0)");
    if ($stmt === false) {
        return false;
    }

    $stmt->bind_param("iis", $sender_id, $receiver_id, $message);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

// Function to get messages
function getMessages($conn, $sender_id, $receiver_id) {
    $stmt = $conn->prepare("
        SELECT m.*, u.username, 
               CASE WHEN m.sender_id = ? THEN 'sent' ELSE 'received' END as direction
        FROM messages m
        JOIN users u ON u.id = CASE
            WHEN m.sender_id = ? THEN m.receiver_id
            ELSE m.sender_id
        END
        WHERE (m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?)
        ORDER BY m.timestamp
    ");
    
    if ($stmt === false) {
        return [];
    }

    $stmt->bind_param("iiiiii", $sender_id, $sender_id, $sender_id, $receiver_id, $receiver_id, $sender_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
    $stmt->close();

    // Mark messages as read for the current user
    $stmt = $conn->prepare("UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ? AND is_read = 0");
    if ($stmt !== false) {
        $stmt->bind_param("ii", $receiver_id, $sender_id);
        $stmt->execute();
        $stmt->close();
    }

    return $messages;
}

// Function to mark messages as read
function markMessagesAsRead($conn, $receiver_id, $current_user_id) {
    $stmt = $conn->prepare("UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ?");
    $stmt->bind_param("ii", $receiver_id, $current_user_id);
    $stmt->execute();
    $stmt->close();
}

// Function to get the count of unread messages
function getUnreadCount($conn, $current_user_id) {
    $stmt = $conn->prepare("SELECT COUNT(*) AS unread_count FROM messages WHERE receiver_id = ? AND is_read = 0");
    $stmt->bind_param("i", $current_user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $unread_data = $result->fetch_assoc();
    $stmt->close();
    return $unread_data['unread_count'];
}


// Function to get a user list
function getUserList($conn, $current_user_id) {
    $stmt = $conn->prepare("SELECT id, username FROM users WHERE id != ?");
    $stmt->bind_param("i", $current_user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    $stmt->close();
    return $users;
}

// Function to get username by user ID
function getUsernameById($conn, $user_id) {
    $stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        return $row['username'];
    } else {
        return null; // Or handle it as appropriate if the user is not found
    }
}

// Handling AJAX Requests
if (isset($_POST['action'])) {
    header('Content-Type: application/json');
    $response = ['status' => 'error'];

    if (!isset($_SESSION['user_id'])) {
        $response['message'] = 'User not logged in';
        echo json_encode($response);
        exit();
    }

    $action = $_POST['action'];
    $sender_id = $_SESSION['user_id'];

    switch ($action) {
        case 'send_message':
            $receiver_id = isset($_POST['receiver_id']) ? intval($_POST['receiver_id']) : 0;
            $message = isset($_POST['message']) ? $_POST['message'] : '';
            if (sendMessage($conn, $sender_id, $receiver_id, $message)) {
                $response = ['status' => 'success'];
            } else {
                $response['message'] = 'Failed to send message';
            }
            break;

        case 'get_messages':
            $receiver_id = isset($_POST['receiver_id']) ? intval($_POST['receiver_id']) : 0;
            $messages = getMessages($conn, $sender_id, $receiver_id);
            $response = ['status' => 'success', 'messages' => $messages];
            break;

        case 'get_user_list':
            $users = getUserList($conn, $sender_id);
            $response = ['status' => 'success', 'users' => $users];
            break;
            case 'get_unread_count':
                $current_user_id = $_SESSION['user_id'];
                $unread_count = getUnreadCount($conn, $current_user_id);
                echo json_encode(['status' => 'success', 'unread_count' => $unread_count]);
                break;
    
            case 'mark_messages_as_read':
                $receiver_id = $_POST['receiver_id'] ?? 0;
                $current_user_id = $_SESSION['user_id'];
                markMessagesAsRead($conn, $receiver_id, $current_user_id);
                echo json_encode(['status' => 'success']);
                break;
        default:
            $response['message'] = 'Invalid action';
    }

    echo json_encode($response);
    exit();
}

// Function to get users with whom the current user has messages, along with the last message and timestamp
function getUsersWithMessages($conn, $user_id) {
    $stmt = $conn->prepare("
        SELECT u.id, u.username, m.message, m.timestamp, 
               CASE 
                   WHEN m.receiver_id = ? AND m.is_read = 0 THEN 0 
                   ELSE 1 
               END as is_read, 
               m.sender_id
        FROM (
            SELECT 
                CASE 
                    WHEN sender_id = ? THEN receiver_id 
                    ELSE sender_id 
                END AS user_id, 
                MAX(timestamp) AS last_timestamp
            FROM messages
            WHERE sender_id = ? OR receiver_id = ?
            GROUP BY user_id
        ) AS sub
        JOIN messages m ON (m.sender_id = sub.user_id AND m.receiver_id = ? AND m.timestamp = sub.last_timestamp)
                        OR (m.receiver_id = sub.user_id AND m.sender_id = ? AND m.timestamp = sub.last_timestamp)
        JOIN users u ON u.id = sub.user_id
        ORDER BY m.timestamp DESC
    ");
    
    if ($stmt === false) {
        return [];
    }

    $stmt->bind_param("iiiiii", $user_id, $user_id, $user_id, $user_id, $user_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    $stmt->close();

    return $users;
}
?>
