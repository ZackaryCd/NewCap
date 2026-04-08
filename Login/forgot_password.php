<?php
header('Content-Type: application/json');
// siguraduhin na tama ang path dito
require '../Backend/db.php';

$data = json_decode(file_get_contents('php://input'), true);
$email = $data['email'] ?? '';

$userquery = $conn->prepare("select id from users where email = ?");
$userquery->bind_param("s", $email);
$userquery->execute();
$userresult = $userquery->get_result();

if ($userresult->num_rows > 0) {
    $user = $userresult->fetch_assoc();
    $userid = $user['id'];
    $otp = sprintf("%06d", mt_rand(0, 999999));
    $expires = date("y-m-d h:i:s", strtotime("+10 minutes"));

    $deleteold = $conn->prepare("delete from password_resets where user_id = ?");
    $deleteold->bind_param("i", $userid);
    $deleteold->execute();

    $insert = $conn->prepare("insert into password_resets (user_id, otp, expires_at) values (?, ?, ?)");
    $insert->bind_param("iss", $userid, $otp, $expires);
    
    if ($insert->execute()) {
        echo json_encode(["status" => "success", "msg" => "Code sent to your email."]);
    }
} else {
    echo json_encode(["status" => "error", "msg" => "Email not found."]);
}
?>