<?php
include_once "../config/cors.php";
include_once "../config/connection.php";

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->id) && !empty($data->faculty_name)) {
    $id = $data->id;
    $faculty_name = $data->faculty_name;

    $dup = $conn->prepare("SELECT id FROM `faculty` WHERE faculty_name=? AND id!=?");
    $dup->bind_param("si", $faculty_name, $id);
    $dup->execute();
    if ($dup->get_result()->num_rows > 0) {
        echo json_encode(["success" => false, "message" => "Faculty already exists"]);
        exit;
    }
    $dup->close();

    $stmt = $conn->prepare("UPDATE `faculty` SET `faculty_name` = ? WHERE `id` = ?");
    $stmt->bind_param("si", $faculty_name, $id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Faculty updated"]);
    } else {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Unable to update faculty"]);
    }
    $stmt->close();
} else {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Data is incomplete"]);
}
$conn->close();
