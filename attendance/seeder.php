<?php
include_once './config/cors.php';
include_once './config/connection.php';

$stmt = $conn->prepare("SELECT id FROM `user` LIMIT 1");
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "Users already exist"]);
    exit;
}

$first_name = "Onyekachukwu";
$last_name = "Uwaje";
$middle_name = "Peter";
$email = "uwajeonyeka05@gmail.com";
$password = password_hash("uwaje123", PASSWORD_DEFAULT);
$role = "Admin";
$token = "f8c3a9d2e1b04f6a7b8c9d0e1f2a3b4c";

$stmt = $conn->prepare("INSERT INTO `user` (first_name, last_name, middle_name, email_address, password, role, token) VALUES (?,?,?,?,?,?,?)");
$stmt->bind_param("sssssss", $first_name, $last_name, $middle_name, $email, $password, $role, $token);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Seeder completed", "email" => $email, "password" => "uwaje123", "token" => $token]);
} else {
    echo json_encode(["success" => false, "message" => "Error"]);
}

$stmt->close();
$conn->close();
