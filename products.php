
<?php
session_start();
require_once __DIR__ . '/connect.php';

$message = "";

/*
|--------------------------------------------------------------------------
| Add product to basket
|--------------------------------------------------------------------------
*/
if (
    isset($_GET['action'], $_GET['id']) &&
    $_GET['action'] === 'add'
) {
    // Customers must be logged in to add products.
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    $product_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);

    if ($product_id === false || $product_id <= 0) {
        $message = "Invalid product.";
    } else {
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

            // Add the product or increase its existing quantity.
            if (isset($_SESSION['cart'][$product_id])) {
                $_SESSION['cart'][$product_id]++;
            } else {
                $_SESSION['cart'][$product_id] = 1;
            }

            $_SESSION['cart_message'] =
                $prod['product_title'] . " has been added to your basket.";

            // Prevent the product being added again on refresh.
            header("Location: products.php?" . http_build_query([
                'filter' => $_GET['filter'] ?? 'all',
                'search' => $_GET['search'] ?? ''
            ]));
            exit();
        } else {
            $message = "Product not found.";
        }
    }
}

/*
|--------------------------------------------------------------------------
| Product filtering and search
|--------------------------------------------------------------------------
*/

// Only accept the available category filters.
$allowedFilters = ['all', 'tshirt', 'jumper', 'hoodie'];

$filter = $_GET['filter'] ?? 'all';

if (!in_array($filter, $allowedFilters, true)) {
    $filter = 'all';
}

$search = trim($_GET['search'] ?? '');

$sql = "SELECT * FROM tbl_products";
$where = [];
$params = [];

// Category filter.
if ($filter === 'hoodie') {
    $where[] = "product_type LIKE :type";
    $params[':type'] = '%Hoodie%';

} elseif ($filter === 'jumper') {
    $where[] = "product_type LIKE :type";
    $params[':type'] = '%Jumper%';

} elseif ($filter === 'tshirt') {
    $where[] = "product_type LIKE :type";
    $params[':type'] = '%Tshirt%';
}

// Search product title and description.
if ($search !== '') {
    $where[] = "(
        product_title LIKE :title_search
        OR product_desc LIKE :desc_search
    )";

    $searchTerm = '%' . $search . '%';

    $params[':title_search'] = $searchTerm;
    $params[':desc_search'] = $searchTerm;
}

// Add WHERE conditions when required.
if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY product_id ASC";

$stmt = $conn->prepare($sql);

foreach ($params as $parameter => $value) {
    $stmt->bindValue($parameter, $value, PDO::PARAM_STR);
}

$stmt->execute();

$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Retrieve and clear the basket confirmation message.
if (isset($_SESSION['cart_message'])) {
    $message = $_SESSION['cart_message'];
    unset($_SESSION['cart_message']);
}

// Count basket items.
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

    <title>Products | Student Union Shop</title>

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
            <a href="products.php" class="selected">Products</a>

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

<main class="products-page">

    <section class="page-heading">
        <h2>Our Collection</h2>

        <p>
            Browse our range of hoodies, jumpers and T-shirts.
        </p>
    </section>

    <?php if (!empty($message)): ?>
        <div class="notice notice-success" role="status">
            <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <!-- Product filters and search -->
    <section class="catalogue-controls">

        <div class="filter-group" aria-label="Filter products">

            <a
                href="products.php?filter=all"
                class="filter-btn <?php echo $filter === 'all' ? 'selected' : ''; ?>"
                <?php echo $filter === 'all' ? 'aria-current="page"' : ''; ?>
            >
                All Products
            </a>

            <a
                href="products.php?filter=tshirt"
                class="filter-btn <?php echo $filter === 'tshirt' ? 'selected' : ''; ?>"
                <?php echo $filter === 'tshirt' ? 'aria-current="page"' : ''; ?>
            >
                T-Shirts
            </a>

            <a
                href="products.php?filter=jumper"
                class="filter-btn <?php echo $filter === 'jumper' ? 'selected' : ''; ?>"
                <?php echo $filter === 'jumper' ? 'aria-current="page"' : ''; ?>
            >
                Jumpers
            </a>

            <a
                href="products.php?filter=hoodie"
                class="filter-btn <?php echo $filter === 'hoodie' ? 'selected' : ''; ?>"
                <?php echo $filter === 'hoodie' ? 'aria-current="page"' : ''; ?>
            >
                Hoodies
            </a>

        </div>

        <form
            method="GET"
            action="products.php"
            class="search-form"
            role="search"
        >

            <input
                type="hidden"
                name="filter"
                value="<?php echo htmlspecialchars($filter, ENT_QUOTES, 'UTF-8'); ?>"
            >

            <label for="product-search" class="visually-hidden">
                Search products
            </label>

            <input
                type="search"
                id="product-search"
                name="search"
                placeholder="Search products..."
                value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>"
            >

            <button type="submit" class="btn btn-search">
                Search
            </button>

            <?php if ($search !== ''): ?>
                <a
                    href="products.php?filter=<?php echo urlencode($filter); ?>"
                    class="btn btn-clear"
                >
                    Clear
                </a>
            <?php endif; ?>

        </form>

    </section>

    <!-- Product results -->
    <section class="products-section">

        <div class="results-heading">

            <h2>
                <?php
                if ($filter === 'hoodie') {
                    echo 'Hoodies';
                } elseif ($filter === 'jumper') {
                    echo 'Jumpers';
                } elseif ($filter === 'tshirt') {
                    echo 'T-Shirts';
                } else {
                    echo 'All Products';
                }
                ?>
            </h2>

            <p>
                <?php echo count($products); ?>
                <?php echo count($products) === 1 ? 'product' : 'products'; ?>
                found
            </p>

        </div>

        <?php if (!empty($products)): ?>

            <div class="products-container">

                <?php foreach ($products as $row): ?>

                    <article class="product-card">

                        <a
                            href="item.php?id=<?php echo (int) $row['product_id']; ?>"
                            class="product-image-link"
                            aria-label="View <?php echo htmlspecialchars($row['product_title'], ENT_QUOTES, 'UTF-8'); ?>"
                        >

                            <img
                                src="<?php echo htmlspecialchars($row['product_image'], ENT_QUOTES, 'UTF-8'); ?>"
                                alt="<?php echo htmlspecialchars($row['product_title'], ENT_QUOTES, 'UTF-8'); ?>"
                                loading="lazy"
                            >

                        </a>

                        <div class="product-card-content">

                            <h3>
                                <?php echo htmlspecialchars($row['product_title'], ENT_QUOTES, 'UTF-8'); ?>
                            </h3>

                            <p class="product-type">
                                <?php echo htmlspecialchars($row['product_type'], ENT_QUOTES, 'UTF-8'); ?>
                            </p>

                            <p class="product-price">
                                £<?php echo number_format((float) $row['product_price'], 2); ?>
                            </p>

                            <div class="product-buttons">

                                <a
                                    href="item.php?id=<?php echo (int) $row['product_id']; ?>"
                                    class="btn btn-details"
                                >
                                    View Details
                                </a>

                                <a
                                    href="products.php?action=add&id=<?php echo (int) $row['product_id']; ?>&filter=<?php echo urlencode($filter); ?>&search=<?php echo urlencode($search); ?>"
                                    class="btn btn-add"
                                >
                                    Add to Basket
                                </a>

                                <a
                                    href="cart.php"
                                    class="btn btn-cart"
                                >
                                    View Cart
                                </a>

                            </div>

                        </div>

                    </article>

                <?php endforeach; ?>

            </div>

        <?php else: ?>

            <div class="empty-results">

                <h3>No products found</h3>

                <?php if ($search !== ''): ?>
                    <p>
                        No products matched
                        "<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>".
                        Try another search.
                    </p>
                <?php else: ?>
                    <p>There are currently no products in this category.</p>
                <?php endif; ?>

                <a href="products.php" class="btn btn-details">
                    View All Products
                </a>

            </div>

        <?php endif; ?>

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
