<?php
// Database connection settings
$host = "localhost";
$dbuser = "root";
$dbpass = "";
$dbname = "pglife";

// Make MySQL errors throw exceptions with real messages instead of failing silently
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($host, $dbuser, $dbpass, $dbname);
    $conn->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    die(json_encode([
        "success" => false,
        "message" => "Database error: " . $e->getMessage() . " — did you import pglife.sql and start MySQL in XAMPP?"
    ]));
}
?>
