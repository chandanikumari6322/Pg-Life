<?php
header('Content-Type: application/json');
require_once '../includes/db.php';

$city = $_GET['city'] ?? '';
$gender = $_GET['gender'] ?? '';
$budget = intval($_GET['budget'] ?? 0);
$sort = $_GET['sort'] ?? ''; // 'asc' or 'desc' by price

$sql = "SELECT p.*,
        (SELECT COUNT(*) FROM interested_users iu WHERE iu.property_id = p.id) AS interested_count
        FROM properties p WHERE 1=1";
$params = [];
$types = "";

if (!empty($city)) {
    $sql .= " AND p.city = ?";
    $params[] = $city;
    $types .= "s";
}
if (!empty($gender) && $gender !== 'all') {
    $sql .= " AND p.gender = ?";
    $params[] = $gender;
    $types .= "s";
}
if ($budget > 0) {
    $sql .= " AND p.price <= ?";
    $params[] = $budget;
    $types .= "i";
}

if ($sort === 'asc') {
    $sql .= " ORDER BY p.price ASC";
} elseif ($sort === 'desc') {
    $sql .= " ORDER BY p.price DESC";
} else {
    $sql .= " ORDER BY p.id ASC";
}

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$properties = [];
while ($row = $result->fetch_assoc()) {
    // Find first image inside img/properties/<id>/
    $folder = "../img/properties/" . $row['image_folder'] . "/";
    $firstImage = "img/logo.png"; // fallback
    if (is_dir($folder)) {
        $files = array_values(array_diff(scandir($folder), ['.', '..']));
        if (!empty($files)) {
            $firstImage = "img/properties/" . $row['image_folder'] . "/" . $files[0];
        }
    }
    $row['thumbnail'] = $firstImage;
    $properties[] = $row;
}

echo json_encode(["success" => true, "properties" => $properties]);

$stmt->close();
$conn->close();
?>
