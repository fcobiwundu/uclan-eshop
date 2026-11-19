
<?php
session_start();
require_once __DIR__ . '/connect.php';

/*
|--------------------------------------------------------------------------
| Validate product ID
|--------------------------------------------------------------------------
*/

if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    header("Location: products.php");
    exit();
}

$product_id = (int) $_GET['id'];

if ($product_id <= 0) {
    header("Location: products.php");
    exit();
}

/*
|--------------------------------------------------------------------------
| Add product to basket
|--------------------------------------------------------------------------
*/

if (isset($_GET['action']) && $_GET['action'] === 'add') {

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    $stmtProduct = $conn->prepare(
        "SELECT product_title FROM tbl_products WHERE product_id = :id"
    );

    $stmtProduct->bindValue(':id', $product_id, PDO::PARAM_INT);
    $stmtProduct->execute();

    $prod = $stmtProduct->fetch(PDO::FETCH_ASSOC);

    if ($prod) {

        if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]++;
        } else {
            $_SESSION['cart'][$product_id] = 1;
        }

        $_SESSION['cart_message'] =
            $prod['product_title'] . " has been added to your basket.";

    } else {
        $_SESSION['cart_message'] = "Product not found.";
    }

    header("Location: item.php?id=" . $product_id);
    exit();
}

/*
|--------------------------------------------------------------------------
| Retrieve product
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare(
    "SELECT * FROM tbl_products WHERE product_id = :id"
);

$stmt->bindValue(':id', $product_id, PDO::PARAM_INT);
$stmt->execute();

$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    http_response_code(404);
    echo "Product not found.";
    exit();
}

/*
|--------------------------------------------------------------------------
| Review submission
|--------------------------------------------------------------------------
*/

$reviewMessage = "";
$reviewError = "";

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['review_title'], $_POST['review_desc'], $_POST['review_rating'])
) {

    if (!isset($_SESSION['user_id'])) {

        $reviewError = "Please log in to submit a review.";

    } else {

        $review_title = trim($_POST['review_title']);
        $review_desc = trim($_POST['review_desc']);
        $review_rating = filter_var(
            $_POST['review_rating'],
            FILTER_VALIDATE_INT
        );

        if ($review_title === '' || $review_desc === '') {

            $reviewError = "Please complete the review title and description.";

        } elseif (
            $review_rating === false ||
            $review_rating < 1 ||
            $review_rating > 5
        ) {

            $reviewError = "Please select a rating between 1 and 5.";

        } else {

            $user_id = (int) $_SESSION['user_id'];

            $stmtInsert = $conn->prepare(
                "INSERT INTO tbl_reviews
                (user_id, product_id, review_title, review_desc, review_rating)
                VALUES
                (:uid, :pid, :title, :description, :rating)"
            );

            $stmtInsert->execute([
                ':uid' => $user_id,
                ':pid' => $product_id,
                ':title' => $review_title,
                ':description' => $review_desc,
                ':rating' => $review_rating
            ]);

            $reviewMessage = "Your review has been submitted.";
        }
    }
}

/*
|--------------------------------------------------------------------------
| Retrieve basket confirmation message
|--------------------------------------------------------------------------
*/

$cartMessage = "";

if (isset($_SESSION['cart_message'])) {
    $cartMessage = $_SESSION['cart_message'];
    unset($_SESSION['cart_message']);
}

/*
|--------------------------------------------------------------------------
| Retrieve product ratings and reviews
|--------------------------------------------------------------------------
*/

$stmtRatings = $conn->prepare(
    "SELECT review_rating FROM tbl_reviews WHERE product_id = :pid"
);

$stmtRatings->bindValue(':pid', $product_id, PDO::PARAM_INT);
$stmtRatings->execute();

$ratings = $stmtRatings->fetchAll(PDO::FETCH_COLUMN);

$averageRating = 0;
$reviewCount = count($ratings);

if ($reviewCount > 0) {
    $averageRating = array_sum($ratings) / $reviewCount;
}

$stmtReviews = $conn->prepare(
    "SELECT * FROM tbl_reviews
     WHERE product_id = :pid
     ORDER BY review_timestamp DESC"
);

$stmtReviews->bindValue(':pid', $product_id, PDO::PARAM_INT);
$stmtReviews->execute();

$reviews = $stmtReviews->fetchAll(PDO::FETCH_ASSOC);

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

    <title>
        <?php echo htmlspecialchars($product['product_title'], ENT_QUOTES, 'UTF-8'); ?>
        | Student Union Shop
    </title>

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

            <a href="index.php">Home</a>

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

<main class="item-page">

    <!-- Product information -->

    <section class="item-container">

        <?php if (!empty($cartMessage)): ?>

            <div class="notice notice-success" role="status">
                <?php echo htmlspecialchars($cartMessage, ENT_QUOTES, 'UTF-8'); ?>
            </div>

        <?php endif; ?>

        <div class="item-layout">

            <div class="item-image">

                <img
                    src="<?php echo htmlspecialchars($product['product_image'], ENT_QUOTES, 'UTF-8'); ?>"
                    alt="<?php echo htmlspecialchars($product['product_title'], ENT_QUOTES, 'UTF-8'); ?>"
                >

            </div>

            <div class="item-details">

                <p class="product-type">
                    <?php echo htmlspecialchars($product['product_type'], ENT_QUOTES, 'UTF-8'); ?>
                </p>

                <h2>
                    <?php echo htmlspecialchars($product['product_title'], ENT_QUOTES, 'UTF-8'); ?>
                </h2>

                <p class="item-price">
                    £<?php echo number_format((float) $product['product_price'], 2); ?>
                </p>

                <div class="item-description">

                    <h3>Product Description</h3>

                    <p>
                        <?php echo nl2br(htmlspecialchars($product['product_desc'], ENT_QUOTES, 'UTF-8')); ?>
                    </p>

                </div>

                <!-- Product rating summary -->

                <div class="item-rating">

                    <?php if ($reviewCount > 0): ?>

                        <div class="rating-stars" aria-label="<?php echo round($averageRating); ?> out of 5 stars">

                            <?php
                            echo str_repeat("★", (int) round($averageRating));
                            echo str_repeat("☆", 5 - (int) round($averageRating));
                            ?>

                        </div>

                        <p>
                            <strong>
                                <?php echo number_format($averageRating, 1); ?>/5
                            </strong>

                            from

                            <?php echo $reviewCount; ?>

                            <?php echo $reviewCount === 1 ? 'review' : 'reviews'; ?>
                        </p>

                    <?php else: ?>

                        <p>No reviews yet for this product.</p>

                    <?php endif; ?>

                </div>

                <!-- Product actions -->

                <div class="product-buttons item-actions">

                    <a href="products.php" class="btn btn-back">
                        Back to Products
                    </a>

                    <a
                        href="item.php?id=<?php echo $product_id; ?>&action=add"
                        class="btn btn-add"
                    >
                        Add to Basket
                    </a>

                    <a href="cart.php" class="btn btn-cart">
                        View Cart
                    </a>

                </div>

                <?php if (!isset($_SESSION['user_id'])): ?>

                    <p class="item-login-note">
                        Please
                        <a href="login.php">log in</a>
                        to add products to your basket and submit a review.
                    </p>

                <?php endif; ?>

            </div>

        </div>

    </section>

    <!-- Product reviews -->

    <section class="review-section">

        <div class="review-heading">

            <div>
                <h3>Customer Reviews</h3>

                <p>
                    <?php echo $reviewCount; ?>
                    <?php echo $reviewCount === 1 ? 'customer review' : 'customer reviews'; ?>
                </p>
            </div>

            <?php if ($reviewCount > 0): ?>

                <div class="review-average">

                    <strong>
                        <?php echo number_format($averageRating, 1); ?>/5
                    </strong>

                    <span class="rating-stars">
                        <?php
                        echo str_repeat("★", (int) round($averageRating));
                        echo str_repeat("☆", 5 - (int) round($averageRating));
                        ?>
                    </span>

                </div>

            <?php endif; ?>

        </div>

        <?php if (!empty($reviewMessage)): ?>

            <div class="notice notice-success" role="status">

                <?php echo htmlspecialchars($reviewMessage, ENT_QUOTES, 'UTF-8'); ?>

            </div>

        <?php endif; ?>

        <?php if (!empty($reviewError)): ?>

            <div class="notice notice-error" role="alert">

                <?php echo htmlspecialchars($reviewError, ENT_QUOTES, 'UTF-8'); ?>

            </div>

        <?php endif; ?>

        <?php if (!empty($reviews)): ?>

            <div class="reviews-list">

                <?php foreach ($reviews as $rev): ?>

                    <article class="review-card">

                        <div class="review-card-heading">

                            <h4>
                                <?php echo htmlspecialchars($rev['review_title'], ENT_QUOTES, 'UTF-8'); ?>
                            </h4>

                            <span class="review-rating">

                                <?php
                                $rating = (int) $rev['review_rating'];

                                echo str_repeat("★", max(0, min(5, $rating)));
                                echo str_repeat("☆", 5 - max(0, min(5, $rating)));
                                ?>

                            </span>

                        </div>

                        <p class="review-rating-text">
                            Rating:
                            <?php echo $rating; ?>/5
                        </p>

                        <p class="review-description">
                            <?php echo nl2br(htmlspecialchars($rev['review_desc'], ENT_QUOTES, 'UTF-8')); ?>
                        </p>

                    </article>

                <?php endforeach; ?>

            </div>

        <?php else: ?>

            <div class="empty-reviews">

                <h4>No reviews yet</h4>

                <p>Be the first to review this product.</p>

            </div>

        <?php endif; ?>

    </section>

    <!-- Review submission form -->

    <?php if (isset($_SESSION['user_id'])): ?>

        <section class="review-form">

            <h3>Write a Review</h3>

            <p>Share your experience with this product.</p>

            <form method="POST" action="item.php?id=<?php echo $product_id; ?>">

                <div class="form-group">

                    <label for="review_title">
                        Review Title
                    </label>

                    <input
                        type="text"
                        id="review_title"
                        name="review_title"
                        placeholder="Enter a title for your review"
                        required
                        maxlength="255"
                        value="<?php
                            echo isset($_POST['review_title'])
                                ? htmlspecialchars($_POST['review_title'], ENT_QUOTES, 'UTF-8')
                                : '';
                        ?>"
                    >

                </div>

                <div class="form-group">

                    <label for="review_desc">
                        Your Review
                    </label>

                    <textarea
                        id="review_desc"
                        name="review_desc"
                        rows="5"
                        placeholder="Tell us what you think about this product..."
                        required
                    ><?php
                        echo isset($_POST['review_desc'])
                            ? htmlspecialchars($_POST['review_desc'], ENT_QUOTES, 'UTF-8')
                            : '';
                    ?></textarea>

                </div>

                <div class="form-group">

                    <label for="review_rating">
                        Rating
                    </label>

                    <select
                        id="review_rating"
                        name="review_rating"
                        required
                    >

                        <option value="">Select a rating</option>

                        <?php for ($i = 1; $i <= 5; $i++): ?>

                            <option
                                value="<?php echo $i; ?>"
                                <?php
                                echo (
                                    isset($_POST['review_rating']) &&
                                    (int) $_POST['review_rating'] === $i
                                ) ? 'selected' : '';
                                ?>
                            >
                                <?php echo $i; ?>
                                <?php echo $i === 1 ? 'Star' : 'Stars'; ?>
                            </option>

                        <?php endfor; ?>

                    </select>

                </div>

                <button type="submit" class="btn btn-review-submit">
                    Submit Review
                </button>

            </form>

        </section>

    <?php else: ?>

        <section class="review-login-prompt">

            <p>
                Please
                <a href="login.php">log in</a>
                to write a review.
            </p>

        </section>

    <?php endif; ?>

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

            <p>Phone: 01772 89 3000</p>

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
