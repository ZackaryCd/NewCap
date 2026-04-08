<?php
header('Content-Type: application/json');
require '../Backend/db.php';

$data = json_decode(file_get_contents('php://input'), true);
$email = $data['email'] ?? '';
$otp = $data['otp'] ?? '';

// I-verify ang code gamit ang JOIN sa users table
$check = $conn->prepare("
    SELECT r.user_id 
    FROM password_resets r
    JOIN users u ON r.user_id = u.id
    WHERE u.email = ? AND r.otp = ? AND r.expires_at > NOW()
");
$check->bind_param("ss", $email, $otp);
$check->execute();
$res = $check->get_result();

if ($res->num_rows > 0) {
    echo json_encode(["status" => "success", "msg" => "Identity verified."]);
} else {
    echo json_encode(["status" => "error", "msg" => "Invalid or expired code."]);
}
?>