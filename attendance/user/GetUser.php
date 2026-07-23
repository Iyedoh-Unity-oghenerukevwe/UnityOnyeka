<?php
include_once '../config/cors.php';
include_once '../config/connection.php';

$data = json_decode(file_get_contents("php://input"), true);
$token = $data['token'] ?? '';

if (empty($token)) {
    echo json_encode(["success" => false, "message" => "Token required"]);
    exit;
}

$check = $conn->prepare("SELECT role FROM `user` WHERE token=? AND status=1 LIMIT 1");
$check->bind_param("s", $token);
$check->execute();
$result = $check->get_result();

if ($result->num_rows == 0) {
    echo json_encode(["success" => false, "message" => "Invalid token"]);
    exit;
}

$userData = $result->fetch_assoc();
if ($userData['role'] != 'Admin') {
    echo json_encode(["success" => false, "message" => "Only Admin can view users"]);
    exit;
}
$check->close();

$stmt = $conn->prepare("SELECT id, first_name, last_name, middle_name, email_address, role, status, created_at FROM `user`");
$stmt->execute();
$result = $stmt->get_result();
$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

echo json_encode(["success" => true, "users" => $users]);

$stmt->close();
$conn->close();
