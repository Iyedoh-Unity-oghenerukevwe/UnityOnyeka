<?php
include_once "../config/cors.php";
include_once "../config/connection.php";

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->department_name) && !empty($data->faculty_id)) {
    $department_name = $data->department_name;
    $faculty_id = $data->faculty_id;

    $check = $conn->prepare("SELECT id FROM `faculty` WHERE id=?");
    $check->bind_param("i", $faculty_id);
    $check->execute();
    $check_result = $check->get_result();
    if ($check_result->num_rows == 0) {
        echo json_encode(["success" => false, "message" => "Faculty not found"]);
        exit;
    }
    $check->close();

    $dup = $conn->prepare("SELECT id FROM `department` WHERE department_name=? AND faculty_id=?");
    $dup->bind_param("si", $department_name, $faculty_id);
    $dup->execute();
    if ($dup->get_result()->num_rows > 0) {
        echo json_encode(["success" => false, "message" => "Department already exists for this faculty"]);
        exit;
    }
    $dup->close();

    $stmt = $conn->prepare("INSERT INTO `department` (`department_name`, `faculty_id`) VALUES (?, ?)");
    $stmt->bind_param("si", $department_name, $faculty_id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Department added", "id" => $conn->insert_id]);
    } else {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Unable to add department"]);
    }
    $stmt->close();
} else {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Data is incomplete"]);
}
$conn->close();
