<?php
include_once '../config/cors.php';
include_once '../config/connection.php';

$data = json_decode(file_get_contents("php://input"), true);

$token = $data['token'] ?? '';

if (empty($token)) {
    echo json_encode(["success" => false, "message" => "Token not provided"]);
    exit;
}

$stmt = $conn->prepare("SELECT role FROM `user` WHERE token=? AND status=1");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    echo json_encode(["success" => false, "message" => "Invalid token"]);
    exit;
}
$admin = $result->fetch_assoc();
if ($admin['role'] != 'Admin') {
    echo json_encode(["success" => false, "message" => "Only Admin can add users"]);
    exit;
}
$stmt->close();

if (!isset($data['first_name']) || !isset($data['last_name']) || !isset($data['email_address']) || !isset($data['password'])) {
    echo json_encode(["success" => false, "message" => "Data is incomplete"]);
    exit;
}

$first_name = $data['first_name'];
$last_name = $data['last_name'];
$middle_name = $data['middle_name'] ?? NULL;
$email = $data['email_address'];
$password = password_hash($data['password'], PASSWORD_DEFAULT);
$role = $data['role'] ?? 'Admin';
$new_token = bin2hex(random_bytes(32));

$stmt = $conn->prepare("SELECT id FROM `user` WHERE email_address=?");
$stmt->bind_param("s", $email);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "Email already exists"]);
    exit;
}
$stmt->close();

$stmt = $conn->prepare("INSERT INTO `user` (first_name, last_name, middle_name, email_address, password, role, token) VALUES (?,?,?,?,?,?,?)");
$stmt->bind_param("sssssss", $first_name, $last_name, $middle_name, $email, $password, $role, $new_token);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "User added successfully", "user_id" => $conn->insert_id]);
} else {
    echo json_encode(["success" => false, "message" => "Error"]);
}

$stmt->close();
$conn->close();
