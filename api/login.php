<?php
session_start();
header('Content-Type: application/json');
require_once '../includes/db.php';

$response = ["success" => false, "message" => ""];

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (!$email || !$password) {
    $response["message"] = "Please enter email and password.";
    echo json_encode($response);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT id, full_name, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $response["success"] = true;
            $response["message"] = "Welcome back, " . $user['full_name'] . "!";
        } else {
            $response["message"] = "Incorrect password.";
        }
    } else {
        $response["message"] = "No account found with this email.";
    }

    $stmt->close();
    $conn->close();
} catch (mysqli_sql_exception $e) {
    $response["message"] = "Database error: " . $e->getMessage();
}

echo json_encode($response);
?>
