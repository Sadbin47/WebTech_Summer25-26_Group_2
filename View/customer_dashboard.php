<?php
session_start();

require_once '../Model/db.php';
require_once '../Controller/CustomerController.php';

if (($_SESSION['role'] ?? '') !== 'Customer') {
    header('Location: login.php');
    exit;
}

$database = new Database();
$customerController = new CustomerController($database->connect());

$jerseys = $customerController->dashboard();

$orderError = $_SESSION['order_error'] ?? '';
unset($_SESSION['order_error']);

include 'header.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Customer Dashboard</title>

    <style>
        body {
            margin: 0;
            background: #11161c;
            color: white;
            font-family: Arial, sans-serif;
        }

        .customer-container {
            max-width: 1100px;
            margin: 30px auto;
            padding: 20px;
        }

        .customer-title {
            margin-bottom: 25px;
        }

        .customer-title h1 {
            margin-bottom: 8px;
        }

        .customer-title p {
            color: #aeb7c0;
        }

        .error-message {
            background: #6b3030;
            padding: 12px;
            margin-bottom: 20px;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
            gap: 20px;
        }

        .product-card {
            background: #252b33;
            padding: 20px;
            border-radius: 8px;
        }

        .product-card h3 {
            margin-top: 0;
        }

        .product-info {
            color: #b8c0c8;
            margin: 8px 0;
        }

        .product-price {
            font-size: 20px;
            font-weight: bold;
            margin: 15px 0;
        }

        .view-button {
            display: inline-block;
            padding: 10px 16px;
            background: #28795c;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }

        .view-button:hover {
            background: #32936e;
        }

        .no-products {
            background: #252b33;
            padding: 25px;
            text-align: center;
        }
    </style>
</head>

<body>

<div class="customer-container">

    <div class="customer-title">
        <h1>Jersey Collection</h1>
        <p>Choose a jersey to view its details.</p>
    </div>

    <?php if ($orderError): ?>
        <div class="error-message">
            <?php echo htmlspecialchars($orderError); ?>
        </div>
    <?php endif; ?>

    <?php if (count($jerseys) > 0): ?>

        <div class="product-grid">

            <?php foreach ($jerseys as $jersey): ?>

                <div class="product-card">

                    <h3>
                        <?php echo htmlspecialchars($jersey['name']); ?>
                    </h3>

                    <div class="product-info">
                        Category:
                        <?php echo htmlspecialchars($jersey['category']); ?>
                    </div>

                    <div class="product-info">
                        Size:
                        <?php echo htmlspecialchars($jersey['size']); ?>
                    </div>

                    <div class="product-info">
                        Available:
                        <?php echo htmlspecialchars($jersey['quantity']); ?>
                    </div>

                    <div class="product-price">
                        ৳<?php echo number_format($jersey['price'], 2); ?>
                    </div>

                    <a
                        class="view-button"
                        href="product_details.php?id=<?php echo $jersey['id']; ?>"
                    >
                        View Details
                    </a>

                </div>

            <?php endforeach; ?>

        </div>

    <?php else: ?>

        <div class="no-products">
            No jerseys are currently available.
        </div>

    <?php endif; ?>

</div>

</body>
</html>