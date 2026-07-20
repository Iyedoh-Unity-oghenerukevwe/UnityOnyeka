<?php
include_once "../config/cors.php";
include_once "../config/connection.php";

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->faculty_name)) {
    $faculty_name = $data->faculty_name;

    $dup = $conn->prepare("SELECT id FROM `faculty` WHERE faculty_name=?");
    $dup->bind_param("s", $faculty_name);
    $dup->execute();
    if ($dup->get_result()->num_rows > 0) {
        echo json_encode(["success" => false, "message" => "Faculty already exists"]);
        exit;
    }
    $dup->close();

    $stmt = $conn->prepare("INSERT INTO `faculty` (`faculty_name`) VALUES (?)");
    $stmt->bind_param("s", $faculty_name);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Faculty added", "id" => $conn->insert_id]);
    } else {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Unable to add faculty"]);
    }
    $stmt->close();
} else {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Data is incomplete"]);
}
$conn->close();
