<?php
include_once '../config/cors.php';
include_once '../config/connection.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['id']) || !isset($data['first_name']) || !isset($data['last_name'])) {
    echo json_encode(["success" => false, "message" => "Data is incomplete"]);
    exit;
}

$id = $data['id'];
$first_name = $data['first_name'];
$last_name = $data['last_name'];
$middle_name = isset($data['middle_name']) ? $data['middle_name'] : "";

$faculty_id = 0;
$department_id = 0;

if (isset($data['faculty_name']) && $data['faculty_name'] != "") {
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
}

if (isset($data['department_name']) && $data['department_name'] != "") {
    $department_name = $data['department_name'];
    if ($faculty_id == 0) {
        $curr_stmt = $conn->prepare("SELECT faculty_id FROM `student` WHERE id=?");
        $curr_stmt->bind_param("i", $id);
        $curr_stmt->execute();
        $curr_result = $curr_stmt->get_result();
        if ($curr_result->num_rows > 0) $faculty_id = $curr_result->fetch_assoc()['faculty_id'];
        $curr_stmt->close();
    }
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
}

$dup_stmt = $conn->prepare("SELECT id FROM `student` WHERE first_name=? AND last_name=? AND middle_name=? AND department_id=? AND faculty_id=? AND id!=?");
$dept_check = $department_id != 0 ? $department_id : null;
$fac_check = $faculty_id != 0 ? $faculty_id : null;
if ($dept_check === null || $fac_check === null) {
    $get_stmt = $conn->prepare("SELECT department_id, faculty_id FROM `student` WHERE id=?");
    $get_stmt->bind_param("i", $id);
    $get_stmt->execute();
    $current = $get_stmt->get_result()->fetch_assoc();
    $dept_check = $dept_check ?? $current['department_id'];
    $fac_check = $fac_check ?? $current['faculty_id'];
    $get_stmt->close();
}
$dup_stmt->bind_param("sssiii", $first_name, $last_name, $middle_name, $dept_check, $fac_check, $id);
$dup_stmt->execute();
if ($dup_stmt->get_result()->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "Student already exists"]);
    exit;
}
$dup_stmt->close();

$fields = "first_name=?, last_name=?, middle_name=?";
$params = [$first_name, $last_name, $middle_name];
$types = "sss";

if ($department_id != 0) {
    $fields .= ", department_id=?";
    $params[] = $department_id;
    $types .= "i";
}
if ($faculty_id != 0) {
    $fields .= ", faculty_id=?";
    $params[] = $faculty_id;
    $types .= "i";
}

$fields .= " WHERE id=?";
$params[] = $id;
$types .= "i";

$stmt = $conn->prepare("UPDATE `student` SET $fields");
$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Student updated successfully"]);
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Unable to update student"]);
}
$stmt->close();
$conn->close();
