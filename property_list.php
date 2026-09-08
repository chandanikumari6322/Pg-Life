<?php
require_once 'includes/session_init.php';
$city = isset($_GET['city']) ? trim($_GET['city']) : '';
$pageTitle = $city ? "Best PG's in " . htmlspecialchars($city) : "All PGs | PG Life";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $pageTitle ?></title>

    <link href="css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://use.fontawesome.com/releases/v5.11.2/css/all.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,600;0,700;0,800;1,300;1,400;1,600;1,700;1,800&display=swap" rel="stylesheet" />
    <link href="css/common.css" rel="stylesheet" />
    <link href="css/property_list.css" rel="stylesheet" />
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

    <div id="loading" style="display:none;">
    </div>

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb py-2">
            <li class="breadcrumb-item">
                <a href="index.php">Home</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                <?= $city ? htmlspecialchars($city) : 'All Cities' ?>
            </li>
        </ol>
    </nav>

    <div class="page-container">
        <div class="filter-bar row justify-content-around">
            <div class="col-auto" data-toggle="modal" data-target="#filter-modal">
                <img src="img/filter.png" alt="filter" />
                <span>Filter</span>
            </div>
            <div class="col-auto" id="sort-desc-btn" style="cursor:pointer;">
                <img src="img/desc.png" alt="sort-desc" />
                <span>Highest rent first</span>
            </div>
            <div class="col-auto" id="sort-asc-btn" style="cursor:pointer;">
                <img src="img/asc.png" alt="sort-asc" />
                <span>Lowest rent first</span>
            </div>
        </div>

        <!-- React renders the property cards here (fetched via AJAX from api/get_properties.php) -->
        <div id="react-property-list"
             data-city="<?= htmlspecialchars($city) ?>"></div>

    </div>

    <div class="modal fade" id="filter-modal" tabindex="-1" role="dialog" aria-labelledby="filter-heading" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title" id="filter-heading">Filters</h3>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <h5>Budget (Max Rent)</h5>
                    <hr />
                    <div id="budget-filter-buttons">
                        <button class="btn btn-outline-dark btn-active" data-budget="0">Any</button>
                        <button class="btn btn-outline-dark" data-budget="8000">Up to ₹8,000</button>
                        <button class="btn btn-outline-dark" data-budget="10000">Up to ₹10,000</button>
                        <button class="btn btn-outline-dark" data-budget="12000">Up to ₹12,000</button>
                    </div>

                    <h5 class="mt-4">Gender</h5>
                    <hr />
                    <div id="gender-filter-buttons">
                        <button class="btn btn-outline-dark btn-active" data-gender="all">
                            No Filter
                        </button>
                        <button class="btn btn-outline-dark" data-gender="unisex">
                            <i class="fas fa-venus-mars"></i>Unisex
                        </button>
                        <button class="btn btn-outline-dark" data-gender="male">
                            <i class="fas fa-mars"></i>Male
                        </button>
                        <button class="btn btn-outline-dark" data-gender="female">
                            <i class="fas fa-venus"></i>Female
                        </button>
                    </div>
                </div>

                <div class="modal-footer">
                    <button data-dismiss="modal" class="btn btn-success">Okay</button>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/auth_modals.php'; ?>

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
            <div class="footer-copyright">© 2026 Copyright PG Life </div>
        </div>
    </div>

    <script type="text/javascript" src="js/jquery.js"></script>
    <script type="text/javascript" src="js/bootstrap.min.js"></script>
    <script type="text/javascript" src="js/custom/app.js"></script>

    <!-- React (CDN, no build tools needed) -->
    <script src="https://unpkg.com/react@18/umd/react.production.min.js"></script>
    <script src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js"></script>
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
    <script type="text/babel" src="react/PropertyList.jsx"></script>

    <script>
        // Budget filter buttons inside the filter modal
        $('#budget-filter-buttons button').on('click', function () {
            $('#budget-filter-buttons button').removeClass('btn-active');
            $(this).addClass('btn-active');
            const budget = $(this).data('budget');
            window.dispatchEvent(new CustomEvent('pglife:filterBudget', { detail: budget }));
        });

        // Gender filter buttons inside the filter modal
        $('#gender-filter-buttons button').on('click', function () {
            $('#gender-filter-buttons button').removeClass('btn-active');
            $(this).addClass('btn-active');
            const gender = $(this).data('gender');
            window.dispatchEvent(new CustomEvent('pglife:filterGender', { detail: gender }));
        });

        // Sort buttons
        $('#sort-desc-btn').on('click', function () {
            window.dispatchEvent(new CustomEvent('pglife:sort', { detail: 'desc' }));
        });
        $('#sort-asc-btn').on('click', function () {
            window.dispatchEvent(new CustomEvent('pglife:sort', { detail: 'asc' }));
        });
    </script>

</body>

</html>
