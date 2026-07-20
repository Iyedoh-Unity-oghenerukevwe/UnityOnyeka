<?php
include_once '../config/cors.php';
include_once '../config/connection.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['first_name']) || !isset($data['last_name']) || !isset($data['department_name']) || !isset($data['faculty_name'])) {
    echo json_encode(["success" => false, "message" => "Data is incomplete"]);
    exit;
}

$first_name = $data['first_name'];
$last_name = $data['last_name'];
$middle_name = isset($data['middle_name']) ? $data['middle_name'] : "";
$department_name = $data['department_name'];
$faculty_name = $data['faculty_name'];

$fac_stmt = $conn->prepare("SELECT id FROM `faculty` WHERE faculty_name=?");
$fac_stmt->bind_param("s", $faculty_name);
$fac_stmt->execute();
$fac_result = $fac_stmt->get_result();
if ($fac_result->num_rows == 0) {
    echo json_encode(["success" => false, "message" => "Faculty not found"]);
    exit;
}
$faculty_id = $fac_result->fetch_assoc()['id'];
$fac_stmt->close();

$dept_stmt = $conn->prepare("SELECT id FROM `department` WHERE department_name=? AND faculty_id=?");
$dept_stmt->bind_param("si", $department_name, $faculty_id);
$dept_stmt->execute();
$dept_result = $dept_stmt->get_result();
if ($dept_result->num_rows == 0) {
    echo json_encode(["success" => false, "message" => "Department not found"]);
    exit;
}
$department_id = $dept_result->fetch_assoc()['id'];
$dept_stmt->close();

$dup_stmt = $conn->prepare("SELECT id FROM `student` WHERE first_name=? AND last_name=? AND middle_name=? AND department_id=? AND faculty_id=?");
$dup_stmt->bind_param("sssii", $first_name, $last_name, $middle_name, $department_id, $faculty_id);
$dup_stmt->execute();
if ($dup_stmt->get_result()->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "Student already exists"]);
    exit;
}
$dup_stmt->close();

$stmt = $conn->prepare("INSERT INTO `student` (first_name, last_name, middle_name, department_id, faculty_id) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssii", $first_name, $last_name, $middle_name, $department_id, $faculty_id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Student added successfully", "id" => $conn->insert_id]);
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Unable to add student"]);
}
$stmt->close();
$conn->close();
