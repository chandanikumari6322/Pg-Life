<?php
session_start();
header('Content-Type: application/json');
require_once '../includes/db.php';

$response = ["success" => false, "message" => "", "interested" => false];

if (!isset($_SESSION['user_id'])) {
    $response["message"] = "please_login";
    echo json_encode($response);
    exit;
}

$user_id = $_SESSION['user_id'];
$property_id = intval($_POST['property_id'] ?? 0);

if ($property_id <= 0) {
    $response["message"] = "Invalid property.";
    echo json_encode($response);
    exit;
}

$check = $conn->prepare("SELECT 1 FROM interested_users WHERE user_id = ? AND property_id = ?");
$check->bind_param("ii", $user_id, $property_id);
$check->execute();
$exists = $check->get_result()->num_rows > 0;
$check->close();

if ($exists) {
    $del = $conn->prepare("DELETE FROM interested_users WHERE user_id = ? AND property_id = ?");
    $del->bind_param("ii", $user_id, $property_id);
    $del->execute();
    $del->close();
    $response["interested"] = false;
} else {
    $ins = $conn->prepare("INSERT INTO interested_users (user_id, property_id) VALUES (?, ?)");
    $ins->bind_param("ii", $user_id, $property_id);
    $ins->execute();
    $ins->close();
    $response["interested"] = true;
}

// Get updated count
$countStmt = $conn->prepare("SELECT COUNT(*) as cnt FROM interested_users WHERE property_id = ?");
$countStmt->bind_param("i", $property_id);
$countStmt->execute();
$count = $countStmt->get_result()->fetch_assoc()['cnt'];
$countStmt->close();

$response["success"] = true;
$response["interested_count"] = $count;
echo json_encode($response);

$conn->close();
?>
