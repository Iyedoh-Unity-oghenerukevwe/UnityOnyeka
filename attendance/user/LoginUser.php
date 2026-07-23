<?php
include_once '../config/cors.php';
include_once '../config/connection.php';
require_once '../PHPMailer/src/PHPMailer.php';
require_once '../PHPMailer/src/SMTP.php';
$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data['email_address']) || !isset($data['password'])) {
    echo json_encode(["success" => false, "message" => "Data is incomplete"]);
    exit;
}
$email_address = $data['email_address'];
$password = $data['password'];
$stmt = $conn->prepare("SELECT * FROM `user` WHERE email_address=? AND status=1");
$stmt->bind_param("s", $email_address);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    echo json_encode(["success" => false, "message" => "Invalid email or password"]);
    exit;
}
$user = $result->fetch_assoc();
if (!password_verify($password, $user['password'])) {
    echo json_encode(["success" => false, "message" => "Invalid email or password"]);
    exit;
}
if ($user['active_token'] !== null) {
    echo json_encode(["success" => false, "message" => "Account is already logged in on another device"]);
    exit;
}
$otp = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
$expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));
$update = $conn->prepare("UPDATE `user` SET otp_code=?, otp_expires_at=? WHERE id=?");
$update->bind_param("ssi", $otp, $expires, $user['id']);
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
    $mail->Subject = 'Your Login OTP';
    $mail->Body    = "Hello {$user['first_name']}, <br><br> Your login OTP is: <b>{$otp}</b> <br> It expires in 10 minutes.";
    $mail->AltBody = "Hello {$user['first_name']}, \n\n Your login OTP is: {$otp} \n It expires in 10 minutes.";
    $mail->send();
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Failed to send OTP"]);
    exit;
}
echo json_encode(["success" => true, "message" => "OTP sent to your email", "require_otp" => true, "user_id" => $user['id']]);
$stmt->close();
$conn->close();
