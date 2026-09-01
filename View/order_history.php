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

$orders = $customerController->orderHistory($_SESSION['user_id']);

include 'header.php';

?>

<!DOCTYPE html>
<html lang="en-US">

<head>
    <meta charset="UTF-8">
    <title>Order History</title>

    <style>
        body {
            margin: 0;
            background: #121212;
            color: white;
            font-family: Arial, sans-serif;
        }

        .container {
            width: 90%;
            max-width: 800px;
            margin: 30px auto;
            background: #1e1e1e;
            padding: 25px;
            border-radius: 8px;
        }

        .order-list {
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

        .processing {
            background: #ffc107;
            color: #000;
        }

        .delivered {
            background: #198754;
            color: white;
        }

        .cancelled {
            background: #dc3545;
            color: white;
        }
    </style>
</head>

<body>

<div class="container">

    <h1>Order History</h1>

    <?php if (!empty($orders)): ?>

        <div class="order-list">

            <?php foreach ($orders as $order): ?>

                <div class="order-item">

                    <div>

                        <h3>
                            Order #<?php echo htmlspecialchars($order['id']); ?>
                        </h3>

                        <p>
                            Date:
                            <?php echo htmlspecialchars($order['created_at']); ?>
                        </p>

                        <p>
                            Quantity:
                            <?php echo htmlspecialchars($order['total_quantity']); ?>
                        </p>

                    </div>

                    <div>

                        <strong>
                            ৳<?php echo number_format($order['total_amount'], 2); ?>
                        </strong>

                        <br><br>

                        <span class="status <?php echo strtolower($order['status']); ?>">
                            <?php echo htmlspecialchars($order['status']); ?>
                        </span>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    <?php else: ?>

        <p>No previous orders found.</p>

    <?php endif; ?>

</div>

</body>
</html>