<?php
include_once '../config/cors.php';
include_once '../config/connection.php';

$data = json_decode(file_get_contents("php://input"), true);
$token = $data['token'] ?? '';

if (empty($token)) {
    echo json_encode(["success" => false, "message" => "Token required"]);
    exit;
}

$check = $conn->prepare("SELECT id FROM `user` WHERE token=? AND status=1 LIMIT 1");
$check->bind_param("s", $token);
$check->execute();
$checkResult = $check->get_result();

if ($checkResult->num_rows == 0) {
    echo json_encode(["success" => false, "message" => "Invalid token"]);
    exit;
}

$stmt = $conn->prepare("SELECT s.id, s.first_name, s.middle_name, s.last_name, f.faculty_name, d.department_name FROM student s JOIN faculty f ON s.faculty_id = f.id JOIN department d ON s.department_id = d.id");
$stmt->execute();
$result = $stmt->get_result();

$students = [];
while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}

echo json_encode(["success" => true, "data" => $students]);

$check->close();
$stmt->close();
$conn->close();
