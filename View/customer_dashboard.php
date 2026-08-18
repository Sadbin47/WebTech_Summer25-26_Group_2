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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

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

        header {
            background: #222;
            color: white;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
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
            margin-bottom: 15px;
        }

        .btn {
            border: none;
            padding: 9px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-primary {
            background: #0d6efd;
            color: white;
        }

        .btn-success {
            background: #198754;
            color: white;
        }

        .btn:hover {
            opacity: 0.85;
        }

        /* Cart */

        .cart-table,
        .history-table {
            width: 100%;
            border-collapse: collapse;
        }

        .cart-table th,
        .cart-table td,
        .history-table th,
        .history-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
        }

        .cart-table th,
        .history-table th {
            background: #f1f1f1;
        }

        .cart-total {
            text-align: right;
            margin-top: 15px;
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

        /* Status */

        .status {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 13px;
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

            .cart-table,
            .history-table {
                font-size: 13px;
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

    <!--product-->

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

                    <h3>
                        <?php echo htmlspecialchars($product['name']); ?>
                    </h3>

                    <p class="price">
                        ৳ <?php echo number_format($product['price'], 2); ?>
                    </p>

                    <form method="POST" action="">

                        <input
                            type="hidden"
                            name="product_id"
                            value="<?php echo $product['id']; ?>"
                        >

                        <input
                            type="hidden"
                            name="action"
                            value="add_to_cart"
                        >

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            Add to Cart
                        </button>

                    </form>

                </div>

            <?php endforeach; ?>

        </div>

    </div>


    <!--CART-->

    <div class="section">

        <h2>Shopping Cart</h2>

        <?php if (!empty($cart)): ?>

            <table class="cart-table">

                <thead>

                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                    </tr>

                </thead>

                <tbody>

                    <?php foreach ($cart as $item): ?>

                        <tr>

                            <td>
                                <?php echo htmlspecialchars($item['name']); ?>
                            </td>

                            <td>
                                ৳ <?php echo number_format($item['price'], 2); ?>
                            </td>

                            <td>
                                <?php echo $item['quantity']; ?>
                            </td>

                            <td>
                                ৳ <?php
                                echo number_format(
                                    $item['price'] * $item['quantity'],
                                    2
                                );
                                ?>
                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

            <div class="cart-total">

                Total:
                ৳ <?php echo number_format($cartTotal, 2); ?>

            </div>

        <?php else: ?>

            <p>Your cart is empty.</p>

        <?php endif; ?>

    </div>


    <!--CHECKOUT-->

    <div class="section">

        <h2>Checkout</h2>

        <form
            method="POST"
            action=""
            class="checkout-form"
        >

            <input
                type="hidden"
                name="action"
                value="checkout"
            >

            <div class="form-group">

                <label for="name">
                    Full Name
                </label>

                <input
                    type="text"
                    id="name"
                    name="name"
                    placeholder="Enter your full name"
                    required
                >

            </div>


            <div class="form-group">

                <label for="email">
                    Email
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="Enter your email"
                    required
                >

            </div>


            <div class="form-group">

                <label for="phone">
                    Phone Number
                </label>

                <input
                    type="tel"
                    id="phone"
                    name="phone"
                    placeholder="Enter your phone number"
                    required
                >

            </div>


            <div class="form-group">

                <label for="address">
                    Delivery Address
                </label>

                <textarea
                    id="address"
                    name="address"
                    placeholder="Enter your delivery address"
                    required
                ></textarea>

            </div>


            <div class="form-group">

                <label for="payment">
                    Payment Method
                </label>

                <select
                    id="payment"
                    name="payment"
                    required
                >

                    <option value="">
                        Select payment method
                    </option>

                    <option value="cash">
                        Cash on Delivery
                    </option>

                    <option value="card">
                        Card
                    </option>

                    <option value="mobile_banking">
                        Mobile Banking
                    </option>

                </select>

            </div>


            <button
                type="submit"
                class="btn btn-success checkout-button"
            >
                Place Order
            </button>

        </form>

    </div>


    <!--ORDER HISTORY-->

    <div class="section">

        <h2>Order History</h2>

        <?php if (!empty($orderHistory)): ?>

            <table class="history-table">

                <thead>

                    <tr>
                        <th>Order ID</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>

                </thead>

                <tbody>

                    <?php foreach ($orderHistory as $order): ?>

                        <tr>

                            <td>
                                <?php echo htmlspecialchars($order['order_id']); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($order['date']); ?>
                            </td>

                            <td>
                                ৳ <?php echo number_format($order['total'], 2); ?>
                            </td>

                            <td>

                                <?php if ($order['status'] === 'Delivered'): ?>

                                    <span class="status delivered">
                                        Delivered
                                    </span>

                                <?php else: ?>

                                    <span class="status processing">
                                        <?php echo htmlspecialchars($order['status']); ?>
                                    </span>

                                <?php endif; ?>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        <?php else: ?>

            <p>No previous orders found.</p>

        <?php endif; ?>

    </div>

</div>

</body>

</html>