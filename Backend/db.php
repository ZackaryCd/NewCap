<?php
$host = "localhost";     
$user = "admin";         
$pass = "admin123";      
$db   = "eu_db";   
$port = 3307;            

// Create connection
$conn = new mysqli($host, $user, $pass, $db, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function logActivity($conn, $user_id, $action, $item_name, $item_type) {

    // Skip logging kung walang valid user_id
    if (empty($user_id)) {
        return;
    }

    // Optional: check if user exists
    $check = $conn->prepare("SELECT id FROM users WHERE id = ?");
    $check->bind_param("i", $user_id);
    $check->execute();
    $result = $check->get_result();
    if ($result->num_rows === 0) {
        return; // stop logging if user not found
    }

    $stmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, item_name, item_type) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $action, $item_name, $item_type);
    $stmt->execute();
    $stmt->close();
}

?>
