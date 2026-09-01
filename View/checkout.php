<?php
// View/checkout.php
session_start();
$cart = $_SESSION['cart'] ?? [];
$cartTotal = 0;
foreach ($cart as $item) {
    $cartTotal += $item['price'] * $item['quantity'];
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['checkout_data'] = [
        'name' => $_POST['name'],
        'address' => $_POST['address'],
        'cartTotal' => $cartTotal
    ];
    header("Location: payment.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <title>Checkout</title>
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
            max-width: 800px;
            margin: 30px auto;
            background: #1e1e1e;
            padding: 25px;
            border-radius: 8px;
            border: 1px solid #2a2a2a;
        }
        .cart-list {
            list-style: none;
            margin-bottom: 20px;
        }
        .cart-item {
            display: flex;
            justify-content: space-between;
            padding: 10px;
            background: #252525;
            margin-bottom: 8px;
            border-radius: 4px;
        }
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-bottom: 15px;
        }
        .form-group input, .form-group textarea {
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
            background: #0d6efd;
            color: white;
        }
    </style>
</head>
<body>
<header>
    <h1>Checkout</h1>
    <a href="customer_dashboard.php?action=logout" class="logout">Logout</a>
</header>
<div class="container">
    <h2>Order Summary</h2>
    <?php if (!empty($cart)): ?>
        <ul class="cart-list">
            <?php foreach ($cart as $item): ?>
                <li class="cart-item">
                    <span><?php echo htmlspecialchars($item['name']);
                    ?> (x<?php echo $item['quantity'];
                    ?>)</span>
                    <span>৳<?php echo number_format($item['price'] * $item['quantity'], 2);
                    ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
        <h3 style="text-align: right; margin-bottom: 20px; color: #198754;">Total: ৳<?php echo number_format($cartTotal, 2);
        ?></h3>
        <h2>Shipping Information</h2>
        <form method="POST" action="checkout.php">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="address">Delivery Address</label>
                <textarea id="address" name="address" rows="3" required></textarea>
            </div>
            <button type="submit" class="btn">Proceed to Payment</button>
        </form>
    <?php else: ?>
        <p style="margin-top: 15px;">Your cart is empty. <a href="customer_dashboard.php" style="color: #0d6efd;">Return to dashboard</a>.</p>
    <?php endif; ?>
</div>
</body>
</html>