
<?php
session_start();
require_once __DIR__ . '/connect.php';

/*
|--------------------------------------------------------------------------
| Retrieve current offers
|--------------------------------------------------------------------------
*/

$stmtOffers = $conn->query(
    "SELECT offer_title, offer_dec FROM tbl_offers"
);

$offers = $stmtOffers->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| Basket count
|--------------------------------------------------------------------------
*/

$cartCount = 0;

if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $cartCount = array_sum($_SESSION['cart']);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta
        name="description"
        content="Browse the Student Union Shop collection of hoodies, jumpers and T-shirts."
    >

    <title>Student Union Shop | Home</title>

    <link rel="stylesheet" href="css/style.css">

</head>

<body>

<header>

    <div class="header-container">

        <a href="index.php" class="logo-title">

            <img
                src="resources/images/logos/logo.svg"
                alt="Student Union Logo"
            >

            <h1>Student Union Shop</h1>

        </a>

        <nav class="nav-links" id="nav-links">

            <a href="index.php" class="selected">Home</a>

            <a href="products.php">Products</a>

            <a href="cart.php">

                Cart

                <?php if ($cartCount > 0): ?>

                    <span class="cart-count">
                        <?php echo $cartCount; ?>
                    </span>

                <?php endif; ?>

            </a>

            <?php if (isset($_SESSION['user_name'])): ?>

                <a href="logout.php">Logout</a>

            <?php else: ?>

                

                <a href="login.php">Login</a>

            <?php endif; ?>

        </nav>

        <button
            class="burger-menu"
            id="burger-menu"
            type="button"
            aria-label="Open navigation"
            aria-expanded="false"
            aria-controls="nav-links"
        >
            <span></span>
            <span></span>
            <span></span>
        </button>

    </div>

</header>

<main class="home-page">

    <!-- Welcome section -->

    <section class="home-hero">

        <div class="home-hero-content">

            <?php if (isset($_SESSION['user_name'])): ?>

                <h2>
                    Welcome back,
                    <?php echo htmlspecialchars($_SESSION['user_name'], ENT_QUOTES, 'UTF-8'); ?>!
                </h2>

                <p>
                    Explore the Student Union Shop collection.
                </p>

            <?php else: ?>

                <h2>
                    Welcome to the Student Union Shop
                </h2>

                <p>
                    Explore our collection of hoodies, jumpers and T-shirts designed for students.
                </p>

            <?php endif; ?>

            <div class="home-actions">

                <a href="products.php" class="btn btn-shop">
                    Browse Products
                </a>

                <?php if (!isset($_SESSION['user_id'])): ?>

                    <a href="register.php" class="btn btn-register">
                        Create an Account
                    </a>

                <?php endif; ?>

            </div>

        </div>

    </section>

    <!-- Current offers -->

    <section class="offers-section">

        <div class="section-heading">

            <h2>Current Offers</h2>

            <p>
                View the offers currently available in the Student Union Shop.
            </p>

        </div>

        <?php if (!empty($offers)): ?>

            <div class="offers-container">

                <?php foreach ($offers as $offer): ?>

                    <article class="offer-card">

                        <div class="offer-card-icon" aria-hidden="true">
                            %
                        </div>

                        <div class="offer-card-content">

                            <h3>
                                <?php echo htmlspecialchars($offer['offer_title'], ENT_QUOTES, 'UTF-8'); ?>
                            </h3>

                            <p>
                                <?php echo htmlspecialchars($offer['offer_dec'], ENT_QUOTES, 'UTF-8'); ?>
                            </p>

                        </div>

                    </article>

                <?php endforeach; ?>

            </div>

        <?php else: ?>

            <div class="no-offers">

                <p>
                    There are currently no offers available.
                </p>

            </div>

        <?php endif; ?>

        <div class="offers-action">

            <a href="products.php" class="btn btn-details">
                View Products
            </a>

        </div>

    </section>

</main>

<footer>

    <div class="footer">

        <div class="info">

            <h3>Links</h3>

            <p>
                <a href="https://www.uclansu.co.uk/">
                    Students' Union
                </a>
            </p>

        </div>

        <div class="info">

            <h3>Contact</h3>

            <p>
                Email:
                <a href="mailto:suinformation@uclan.ac.uk">
                    suinformation@uclan.ac.uk
                </a>
            </p>

            <p>
                Phone: 01772 89 3000
            </p>

        </div>

        <div class="info">

            <h3>Location</h3>

            <p>
                University of Central Lancashire Students' Union.<br>
                Fylde Road, Preston. PR1 7BY<br>
                Registered in England<br>
                Company Number: 7623917<br>
                Registered Charity Number: 11426616
            </p>

        </div>

    </div>

    <span>
        &copy; 2024 Student Union Shop. All rights reserved.
    </span>

</footer>

<script src="js/script.js"></script>

</body>
</html>
