
<?php
session_start();

require_once __DIR__ . '/connect.php';

// Variables for messages
$successMsg = "";
$errorMsg   = "";

// Server-side validation
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Retrieve and clean submitted values
    $fullName    = trim($_POST["full_name"] ?? "");
    $email       = trim($_POST["email"] ?? "");
    $address     = trim($_POST["address"] ?? "");
    $pass        = $_POST["password"] ?? "";
    $passConfirm = $_POST["confirm_password"] ?? "";

    // Check required fields
    if (
        $fullName === "" ||
        $email === "" ||
        $address === "" ||
        $pass === "" ||
        $passConfirm === ""
    ) {

        $errorMsg = "Please fill in all fields.";

    // Validate email format
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $errorMsg = "Please enter a valid email address.";

    // Confirm passwords match
    } elseif ($pass !== $passConfirm) {

        $errorMsg = "Passwords do not match.";

    } else {

        // Check password requirements
        $validLength  = strlen($pass) >= 8;
        $validUpper   = preg_match('/[A-Z]/', $pass);
        $validLower   = preg_match('/[a-z]/', $pass);
        $validDigit   = preg_match('/[0-9]/', $pass);
        $validSpecial = preg_match('/[^A-Za-z0-9]/', $pass);

        // Reject passwords that do not meet all requirements
        if (
            !$validLength ||
            !$validUpper ||
            !$validLower ||
            !$validDigit ||
            !$validSpecial
        ) {

            $errorMsg = "Password must be at least 8 characters and contain an uppercase letter, lowercase letter, digit, and special character.";

        } else {

            try {

                // Check whether the email is already registered
                $checkStmt = $conn->prepare(
                    "SELECT user_id FROM tbl_users WHERE user_email = :email"
                );

                $checkStmt->bindValue(':email', $email);
                $checkStmt->execute();

                if ($checkStmt->fetch(PDO::FETCH_ASSOC)) {

                    $errorMsg = "That email address is already registered.";

                } else {

                    // Securely hash the password
                    $hashedPassword = password_hash(
                        $pass,
                        PASSWORD_DEFAULT
                    );

                    // Insert the new user into the database
                    $insertStmt = $conn->prepare(
                        "INSERT INTO tbl_users
                        (user_full_name, user_address, user_email, user_pass)
                        VALUES
                        (:fname, :address, :email, :upass)"
                    );

                    $insertStmt->bindValue(':fname', $fullName);
                    $insertStmt->bindValue(':address', $address);
                    $insertStmt->bindValue(':email', $email);
                    $insertStmt->bindValue(':upass', $hashedPassword);

                    $insertStmt->execute();

                    $successMsg = "Registration successful! You can now log in.";
                }

            } catch (PDOException $e) {

                // Log technical details instead of exposing database errors
                error_log("Registration error: " . $e->getMessage());

                $errorMsg = "Registration could not be completed. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Register - Student Union Shop</title>

    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/auth.css">
</head>

<body>

<div class="auth-container">

    <h2>Register a New Account</h2>

    <!-- Server-side error message -->
    <?php if ($errorMsg): ?>
        <p class="error">
            <?php echo htmlspecialchars($errorMsg, ENT_QUOTES, 'UTF-8'); ?>
        </p>
    <?php endif; ?>

    <!-- Server-side success message -->
    <?php if ($successMsg): ?>
        <p class="success">
            <?php echo htmlspecialchars($successMsg, ENT_QUOTES, 'UTF-8'); ?>
        </p>
    <?php endif; ?>

    <!-- Registration Form -->
    <form action="register.php" method="POST" id="regForm">

        <label for="full_name">Full Name:</label>

        <input
            type="text"
            name="full_name"
            id="full_name"
            autocomplete="name"
            placeholder="Enter your full name"
            value="<?php echo htmlspecialchars($_POST['full_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
            required
        >

        <label for="email">Email:</label>

        <input
            type="email"
            name="email"
            id="email"
            autocomplete="email"
            placeholder="Enter your email"
            value="<?php echo htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
            required
        >

        <label for="address">Address:</label>

        <input
            type="text"
            name="address"
            id="address"
            autocomplete="street-address"
            placeholder="Enter your address"
            value="<?php echo htmlspecialchars($_POST['address'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
            required
        >

        <label for="password">Password:</label>

        <input
            type="password"
            name="password"
            id="password"
            autocomplete="new-password"
            placeholder="Enter your password"
            required
        >

        <!-- Password requirements list -->
        <div class="requirements hidden" id="password-requirements">

            <ul>
                <li id="req-length">At least 8 characters</li>
                <li id="req-upper">At least 1 uppercase letter (A-Z)</li>
                <li id="req-lower">At least 1 lowercase letter (a-z)</li>
                <li id="req-digit">At least 1 digit (0-9)</li>
                <li id="req-special">At least 1 special symbol (!@#$%^&* etc.)</li>
            </ul>

        </div>

        <label for="confirm_password">Confirm Password:</label>

        <input
            type="password"
            name="confirm_password"
            id="confirm_password"
            autocomplete="new-password"
            placeholder="Confirm your password"
            required
        >

        <p id="confirmMsg" class="hidden"></p>

        <button type="submit" class="auth-btn">
            Sign Up
        </button>

    </form>

    <p>
        Already have an account?
        <a href="login.php">Login here</a>.
    </p>

</div>

<script src="js/auth.js"></script>

</body>
</html>