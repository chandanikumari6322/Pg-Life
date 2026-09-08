<?php
session_start();
header('Content-Type: application/json');
require_once '../includes/db.php';

$response = ["success" => false, "message" => ""];

$full_name = trim($_POST['full_name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$college_name = trim($_POST['college_name'] ?? '');
$gender = $_POST['gender'] ?? '';

if (!$full_name || !$phone || !$email || !$password || !$college_name || !$gender) {
    $response["message"] = "Please fill all fields.";
    echo json_encode($response);
    exit;
}

if (strlen($password) < 6) {
    $response["message"] = "Password must be at least 6 characters.";
    echo json_encode($response);
    exit;
}

try {
    // Check for existing email
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $response["message"] = "This email is already registered. Please login instead.";
        echo json_encode($response);
        exit;
    }
    $check->close();

    $hashed = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, phone, college_name, gender) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $full_name, $email, $hashed, $phone, $college_name, $gender);
    $stmt->execute();

    // Auto-login after signup
    $_SESSION['user_id'] = $stmt->insert_id;
    $_SESSION['user_name'] = $full_name;

    $response["success"] = true;
    $response["message"] = "Account created successfully! Welcome, $full_name.";

    $stmt->close();
    $conn->close();
} catch (mysqli_sql_exception $e) {
    $response["message"] = "Database error: " . $e->getMessage();
}

echo json_encode($response);
?>
