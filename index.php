<?php
require_once 'includes/session_init.php';
require_once 'includes/db.php';

$featured = [];
try {
    $res = $conn->query("SELECT p.*,
            (SELECT COUNT(*) FROM interested_users iu WHERE iu.property_id = p.id) AS interested_count
            FROM properties p ORDER BY p.rating DESC LIMIT 3");
    while ($row = $res->fetch_assoc()) {
        $folder = "img/properties/" . $row['image_folder'] . "/";
        $thumb = "img/logo.png";
        if (is_dir($folder)) {
            $files = array_values(array_diff(scandir($folder), ['.', '..']));
            if (!empty($files)) $thumb = $folder . $files[0];
        }
        $row['thumb'] = $thumb;
        $featured[] = $row;
    }
} catch (mysqli_sql_exception $e) {
    // DB not set up yet - homepage still works, featured section just won't show
}

function homeStars($rating) {
    $full = floor($rating);
    $half = ($rating - $full) >= 0.5;
    $html = '';
    for ($i = 0; $i < $full; $i++) $html .= '<i class="fas fa-star"></i>';
    if ($half) $html .= '<i class="fas fa-star-half-alt"></i>';
    $remaining = 5 - $full - ($half ? 1 : 0);
    for ($i = 0; $i < $remaining; $i++) $html .= '<i class="far fa-star"></i>';
    return $html;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PG Life - Find Your Perfect PG</title>

    <link href="css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://use.fontawesome.com/releases/v5.11.2/css/all.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet" />
    <link href="css/common.css" rel="stylesheet" />
    <link href="css/home.css" rel="stylesheet" />
    <link href="css/property_list.css" rel="stylesheet" />
</head>

<body>

    <!-- Header -->
    <div class="header sticky-top">
        <nav class="navbar navbar-expand-md navbar-light">

            <a class="navbar-brand" href="index.php">
                <img src="img/logo.png" />
            </a>

            <button class="navbar-toggler" type="button"
                data-toggle="collapse" data-target="#my-navbar">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse justify-content-end" id="my-navbar">
                <ul class="navbar-nav">

                    <li class="nav-item">
                        <a class="nav-link" href="#cities">
                            <i class="fas fa-city"></i> Cities
                        </a>
                    </li>

                    <div class="nav-vl"></div>

                    <?php include 'includes/nav_state.php'; ?>

                </ul>
            </div>

        </nav>
    </div>


    <!-- Hero Section -->
    <div class="hero-section">

        <div class="hero-content">

            <h1>Find Your Perfect PG</h1>

            <p>Comfortable, affordable and convenient accommodation</p>

            <div class="search-box">

                <div class="input-group">
                    <input type="text" id="city-search" class="form-control"
                        placeholder="Search for a city">

                    <div class="input-group-append">
                        <a href="#" id="search-btn"
                            class="btn btn-primary">
                            <i class="fas fa-search"></i> Search
                        </a>
                    </div>
                </div>

            </div>

        </div>

    </div>


    <!-- Cities -->
    <div class="cities-section" id="cities">

        <div class="page-container">

            <h2>Popular Cities</h2>

            <div class="row justify-content-center">

                <div class="col-6 col-md-3 city-card">
                    <a href="property_list.php?city=Delhi">
                        <img src="img/delhi.png" />
                        <h5>Delhi</h5>
                    </a>
                </div>

                <div class="col-6 col-md-3 city-card">
                    <a href="property_list.php?city=Mumbai">
                        <img src="img/mumbai.png" />
                        <h5>Mumbai</h5>
                    </a>
                </div>

                <div class="col-6 col-md-3 city-card">
                    <a href="property_list.php?city=Bangalore">
                        <img src="img/bangalore.png" />
                        <h5>Bangalore</h5>
                    </a>
                </div>

                <div class="col-6 col-md-3 city-card">
                    <a href="property_list.php?city=Hyderabad">
                        <img src="img/hyderabad.png" />
                        <h5>Hyderabad</h5>
                    </a>
                </div>

            </div>

        </div>

    </div>


    <!-- Featured Properties -->
    <?php if (!empty($featured)): ?>
    <div class="cities-section">
        <div class="page-container">
            <h2>Top Rated PGs</h2>
            <?php foreach ($featured as $p):
                $genderIcon = $p['gender'] === 'male' ? 'img/male.png' : ($p['gender'] === 'female' ? 'img/female.png' : 'img/unisex.png');
            ?>
                <div class="property-card row">
                    <div class="image-container col-md-4">
                        <img src="<?= htmlspecialchars($p['thumb']) ?>" />
                    </div>
                    <div class="content-container col-md-8">
                        <div class="row no-gutters justify-content-between">
                            <div class="star-container" title="<?= $p['rating'] ?>">
                                <?= homeStars($p['rating']) ?>
                            </div>
                            <div class="interested-container">
                                <i class="far fa-heart"></i>
                                <div class="interested-text"><?= $p['interested_count'] ?> interested</div>
                            </div>
                        </div>
                        <div class="detail-container">
                            <div class="property-name"><?= htmlspecialchars($p['name']) ?></div>
                            <div class="property-address"><?= htmlspecialchars($p['address']) ?></div>
                            <div class="property-gender">
                                <img src="<?= $genderIcon ?>" />
                            </div>
                        </div>
                        <div class="row no-gutters">
                            <div class="rent-container col-6">
                                <div class="rent">Rs <?= number_format($p['price']) ?>/-</div>
                                <div class="rent-unit">per month</div>
                            </div>
                            <div class="button-container col-6">
                                <a href="property_detail.php?id=<?= $p['id'] ?>" class="btn btn-primary">View</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Signup / Login modals (reused across site) -->
    <?php include 'includes/auth_modals.php'; ?>


    <!-- Footer -->
    <div class="footer">
        <div class="page-container footer-container">

            <div class="footer-cities">

                <div class="footer-city">
                    <a href="property_list.php?city=Delhi">PG in Delhi</a>
                </div>

                <div class="footer-city">
                    <a href="property_list.php?city=Mumbai">PG in Mumbai</a>
                </div>

                <div class="footer-city">
                    <a href="property_list.php?city=Bangalore">PG in Bangalore</a>
                </div>

                <div class="footer-city">
                    <a href="property_list.php?city=Hyderabad">PG in Hyderabad</a>
                </div>

            </div>

            <div class="footer-copyright">
                © 2026 Copyright PG Life
            </div>

        </div>
    </div>


    <!-- JavaScript -->
    <script type="text/javascript" src="js/jquery.js"></script>
    <script type="text/javascript" src="js/bootstrap.min.js"></script>
    <script type="text/javascript" src="js/custom/app.js"></script>
    <script>
        $('#search-btn').on('click', function () {
            const city = $('#city-search').val().trim();
            window.location.href = 'property_list.php' + (city ? '?city=' + encodeURIComponent(city) : '');
        });
    </script>

</body>
</html>
