<?php
require_once 'admin_auth.php';
header('Content-Type: application/json');

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    http_response_code(403);
    echo json_encode(["success" => false, "message" => "Not authorized."]);
    exit;
}

require_once '../includes/db.php';

$response = ["success" => false, "message" => ""];

$booking_id = intval($_POST['booking_id'] ?? 0);
$status = $_POST['status'] ?? '';

if (!in_array($status, ['confirmed', 'cancelled'])) {
    $response["message"] = "Invalid status.";
    echo json_encode($response);
    exit;
}

try {
    // notified = 0 so the user sees a fresh "your booking was approved/rejected" badge
    $stmt = $conn->prepare("UPDATE bookings SET status = ?, notified = 0 WHERE id = ?");
    $stmt->bind_param("si", $status, $booking_id);
    $stmt->execute();
    $stmt->close();

    $response["success"] = true;
    $response["message"] = "Booking updated.";
    $conn->close();
} catch (mysqli_sql_exception $e) {
    $response["message"] = "Database error: " . $e->getMessage();
}

echo json_encode($response);
?>
