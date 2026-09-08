<?php
header('Content-Type: application/json');
require_once '../includes/db.php';

$id = intval($_GET['id'] ?? 0);

$stmt = $conn->prepare("SELECT p.*,
        (SELECT COUNT(*) FROM interested_users iu WHERE iu.property_id = p.id) AS interested_count
        FROM properties p WHERE p.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "Property not found."]);
    exit;
}

$property = $result->fetch_assoc();

// Images
$folder = "../img/properties/" . $property['image_folder'] . "/";
$images = [];
if (is_dir($folder)) {
    $files = array_values(array_diff(scandir($folder), ['.', '..']));
    foreach ($files as $f) {
        $images[] = "img/properties/" . $property['image_folder'] . "/" . $f;
    }
}
$property['images'] = $images;

// Amenities grouped by category
$amStmt = $conn->prepare("SELECT a.name, a.icon, a.category FROM amenities a
                           JOIN property_amenities pa ON a.id = pa.amenity_id
                           WHERE pa.property_id = ?");
$amStmt->bind_param("i", $id);
$amStmt->execute();
$amResult = $amStmt->get_result();

$amenities = [];
while ($row = $amResult->fetch_assoc()) {
    $amenities[$row['category']][] = ['name' => $row['name'], 'icon' => $row['icon']];
}
$property['amenities'] = $amenities;

// Is current user interested?
session_start();
$isInterested = false;
if (isset($_SESSION['user_id'])) {
    $chk = $conn->prepare("SELECT 1 FROM interested_users WHERE user_id = ? AND property_id = ?");
    $chk->bind_param("ii", $_SESSION['user_id'], $id);
    $chk->execute();
    $isInterested = $chk->get_result()->num_rows > 0;
    $chk->close();
}
$property['is_interested'] = $isInterested;

echo json_encode(["success" => true, "property" => $property]);

$stmt->close();
$amStmt->close();
$conn->close();
?>
