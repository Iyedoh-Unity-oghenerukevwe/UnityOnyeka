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

$stmt = $conn->prepare("SELECT * FROM `faculty` ORDER BY id DESC");
$stmt->execute();
$result = $stmt->get_result();

$faculties = [];
while ($row = $result->fetch_assoc()) {
    $faculties[] = $row;
}

echo json_encode(["success" => true, "faculties" => $faculties]);
$check->close();
$stmt->close();
$conn->close();
