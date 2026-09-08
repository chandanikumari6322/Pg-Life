<?php
require_once 'includes/session_init.php';
require_once 'includes/db.php';

$id = intval($_GET['id'] ?? 0);

$stmt = $conn->prepare("SELECT p.*,
        (SELECT COUNT(*) FROM interested_users iu WHERE iu.property_id = p.id) AS interested_count
        FROM properties p WHERE p.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<h3 style='text-align:center;margin-top:50px;'>Property not found. <a href='index.php'>Go back home</a></h3>";
    exit;
}
$property = $result->fetch_assoc();

// Images
$folder = "img/properties/" . $property['image_folder'] . "/";
$images = [];
if (is_dir($folder)) {
    $files = array_values(array_diff(scandir($folder), ['.', '..']));
    foreach ($files as $f) {
        $images[] = $folder . $f;
    }
}
if (empty($images)) $images[] = "img/logo.png";

// Amenities grouped by category
$amStmt = $conn->prepare("SELECT a.name, a.icon, a.category FROM amenities a
                           JOIN property_amenities pa ON a.id = pa.amenity_id
                           WHERE pa.property_id = ?
                           ORDER BY a.category");
$amStmt->bind_param("i", $id);
$amStmt->execute();
$amResult = $amStmt->get_result();
$amenitiesByCategory = [];
while ($row = $amResult->fetch_assoc()) {
    $amenitiesByCategory[$row['category']][] = $row;
}

// Is current user interested?
$isInterested = false;
$bookingStatus = null;
if (isset($_SESSION['user_id'])) {
    $chk = $conn->prepare("SELECT 1 FROM interested_users WHERE user_id = ? AND property_id = ?");
    $chk->bind_param("ii", $_SESSION['user_id'], $id);
    $chk->execute();
    $isInterested = $chk->get_result()->num_rows > 0;
    $chk->close();

    $bkStmt = $conn->prepare("SELECT status FROM bookings WHERE user_id = ? AND property_id = ? AND status != 'cancelled'");
    $bkStmt->bind_param("ii", $_SESSION['user_id'], $id);
    $bkStmt->execute();
    $bkRow = $bkStmt->get_result()->fetch_assoc();
    if ($bkRow) $bookingStatus = $bkRow['status'];
    $bkStmt->close();
}

function starsHtml($rating) {
    $full = floor($rating);
    $half = ($rating - $full) >= 0.5;
    $html = '';
    for ($i = 0; $i < $full; $i++) $html .= '<i class="fas fa-star"></i>';
    if ($half) $html .= '<i class="fas fa-star-half-alt"></i>';
    $remaining = 5 - $full - ($half ? 1 : 0);
    for ($i = 0; $i < $remaining; $i++) $html .= '<i class="far fa-star"></i>';
    return $html;
}

$genderIcon = $property['gender'] === 'male' ? 'img/male.png' : ($property['gender'] === 'female' ? 'img/female.png' : 'img/unisex.png');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($property['name']) ?> | PG Life</title>

    <link href="css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://use.fontawesome.com/releases/v5.11.2/css/all.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,600;0,700;0,800;1,300;1,400;1,600;1,700;1,800&display=swap" rel="stylesheet" />
    <link href="css/common.css" rel="stylesheet" />
    <link href="css/property_detail.css" rel="stylesheet" />
</head>

<body>
    <div class="header sticky-top">
        <nav class="navbar navbar-expand-md navbar-light">
            <a class="navbar-brand" href="index.php">
                <img src="img/logo.png" />
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#my-navbar">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse justify-content-end" id="my-navbar">
                <ul class="navbar-nav">
                    <?php include 'includes/nav_state.php'; ?>
                </ul>
            </div>
        </nav>
    </div>

    <div id="loading" style="display:none;"></div>

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb py-2">
            <li class="breadcrumb-item">
                <a href="index.php">Home</a>
            </li>
            <li class="breadcrumb-item">
                <a href="property_list.php?city=<?= urlencode($property['city']) ?>"><?= htmlspecialchars($property['city']) ?></a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                <?= htmlspecialchars($property['name']) ?>
            </li>
        </ol>
    </nav>

    <div id="property-images" class="carousel slide" data-ride="carousel">
        <ol class="carousel-indicators">
            <?php foreach ($images as $i => $img): ?>
                <li data-target="#property-images" data-slide-to="<?= $i ?>" class="<?= $i === 0 ? 'active' : '' ?>"></li>
            <?php endforeach; ?>
        </ol>
        <div class="carousel-inner">
            <?php foreach ($images as $i => $img): ?>
                <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
                    <img class="d-block w-100" src="<?= htmlspecialchars($img) ?>" alt="slide">
                </div>
            <?php endforeach; ?>
        </div>
        <a class="carousel-control-prev" href="#property-images" role="button" data-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="sr-only">Previous</span>
        </a>
        <a class="carousel-control-next" href="#property-images" role="button" data-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="sr-only">Next</span>
        </a>
    </div>

    <div class="property-summary page-container">
        <div class="row no-gutters justify-content-between">
            <div class="star-container" title="<?= $property['rating'] ?>">
                <?= starsHtml($property['rating']) ?>
            </div>
            <div class="interested-container">
                <i id="interestBtn" data-id="<?= $property['id'] ?>"
                   class="is-interested-image <?= $isInterested ? 'fas' : 'far' ?> fa-heart"></i>
                <div class="interested-text">
                    <span id="interestedCount" class="interested-user-count"><?= $property['interested_count'] ?></span> interested
                </div>
            </div>
        </div>
        <div class="detail-container">
            <div class="property-name"><?= htmlspecialchars($property['name']) ?></div>
            <div class="property-address"><?= htmlspecialchars($property['address']) ?></div>
            <div class="property-gender">
                <img src="<?= $genderIcon ?>" />
            </div>
        </div>
        <div class="row no-gutters">
            <div class="rent-container col-6">
                <div class="rent">Rs <?= number_format($property['price']) ?>/-</div>
                <div class="rent-unit">per month</div>
            </div>
            <div class="button-container col-6">
                <button id="bookBtn" data-id="<?= $property['id'] ?>"
                        class="btn <?= $bookingStatus ? 'btn-secondary' : 'btn-primary' ?>"
                        <?= $bookingStatus ? 'disabled' : '' ?>>
                    <?= $bookingStatus ? ucfirst($bookingStatus) : 'Book Now' ?>
                </button>
            </div>
        </div>
    </div>

    <div class="property-amenities">
        <div class="page-container">
            <h1>Amenities</h1>
            <div class="row justify-content-between">
                <?php foreach ($amenitiesByCategory as $category => $items): ?>
                    <div class="col-md-auto">
                        <h5><?= htmlspecialchars($category) ?></h5>
                        <?php foreach ($items as $item): ?>
                            <div class="amenity-container">
                                <img src="<?= htmlspecialchars($item['icon']) ?>">
                                <span><?= htmlspecialchars($item['name']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="property-about page-container">
        <h1>About the Property</h1>
        <p><?= nl2br(htmlspecialchars($property['description'])) ?></p>
    </div>

    <div class="property-testimonials page-container">
        <h1>What people say</h1>
        <div class="testimonial-block">
            <div class="testimonial-image-container">
                <img class="testimonial-img" src="img/man.png">
            </div>
            <div class="testimonial-text">
                <i class="fa fa-quote-left" aria-hidden="true"></i>
                <p>You just have to arrive at the place, it's fully furnished and stocked with all basic amenities and services and even your friends are welcome.</p>
            </div>
            <div class="testimonial-name">- Ashutosh Gowariker</div>
        </div>
        <div class="testimonial-block">
            <div class="testimonial-image-container">
                <img class="testimonial-img" src="img/man.png">
            </div>
            <div class="testimonial-text">
                <i class="fa fa-quote-left" aria-hidden="true"></i>
                <p>You just have to arrive at the place, it's fully furnished and stocked with all basic amenities and services and even your friends are welcome.</p>
            </div>
            <div class="testimonial-name">- Karan Johar</div>
        </div>
    </div>

    <?php include 'includes/auth_modals.php'; ?>

    <div class="footer">
        <div class="page-container footer-container">
            <div class="footer-cities">
                <div class="footer-city"><a href="property_list.php?city=Delhi">PG in Delhi</a></div>
                <div class="footer-city"><a href="property_list.php?city=Mumbai">PG in Mumbai</a></div>
                <div class="footer-city"><a href="property_list.php?city=Bangalore">PG in Bangalore</a></div>
                <div class="footer-city"><a href="property_list.php?city=Hyderabad">PG in Hyderabad</a></div>
            </div>
            <div class="footer-copyright">© 2026 Copyright PG Life </div>
        </div>
    </div>

    <script type="text/javascript" src="js/jquery.js"></script>
    <script type="text/javascript" src="js/bootstrap.min.js"></script>
    <script type="text/javascript" src="js/custom/app.js"></script>
    <script type="text/javascript" src="js/custom/details.js"></script>
</body>

</html>
<?php
$stmt->close();
$amStmt->close();
$conn->close();
?>
