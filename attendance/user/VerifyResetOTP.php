<?php
include_once '../config/cors.php';
include_once '../config/connection.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['email_address']) || !isset($data['otp'])) {
    echo json_encode(["success" => false, "message" => "Data is incomplete"]);
    exit;
}

$email = $data['email_address'];
$otp = $data['otp'];
$now = date('Y-m-d H:i:s');

$stmt = $conn->prepare("SELECT id FROM `user` WHERE email_address=? AND reset_otp=? AND reset_otp_expires_at > ?");
$stmt->bind_param("sss", $email, $otp, $now);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo json_encode(["success" => false, "message" => "Invalid or expired OTP"]);
    exit;
}

echo json_encode(["success" => true, "message" => "OTP verified"]);

$stmt->close();
$conn->close();
