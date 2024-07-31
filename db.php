<?php
// db.php
$servername = "localhost";
$username = "root";
$password = ""; //Ov8|[oC][/S
$dbname = "erdonnet"; 

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
