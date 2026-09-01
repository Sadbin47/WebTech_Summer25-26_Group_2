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

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$jersey = $customerController->productDetails($id);

if (!$jersey) {
    header('Location: customer_dashboard.php');
    exit;
}

include 'header.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Product Details</title>

    <style>
        body {
            margin: 0;
            background: #11161c;
            color: white;
            font-family: Arial, sans-serif;
        }

        .details-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
        }

        .product-details {
            background: #252b33;
            padding: 30px;
            border-radius: 8px;
        }

        .product-details h1 {
            margin-top: 0;
        }

        .details-row {
            margin: 15px 0;
            color: #c3c9cf;
        }

        .price {
            font-size: 24px;
            font-weight: bold;
            margin: 20px 0;
        }

        .quantity-box {
            margin: 20px 0;
        }

        .quantity-box input {
            width: 80px;
            padding: 10px;
            background: #171c22;
            color: white;
            border: 1px solid #4a535c;
        }

        .order-button {
            padding: 11px 20px;
            background: #28795c;
            color: white;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        .back-button {
            display: inline-block;
            margin-right: 10px;
            padding: 11px 20px;
            background: #3b444f;
            color: white;
            text-decoration: none;
        }
    </style>
</head>

<body>

<div class="details-container">

    <div class="product-details">

        <h1>
            <?php echo htmlspecialchars($jersey['name']); ?>
        </h1>

        <div class="details-row">
            Category:
            <?php echo htmlspecialchars($jersey['category']); ?>
        </div>

        <div class="details-row">
            Size:
            <?php echo htmlspecialchars($jersey['size']); ?>
        </div>

        <div class="details-row">
            Available Quantity:
            <?php echo htmlspecialchars($jersey['quantity']); ?>
        </div>

        <div class="price">
            ৳<?php echo number_format($jersey['price'], 2); ?>
        </div>

        <form method="GET" action="checkout.php">

            <input
                type="hidden"
                name="id"
                value="<?php echo $jersey['id']; ?>"
            >

            <div class="quantity-box">

                <label for="quantity">
                    Quantity:
                </label>

                <input
                    type="number"
                    id="quantity"
                    name="quantity"
                    min="1"
                    max="<?php echo $jersey['quantity']; ?>"
                    value="1"
                    required
                >

            </div>

            <a
                class="back-button"
                href="customer_dashboard.php"
            >
                Back
            </a>

            <button class="order-button" type="submit">
                Order Now
            </button>

        </form>

    </div>

</div>

</body>
</html>