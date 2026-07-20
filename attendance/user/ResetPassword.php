<?php
include_once '../config/cors.php';
include_once '../config/connection.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['email_address']) || !isset($data['password'])) {
    echo json_encode(["success" => false, "message" => "Data is incomplete"]);
    exit;
}

$email = $data['email_address'];
$password = password_hash($data['password'], PASSWORD_DEFAULT);

$update = $conn->prepare("UPDATE `user` SET password=?, reset_otp=NULL, reset_otp_expires_at=NULL WHERE email_address=?");
$update->bind_param("ss", $password, $email);
$update->execute();

if ($update->affected_rows > 0) {
    echo json_encode(["success" => true, "message" => "Password updated successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to update password"]);
}

$update->close();
$conn->close();
