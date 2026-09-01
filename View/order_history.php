<?php
// View/order_history.php
session_start();
$orderHistory = $_SESSION['orderHistory'] ?? [
    [
        'order_id' => 'ORD001',
        'date' => '2026-08-10',
        'total' => 1100,
        'payment_method' => 'Cash on Delivery',
        'status' => 'Delivered'
    ]
];
?>
<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <title>Order History</title>
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
        .order-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background: #252525;
            border-radius: 6px;
        }
        .status {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
        }
        .delivered {
            background: #198754;
            color: #fff;
        }
        .processing {
            background: #ffc107;
            color: #000;
        }
        .btn-back {
            display: inline-block;
            margin-top: 20px;
            color: #0d6efd;
            text-decoration: none;
        }
    </style>
</head>
<body>
<header>
    <h1>Order History</h1>
    <a href="customer_dashboard.php?action=logout" class="logout">Logout</a>
</header>
<div class="container">
    <h2>Your Past Orders</h2>
    <?php if (!empty($orderHistory)): ?>
        <ul class="order-list">
            <?php foreach ($orderHistory as $order): ?>
                <li class="order-item">
                    <div>
                        <h4>Order #<?php echo htmlspecialchars($order['order_id']);
                        ?></h4>
                        <p style="color: #aaa; font-size: 13px;">Date: <?php echo htmlspecialchars($order['date']);
                        ?></p>
                        <p style="color: #aaa; font-size: 13px;">Method: <?php echo htmlspecialchars($order['payment_method'] ?? 'N/A');
                        ?></p>
                    </div>
                    <div>
                        <span style="font-weight: bold; margin-right: 15px;">৳<?php echo number_format($order['total'], 2);
                        ?></span>
                        <span class="status <?php echo strtolower($order['status']);
                        ?>">
                            <?php echo htmlspecialchars($order['status']);
                            ?>
                        </span>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p style="color: #aaa;">No previous orders found.</p>
    <?php endif; ?>
    <a href="customer_dashboard.php" class="btn-back">← Back to Dashboard</a>
</div>
</body>
</html>