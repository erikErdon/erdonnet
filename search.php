<?php
include './functions.php';
include './db.php'; // Assuming db.php contains the $conn variable

$query = isset($_GET['query']) ? $_GET['query'] : '';
$results = searchUsers($conn, $query);

echo json_encode(['results' => $results]);
?>