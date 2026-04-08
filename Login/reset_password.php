<?php
header('Content-Type: application/json');
require '../Backend/db.php';
$data = json_decode(file_get_contents('php://input'), true);
$email = $data['email'] ?? '';
$newPass = $data['password'] ?? '';
$hashedPass = password_hash($newPass, PASSWORD_BCRYPT);

$update = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
$update->bind_param("ss", $hashedPass, $email);

if ($update->execute()) {
    // Burahin ang OTP record pagkatapos gamitin
    $cleanup = $conn->prepare("DELETE FROM password_resets WHERE user_id = (SELECT id FROM users WHERE email = ?)");
    $cleanup->bind_param("s", $email);
    $cleanup->execute();

    echo json_encode(["status" => "success", "msg" => "Password updated successfully."]);
} else {
    echo json_encode(["status" => "error", "msg" => "Failed to update password."]);
}
?>