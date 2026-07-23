<?php
include_once '../config/cors.php';
include_once '../config/connection.php';
require_once '../PHPMailer/src/PHPMailer.php';
require_once '../PHPMailer/src/SMTP.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['email_address'])) {
    echo json_encode(["success" => false, "message" => "Email is required"]);
    exit;
}

$email = $data['email_address'];

$stmt = $conn->prepare("SELECT id, first_name, reset_otp_expires_at FROM `user` WHERE email_address=? AND status=1");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo json_encode(["success" => true, "message" => "If that email exists, an OTP has been sent"]);
    exit;
}

$user = $result->fetch_assoc();


if ($user['reset_otp_expires_at']) {
    $last_sent = strtotime($user['reset_otp_expires_at']) - 600;
    if (time() - $last_sent < 60) {
        echo json_encode(["success" => false, "message" => "Please wait 60 seconds before requesting another OTP"]);
        exit;
    }
}

$otp = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
$expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));

$update = $conn->prepare("UPDATE `user` SET reset_otp=?, reset_otp_expires_at=? WHERE id=?");
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
    $mail->addAddress($email, $user['first_name']);
    $mail->isHTML(true);
    $mail->Subject = 'Password Reset OTP';
    $mail->Body    = "Hello {$user['first_name']}, <br><br> Your password reset OTP is: <b>{$otp}</b> <br> This code expires in 10 minutes.";
    $mail->send();
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Failed to send OTP"]);
    exit;
}

echo json_encode(["success" => true, "message" => "OTP sent to your email"]);

$stmt->close();
$conn->close();
