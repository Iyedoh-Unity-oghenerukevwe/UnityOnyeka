<?php
include_once "../config/cors.php";
include_once "../config/connection.php";

$data = json_decode(file_get_contents("php://input"), true);
$token = $data['token'] ?? '';

if (empty($token)) {
    echo json_encode(["success" => false, "message" => "Token required"]);
    exit;
}

$check = $conn->prepare("SELECT id FROM `user` WHERE token=? LIMIT 1");
$check->bind_param("s", $token);
$check->execute();
$checkResult = $check->get_result();

if ($checkResult->num_rows == 0) {
    echo json_encode(["success" => false, "message" => "Invalid token"]);
    exit;
}

$stmt = $conn->prepare("SELECT d.id, d.department_name, f.faculty_name FROM department d INNER JOIN faculty f ON d.faculty_id = f.id ORDER BY d.id DESC");
$stmt->execute();
$result = $stmt->get_result();

$departments = [];
while ($row = $result->fetch_assoc()) {
    $departments[] = $row;
}

echo json_encode(["success" => true, "departments" => $departments]);
$check->close();
$stmt->close();
$conn->close();
