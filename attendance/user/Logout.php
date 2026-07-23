<?php
include_once '../config/cors.php';
include_once '../config/connection.php';
$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data['user_id'])) {
    echo json_encode(["success" => false, "message" => "User ID required"]);
    exit;
}
$user_id = $data['user_id'];
$stmt = $conn->prepare("UPDATE `user` SET active_token=NULL WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
echo json_encode(["success" => true, "message" => "Logged out"]);
$stmt->close();
$conn->close();
