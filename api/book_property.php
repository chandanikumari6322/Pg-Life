<?php
session_start();
header('Content-Type: application/json');
require_once '../includes/db.php';

$response = ["success" => false, "message" => "", "booked" => false];

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

try {
    // Check if this user already has a pending/confirmed booking for this property
    $check = $conn->prepare("SELECT id, status FROM bookings WHERE user_id = ? AND property_id = ? AND status != 'cancelled'");
    $check->bind_param("ii", $user_id, $property_id);
    $check->execute();
    $existing = $check->get_result()->fetch_assoc();
    $check->close();

    if ($existing) {
        $response["success"] = true;
        $response["booked"] = true;
        $response["message"] = "You already have a " . $existing['status'] . " booking for this property.";
    } else {
        $ins = $conn->prepare("INSERT INTO bookings (user_id, property_id, status) VALUES (?, ?, 'pending')");
        $ins->bind_param("ii", $user_id, $property_id);
        $ins->execute();
        $ins->close();

        $response["success"] = true;
        $response["booked"] = true;
        $response["message"] = "Booking request sent! The PG owner will confirm shortly.";
    }

    $conn->close();
} catch (mysqli_sql_exception $e) {
    $response["message"] = "Database error: " . $e->getMessage() . " — did you re-import pglife.sql to get the bookings table?";
}

echo json_encode($response);
?>
