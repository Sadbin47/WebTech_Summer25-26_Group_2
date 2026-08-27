<?php
session_start();
$products = [
    [
        'id' => 1,
        'name' => 'Classic T-Shirt',
        'price' => 550,
        'image' => ''
    ],
    [
        'id' => 2,
        'name' => 'Casual Shirt',
        'price' => 850,
        'image' => ''
    ],
    [
        'id' => 3,
        'name' => 'Denim Jacket',
        'price' => 1500,
        'image' => ''
    ],
    [
        'id' => 4,
        'name' => 'Cotton Pants',
        'price' => 950,
        'image' => ''
    ]
];

$cart = [
    [
        'name' => 'Classic T-Shirt',
        'price' => 550,
        'quantity' => 2
    ]
];

$orderHistory = [
    [
        'order_id' => 'ORD001',
        'date' => '2026-08-10',
        'total' => 1100,
        'status' => 'Delivered'
    ],
    [
        'order_id' => 'ORD002',
        'date' => '2026-08-15',
        'total' => 1500,
        'status' => 'Processing'
    ]
];

$cartTotal = 0;

foreach ($cart as $item) {
    $cartTotal += $item['price'] * $item['quantity'];
}
?>

<!DOCTYPE html>
<html lang="en-US">

<head>
    <meta charset="UTF-8">  <!--help to display character properly-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!--properly display on mobile or tablet screen-->

    <title>Customer Dashboard</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f5f6fa;
            color: #333;
        }

        /* Fixed / Sticky Header at the top */
        header {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: #222;
            color: white;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
        }

        header h1 {
            font-size: 24px;
        }

        .logout {
            color: white;
            text-decoration: none;
            background: #dc3545;
            padding: 8px 15px;
            border-radius: 5px;
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 30px auto;
        }

        .section {
            background: white;
            padding: 25px;
            margin-bottom: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .section h2 {
            margin-bottom: 20px;
            color: #222;
        }

        /* Product Grid */

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }

        .product-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            background: #fff;
        }

        .product-image {
            height: 150px;
            background: #eee;
            border-radius: 6px;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 15px;
            color: #777;
        }

        .product-card h3 {
            margin-bottom: 10px;
        }

        .price {
            font-weight: bold;
            color: #198754;
        }

        .btn {
            border: none;
            padding: 9px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-success {
            background: #198754;
            color: white;
        }

        .btn:hover {
            opacity: 0.85;
        }

        /* Shopping Cart List */

        .cart-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 18px;
            background: #fdfdfd;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
        }

        .cart-item-details h4 {
            font-size: 16px;
            margin-bottom: 5px;
            color: #222;
        }

        .cart-item-details p {
            font-size: 14px;
            color: #666;
        }

        .cart-item-subtotal {
            font-size: 16px;
            font-weight: bold;
            color: #198754;
        }

        .cart-total {
            text-align: right;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 2px dashed #ddd;
            font-size: 18px;
            font-weight: bold;
        }

        /* Checkout */

        .checkout-form {
            display: grid;
            gap: 15px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            margin-bottom: 6px;
            font-weight: bold;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }

        textarea {
            resize: vertical;
            min-height: 80px;
        }

        .checkout-button {
            width: 150px;
            margin-top: 10px;
        }

        /* Order History List */

        .order-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            background: #fdfdfd;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
        }

        .order-info h4 {
            font-size: 16px;
            margin-bottom: 5px;
            color: #222;
        }

        .order-info p {
            font-size: 14px;
            color: #666;
        }

        .order-meta {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .order-total {
            font-weight: bold;
            font-size: 16px;
            color: #222;
        }

        /* Status Badges */

        .status {
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 13px;
            font-weight: 500;
        }

        .delivered {
            background: #d1e7dd;
            color: #0f5132;
        }

        .processing {
            background: #fff3cd;
            color: #664d03;
        }

        @media (max-width: 600px) {
            header {
                padding: 15px;
            }

            .container {
                width: 95%;
            }

            .section {
                padding: 15px;
            }

            .cart-item,
            .order-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .order-meta {
                width: 100%;
                justify-content: space-between;
            }
        }
    </style>
</head>

<body>

<header>
    <h1>Customer Dashboard</h1>
    <a href="#" class="logout">Logout</a>
</header>

<div class="container">

    <!-- PRODUCT SECTION -->
    <div class="section">
        <h2>Available Products</h2>
        <div class="product-grid">
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <div class="product-image">
                        <?php if (!empty($product['image'])): ?>
                            <img
                                src="<?php echo htmlspecialchars($product['image']); ?>"
                                alt="<?php echo htmlspecialchars($product['name']); ?>"
                                style="max-width:100%; max-height:100%;"
                            >
                        <?php else: ?>
                            Product Image
                        <?php endif; ?>
                    </div>

                    <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                    <p class="price">৳ <?php echo number_format($product['price'], 2); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>


    <!-- CART SECTION -->
    <div class="section">
        <h2>Shopping Cart</h2>

        <?php if (!empty($cart)): ?>
            <ul class="cart-list">
                <?php foreach ($cart as $item): ?>
                    <li class="cart-item">
                        <div class="cart-item-details">
                            <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                            <p>Price: ৳ <?php echo number_format($item['price'], 2); ?> | Quantity: <?php echo $item['quantity']; ?></p>
                        </div>
                        <div class="cart-item-subtotal">
                            ৳ <?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>

            <div class="cart-total">
                Total: ৳ <?php echo number_format($cartTotal, 2); ?>
            </div>
        <?php else: ?>
            <p>Your cart is empty.</p>
        <?php endif; ?>
    </div>


    <!-- CHECKOUT SECTION -->
    <div class="section">
        <h2>Checkout</h2>
        <form method="POST" action="" class="checkout-form">
            <input type="hidden" name="action" value="checkout">

            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" placeholder="Enter your full name" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" required>
            </div>

            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" placeholder="Enter your phone number" required>
            </div>

            <div class="form-group">
                <label for="address">Delivery Address</label>
                <textarea id="address" name="address" placeholder="Enter your delivery address" required></textarea>
            </div>

            <div class="form-group">
                <label for="payment">Payment Method</label>
                <select id="payment" name="payment" required>
                    <option value="">Select payment method</option>
                    <option value="cash">Cash on Delivery</option>
                    <option value="card">Card</option>
                    <option value="mobile_banking">Mobile Banking</option>
                </select>
            </div>

            <button type="submit" class="btn btn-success checkout-button">
                Place Order
            </button>
        </form>
    </div>


    <!-- ORDER HISTORY SECTION -->
    <div class="section">
        <h2>Order History</h2>
        <?php if (!empty($orderHistory)): ?>
            <ul class="order-list">
                <?php foreach ($orderHistory as $order): ?>
                    <li class="order-item">
                        <div class="order-info">
                            <h4>Order #<?php echo htmlspecialchars($order['order_id']); ?></h4>
                            <p>Date: <?php echo htmlspecialchars($order['date']); ?></p>
                        </div>
                        <div class="order-meta">
                            <span class="order-total">
                                ৳ <?php echo number_format($order['total'], 2); ?>
                            </span>
                            <?php if ($order['status'] === 'Delivered'): ?>
                                <span class="status delivered">Delivered</span>
                            <?php else: ?>
                                <span class="status processing">
                                    <?php echo htmlspecialchars($order['status']); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No previous orders found.</p>
        <?php endif; ?>
    </div>

</div>

</body>
</html>