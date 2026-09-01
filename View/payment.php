<?php

session_start();

if (($_SESSION['role'] ?? '') !== 'Customer') {
    header('Location: login.php');
    exit;
}

if (!isset($_SESSION['checkout'])) {
    header('Location: customer_dashboard.php');
    exit;
}

require_once '../Model/db.php';
require_once '../Controller/CustomerController.php';

$database = new Database();
$customerController = new CustomerController($database->connect());

$checkout = $_SESSION['checkout'];

$jersey = $customerController->productDetails(
    (int) $checkout['jersey_id']
);

if (!$jersey) {
    unset($_SESSION['checkout']);
    header('Location: customer_dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $paymentMethod = $_POST['payment_method'] ?? '';

    if ($paymentMethod === '') {

        $error = 'Please select a payment method.';

    } else {

        $success = $customerController->placeOrder(
            (int) $_SESSION['user_id'],
            $_SESSION['name'],
            (int) $checkout['jersey_id'],
            (int) $checkout['quantity']
        );

        if ($success) {

            $_SESSION['last_payment_method'] = $paymentMethod;

            unset($_SESSION['checkout']);

            header('Location: order_history.php');
            exit;

        } else {

            $error = 'Unable to place the order. Please try again.';
        }
    }
}

include 'header.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Payment</title>

    <style>
        body {
            margin: 0;
            background: #11161c;
            color: white;
            font-family: Arial, sans-serif;
        }

        .payment-container {
            max-width: 700px;
            margin: 40px auto;
            padding: 20px;
        }

        .payment-box {
            background: #252b33;
            padding: 30px;
            border-radius: 8px;
        }

        .payment-box h1 {
            margin-top: 0;
        }

        .summary {
            color: #c3c9cf;
            margin: 12px 0;
        }

        .total {
            font-size: 22px;
            font-weight: bold;
            margin: 20px 0;
        }

        .error {
            background: #6b3030;
            padding: 10px;
            margin-bottom: 15px;
        }

        select {
            padding: 10px;
            width: 100%;
            background: #171c22;
            color: white;
            border: 1px solid #4a535c;
        }

        button {
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

<div class="payment-container">

    <div class="payment-box">

        <h1>Payment</h1>

        <div class="summary">
            Jersey:
            <?php echo htmlspecialchars($jersey['name']); ?>
        </div>

        <div class="summary">
            Size:
            <?php echo htmlspecialchars($jersey['size']); ?>
        </div>

        <div class="summary">
            Quantity:
            <?php echo $checkout['quantity']; ?>
        </div>

        <div class="total">
            Total:
            ৳<?php echo number_format($checkout['total'], 2); ?>
        </div>

        <?php if ($error): ?>

            <div class="error">
                <?php echo htmlspecialchars($error); ?>
            </div>

        <?php endif; ?>

        <form method="POST">

            <label for="payment_method">
                Payment Method
            </label>

            <select
                id="payment_method"
                name="payment_method"
                required
            >
                <option value="">Select Payment Method</option>
                <option value="Cash on Delivery">
                    Cash on Delivery
                </option>
                <option value="Online Payment">
                    Online Payment
                </option>
            </select>

            <button type="submit">
                Confirm Order
            </button>

        </form>

    </div>

</div>

</body>
</html>