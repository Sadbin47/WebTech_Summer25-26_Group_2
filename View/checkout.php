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
$quantity = isset($_GET['quantity']) ? (int) $_GET['quantity'] : 1;

$jersey = $customerController->checkout($id, $quantity);

if (!$jersey) {
    header('Location: customer_dashboard.php');
    exit;
}

$phoneError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $quantity = (int) $_POST['quantity'];
    $phone = trim($_POST['phone']);

    if ($quantity < 1) {
        $quantity = 1;
    }

    if (!preg_match('/^01[0-9]{9}$/', $phone)) {
        $phoneError = 'Phone number must be exactly 11 digits and start with 01.';
    } else {

        $jersey = $customerController->checkout($id, $quantity);

        if (!$jersey) {
            header('Location: customer_dashboard.php');
            exit;
        }

        $_SESSION['checkout'] = [
            'jersey_id' => $jersey['id'],
            'quantity' => $jersey['order_quantity'],
            'name' => trim($_POST['name']),
            'phone' => $phone,
            'address' => trim($_POST['address']),
            'total' => $jersey['subtotal']
        ];

        header('Location: payment.php');
        exit;
    }
}

include 'header.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Checkout</title>

    <style>
        body {
            margin: 0;
            background: #11161c;
            color: white;
            font-family: Arial, sans-serif;
        }

        .checkout-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
        }

        .checkout-box {
            background: #252b33;
            padding: 30px;
            border-radius: 8px;
        }

        .checkout-box h1 {
            margin-top: 0;
        }

        .product-info {
            margin: 12px 0;
            color: #c3c9cf;
        }

        .total {
            font-size: 22px;
            font-weight: bold;
            margin: 25px 0;
        }

        label {
            display: block;
            margin-top: 15px;
            margin-bottom: 6px;
        }

        input,
        textarea {
            width: 100%;
            box-sizing: border-box;
            padding: 10px;
            background: #171c22;
            border: 1px solid #4a535c;
            color: white;
        }

        textarea {
            height: 90px;
        }

        .error-message {
            background: #6b3030;
            padding: 10px;
            margin-top: 8px;
        }

        .payment-button {
            margin-top: 20px;
            padding: 11px 20px;
            background: #28795c;
            color: white;
            border: none;
            cursor: pointer;
        }
    </style>
</head>

<body>

<div class="checkout-container">

    <div class="checkout-box">

        <h1>Checkout</h1>

        <div class="product-info">
            Jersey:
            <?php echo htmlspecialchars($jersey['name']); ?>
        </div>

        <div class="product-info">
            Size:
            <?php echo htmlspecialchars($jersey['size']); ?>
        </div>

        <div class="product-info">
            Price:
            ৳<?php echo number_format($jersey['price'], 2); ?>
        </div>

        <div class="product-info">
            Quantity:
            <?php echo $jersey['order_quantity']; ?>
        </div>

        <div class="total">
            Total:
            ৳<?php echo number_format($jersey['subtotal'], 2); ?>
        </div>

        <form method="POST">

            <label for="name">
                Name
            </label>

            <input
                type="text"
                id="name"
                name="name"
                value="<?php echo htmlspecialchars($_SESSION['name'] ?? ''); ?>"
                required
            >

            <label for="phone">
                Phone Number
            </label>

            <input
                type="text"
                id="phone"
                name="phone"
                value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>"
                required
            >

            <?php if ($phoneError): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($phoneError); ?>
                </div>
            <?php endif; ?>

            <label for="address">
                Address
            </label>

            <textarea
                id="address"
                name="address"
                required
            ><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>

            <input
                type="hidden"
                name="quantity"
                value="<?php echo $jersey['order_quantity']; ?>"
            >

            <button
                class="payment-button"
                type="submit"
            >
                Proceed to Payment
            </button>

        </form>

    </div>

</div>

</body>
</html>