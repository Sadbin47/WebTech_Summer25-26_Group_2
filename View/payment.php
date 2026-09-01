<?php
// View/payment.php
session_start();
$checkoutData = $_SESSION['checkout_data'] ?? null;
if (!$checkoutData) {
    header("Location: checkout.php");
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['orderHistory'])) {
        $_SESSION['orderHistory'] = [];
    }
    $newOrder = [
        'order_id' => 'ORD' . sprintf('%03d', count($_SESSION['orderHistory']) + 1),
        'date' => date('Y-m-d'),
        'total' => $checkoutData['cartTotal'],
        'payment_method' => $_POST['payment_method'],
        'status' => 'Processing'
    ];
    array_unshift($_SESSION['orderHistory'], $newOrder);
    unset($_SESSION['cart']);
    unset($_SESSION['checkout_data']);
    header("Location: order_history.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <title>Payment</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: Arial, sans-serif;
            background: #121212;
            color: #fff;
        }
        header {
            background: #1e1e1e;
            padding: 18px 40px;
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid #333;
        }
        .logout {
            color: white;
            text-decoration: none;
            background: #dc3545;
            padding: 8px 16px;
            border-radius: 5px;
        }
        .container {
            width: 90%;
            max-width: 600px;
            margin: 30px auto;
            background: #1e1e1e;
            padding: 25px;
            border-radius: 8px;
            border: 1px solid #2a2a2a;
        }
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-bottom: 15px;
        }
        .form-group select {
            padding: 10px;
            background: #000;
            color: #fff;
            border: 1px solid #333;
            border-radius: 5px;
        }
        .btn {
            border: none;
            padding: 10px 18px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            background: #198754;
            color: white;
        }
    </style>
</head>
<body>
<header>
    <h1>Payment</h1>
    <a href="customer_dashboard.php?action=logout" class="logout">Logout</a>
</header>
<div class="container">
    <h2>Select Payment Method</h2>
    <p style="margin: 15px 0;">Amount to Pay: <strong>৳<?php echo number_format($checkoutData['cartTotal'], 2);
    ?></strong></p>
    <form method="POST" action="payment.php">
        <div class="form-group">
            <label for="payment_method">Payment Option</label>
            <select id="payment_method" name="payment_method" required>
                <option value="Cash on Delivery">Cash on Delivery</option>
                <option value="Credit/Debit Card">Credit/Debit Card</option>
                <option value="Mobile Banking">Mobile Banking (bkash/Nagad)</option>
            </select>
        </div>
        <button type="submit" class="btn">Confirm & Place Order</button>
    </form>
</div>
</body>
</html>