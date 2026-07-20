<?php
include_once '../config/cors.php';
include_once '../config/connection.php';
require_once '../PHPMailer/src/PHPMailer.php';
require_once '../PHPMailer/src/SMTP.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['user_id'])) {
    echo json_encode(["success" => false, "message" => "Data is incomplete"]);
    exit;
}

$user_id = $data['user_id'];
$now = date('Y-m-d H:i:s');

$stmt = $conn->prepare("SELECT id, email_address, first_name, otp_expires_at FROM `user` WHERE id=? AND status=1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo json_encode(["success" => false, "message" => "User not found"]);
    exit;
}

$user = $result->fetch_assoc();

if ($user['otp_expires_at'] !== null && $user['otp_expires_at'] > $now) {
    echo json_encode(["success" => false, "message" => "Please wait until current OTP expires"]);
    exit;
}

$otp = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
$expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));

$update = $conn->prepare("UPDATE `user` SET otp_code=?, otp_expires_at=? WHERE id=?");
$update->bind_param("ssi", $otp, $expires, $user_id);
$update->execute();
$update->close();

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = 'smtp-apeiron.alwaysdata.net';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'apeiron@alwaysdata.net';
    $mail->Password   = 'uwaje1960';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;

    $mail->setFrom('apeiron@alwaysdata.net', 'Onyeka Uwaje');
    $mail->addAddress($user['email_address'], $user['first_name']);
    $mail->isHTML(true);
    $mail->Subject = 'Your New Login OTP';
    $mail->Body    = "Hello {$user['first_name']}, <br><br> Your new login OTP is: <b>{$otp}</b> <br> It expires in 10 minutes.";
    $mail->AltBody = "Hello {$user['first_name']}, \n\n Your new login OTP is: {$otp} \n It expires in 10 minutes.";

    $mail->send();
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Failed to send OTP"]);
    exit;
}

echo json_encode(["success" => true, "message" => "New OTP sent to your email"]);

$stmt->close();
$conn->close();