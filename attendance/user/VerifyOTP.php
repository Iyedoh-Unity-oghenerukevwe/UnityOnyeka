<?php
include_once '../config/cors.php';
include_once '../config/connection.php';
$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data['user_id']) || !isset($data['otp'])) {
    echo json_encode(["success" => false, "message" => "Data is incomplete"]);
    exit;
}
$user_id = $data['user_id'];
$otp = $data['otp'];
$now = date('Y-m-d H:i:s');
$stmt = $conn->prepare("SELECT * FROM `user` WHERE id=? AND otp_code=? AND otp_expires_at > ?");
$stmt->bind_param("iss", $user_id, $otp, $now);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    echo json_encode(["success" => false, "message" => "Invalid or expired OTP"]);
    exit;
}
$user = $result->fetch_assoc();
$token = bin2hex(random_bytes(32));
$update = $conn->prepare("UPDATE `user` SET token=?, active_token=?, otp_code=NULL, otp_expires_at=NULL WHERE id=?");
$update->bind_param("ssi", $token, $token, $user_id);
$update->execute();
$update->close();
echo json_encode(["success" => true, "message" => "Login successful", "token" => $token, "user" => [
    "id" => $user['id'],
    "first_name" => $user['first_name'],
    "last_name" => $user['last_name'],
    "middle_name" => $user['middle_name'],
    "email_address" => $user['email_address'],
    "role" => $user['role']
]]);
$stmt->close();
$conn->close();
