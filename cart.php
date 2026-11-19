
<?php
session_start();
require_once __DIR__ . '/connect.php';

/*
|--------------------------------------------------------------------------
| Initialise basket
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

/*
|--------------------------------------------------------------------------
| Process basket actions
|--------------------------------------------------------------------------
*/

if (isset($_GET['action'])) {

    // Empty basket.
    if ($_GET['action'] === 'empty') {

        $_SESSION['cart'] = [];
        $_SESSION['cart_message'] = "Your basket has been emptied.";

        header("Location: cart.php");
        exit();
    }

    // Checkout.
    elseif ($_GET['action'] === 'checkout') {

        if (!isset($_SESSION['user_id'])) {
            header("Location: login.php");
            exit();
        }

        if (empty($_SESSION['cart'])) {
            $_SESSION['cart_message'] = "Your basket is empty.";
            header("Location: cart.php");
            exit();
        }

        $product_ids = json_encode($_SESSION['cart']);

        $stmtOrder = $conn->prepare(
            "INSERT INTO tbl_orders (user_id, product_ids)
             VALUES (:user_id, :product_ids)"
        );

        $stmtOrder->bindValue(
            ':user_id',
            (int) $_SESSION['user_id'],
            PDO::PARAM_INT
        );

        $stmtOrder->bindValue(
            ':product_ids',
            $product_ids,
            PDO::PARAM_STR
        );

        if ($stmtOrder->execute()) {

            $_SESSION['cart_message'] =
                "Your order has been placed successfully.";

            $_SESSION['cart'] = [];

        } else {

            $_SESSION['cart_message'] =
                "There was an error processing your order.";
        }

        header("Location: cart.php");
        exit();
    }
}

/*
|--------------------------------------------------------------------------
| Retrieve basket message
|--------------------------------------------------------------------------
*/

$cartMessage = "";

if (isset($_SESSION['cart_message'])) {

    $cartMessage = $_SESSION['cart_message'];

    unset($_SESSION['cart_message']);
}

/*
|--------------------------------------------------------------------------
| Retrieve basket products
|--------------------------------------------------------------------------
*/

$cartItems = [];
$grandTotal = 0;
$totalQuantity = 0;

foreach ($_SESSION['cart'] as $id => $quantity) {

    $productId = (int) $id;
    $quantity = (int) $quantity;

    if ($productId <= 0 || $quantity <= 0) {
        continue;
    }

    $stmt = $conn->prepare(
        "SELECT * FROM tbl_products WHERE product_id = :id"
    );

    $stmt->bindValue(':id', $productId, PDO::PARAM_INT);
    $stmt->execute();

    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {

        $price = (float) $product['product_price'];
        $lineTotal = $price * $quantity;

        $product['cart_quantity'] = $quantity;
        $product['cart_line_total'] = $lineTotal;

        $cartItems[] = $product;

        $grandTotal += $lineTotal;
        $totalQuantity += $quantity;
    }
}

/*
|--------------------------------------------------------------------------
| Basket count for navigation
|--------------------------------------------------------------------------
*/

$cartCount = $totalQuantity;

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Your Cart | Student Union Shop</title>

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

            <a href="cart.php" class="selected">
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

<main class="cart-page">

    <section class="page-heading">

        <h2>Your Shopping Basket</h2>

        <p>
            Review the products you have selected.
        </p>

    </section>

    <?php if (!empty($cartMessage)): ?>

        <div class="notice notice-success" role="status">

            <?php echo htmlspecialchars($cartMessage, ENT_QUOTES, 'UTF-8'); ?>

        </div>

    <?php endif; ?>

    <?php if (!isset($_SESSION['user_id'])): ?>

        <section class="empty-cart">

            <h3>Log in to access your basket</h3>

            <p>
                Please log in to view your basket and continue with checkout.
            </p>

            <div class="cart-actions">

                <a href="login.php" class="btn btn-checkout">
                    Login
                </a>

                <a href="register.php" class="btn btn-details">
                    Create Account
                </a>

            </div>

        </section>

    <?php elseif (empty($cartItems)): ?>

        <section class="empty-cart">

            <div class="empty-cart-icon" aria-hidden="true">
                🛒
            </div>

            <h3>Your basket is empty</h3>

            <p>
                You have not added any products to your basket yet.
            </p>

            <a href="products.php" class="btn btn-continue">
                Browse Products
            </a>

        </section>

    <?php else: ?>

        <section class="cart-content">

            <div class="cart-summary-heading">

                <h3>Basket Items</h3>

                <p>
                    <?php echo $totalQuantity; ?>
                    <?php echo $totalQuantity === 1 ? 'item' : 'items'; ?>
                    in your basket
                </p>

            </div>

            <div class="cart-container">

                <?php foreach ($cartItems as $product): ?>

                    <article class="cart-item">

                        <div class="cart-item-image">

                            <img
                                src="<?php echo htmlspecialchars($product['product_image'], ENT_QUOTES, 'UTF-8'); ?>"
                                alt="<?php echo htmlspecialchars($product['product_title'], ENT_QUOTES, 'UTF-8'); ?>"
                                loading="lazy"
                            >

                        </div>

                        <div class="cart-item-details">

                            <h3>
                                <?php echo htmlspecialchars($product['product_title'], ENT_QUOTES, 'UTF-8'); ?>
                            </h3>

                            <p class="product-type">
                                <?php echo htmlspecialchars($product['product_type'], ENT_QUOTES, 'UTF-8'); ?>
                            </p>

                            <p>
                                Unit Price:
                                <strong>
                                    £<?php echo number_format((float) $product['product_price'], 2); ?>
                                </strong>
                            </p>

                            <p class="cart-quantity">
                                Quantity:
                                <strong>
                                    <?php echo $product['cart_quantity']; ?>
                                </strong>
                            </p>

                            <p class="cart-line-total">
                                Item Total:
                                <strong>
                                    £<?php echo number_format($product['cart_line_total'], 2); ?>
                                </strong>
                            </p>

                            <a
                                href="item.php?id=<?php echo (int) $product['product_id']; ?>"
                                class="btn btn-details"
                            >
                                View Product
                            </a>

                        </div>

                    </article>

                <?php endforeach; ?>

            </div>

            <section class="cart-total-panel">

                <div class="cart-total-row">

                    <span>Total Items</span>

                    <strong>
                        <?php echo $totalQuantity; ?>
                    </strong>

                </div>

                <div class="cart-total-row cart-grand-total">

                    <span>Grand Total</span>

                    <strong>
                        £<?php echo number_format($grandTotal, 2); ?>
                    </strong>

                </div>

                <p class="cart-total-note">
                    Your order total is calculated using the current product prices.
                </p>

            </section>

            <div class="cart-actions">

                <a href="products.php" class="btn btn-continue">
                    Continue Shopping
                </a>

                <a
                    href="cart.php?action=checkout"
                    class="btn btn-checkout"
                    onclick="return confirm('Are you sure you want to place this order?');"
                >
                    Proceed to Checkout
                </a>

                <a
                    href="cart.php?action=empty"
                    class="btn btn-empty"
                    onclick="return confirm('Are you sure you want to empty your basket?');"
                >
                    Empty Cart
                </a>

            </div>

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
