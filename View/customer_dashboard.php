```php
<?php
// MUST BE AT THE VERY TOP OF THE FILE
session_start();

// 1. LOGOUT LOGIC
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

// 2. PRODUCT DATA
$products = [
    ['id' => 1, 'name' => 'Argentina Jersey', 'price' => 1200],
    ['id' => 2, 'name' => 'Real Madrid Jersey', 'price' => 1350],
    ['id' => 3, 'name' => 'Barcelona Jersey', 'price' => 1250],
    ['id' => 4, 'name' => 'Arsenal Jersey', 'price' => 1150]
];

// Initialize Order History
if (!isset($_SESSION['placed_orders'])) {
    $_SESSION['placed_orders'] = [];
}

// 3. ORDER HANDLER
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['action']) &&
    $_POST['action'] === 'place_order') {

    $productId = (int)$_POST['product_id'];
    $size = $_POST['size'];
    $quantity = (int)$_POST['quantity'];
    $custName = trim($_POST['customer_name']);
    $phone = trim($_POST['phone_number']);
    $address = trim($_POST['address']);
    $payment = $_POST['payment_method'];

    $selectedProduct = null;

    foreach ($products as $p) {
        if ($p['id'] === $productId) {
            $selectedProduct = $p;
            break;
        }
    }

    if ($selectedProduct === null) {

        $message = "Error: Invalid product.";

    } elseif (!preg_match('/^01[0-9]{9}$/', $phone)) {

        $message = "Error: Phone number must be exactly 11 digits and start with 01.";

    } elseif ($quantity < 1) {

        $message = "Error: Quantity must be at least 1.";

    } elseif (empty($custName) || empty($address)) {

        $message = "Error: Please fill in all required fields.";

    } else {

        $total = $selectedProduct['price'] * $quantity;

        $_SESSION['placed_orders'][] = [
            'order_id' => 'ORD-' . rand(1000, 9999),
            'date' => date('Y-m-d'),
            'product' => $selectedProduct['name'],
            'size' => $size,
            'quantity' => $quantity,
            'customer' => $custName,
            'phone' => $phone,
            'address' => $address,
            'total' => $total,
            'payment' => $payment,
            'status' => 'Pending'
        ];

        $message = "Order placed successfully!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background: #f4f4f4;
        }

        .header {
            background: #222;
            color: white;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 5px;
        }

        .header h2 {
            margin: 0;
        }

        .logout-btn {
            background: #dc3545;
            color: white;
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
        }

        .section {
            background: white;
            padding: 20px;
            margin-top: 20px;
            border-radius: 5px;
        }

        .product-list {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .product-card {
            border: 1px solid #ccc;
            padding: 15px;
            width: 200px;
            text-align: center;
            border-radius: 5px;
            background: white;
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

        .product-image img {
            max-width: 100%;
            max-height: 100%;
        }

        .price {
            font-weight: bold;
            margin-bottom: 15px;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 10px;
            max-width: 400px;
        }

        input,
        select,
        textarea,
        button {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button {
            cursor: pointer;
            font-weight: bold;
        }

        .order-button {
            background: #28a745;
            color: white;
        }

        .place-order-button {
            background: #007bff;
            color: white;
        }

        .order-card {
            border: 1px solid #ddd;
            padding: 15px;
            margin-top: 15px;
            border-radius: 5px;
            background: #fafafa;
        }

        .order-card h4 {
            margin-top: 0;
        }

        .alert {
            padding: 10px;
            background: #e2e3e5;
            margin-top: 10px;
            font-weight: bold;
            border-radius: 4px;
        }

        @media (max-width: 600px) {
            .header {
                padding: 15px;
            }

            .section {
                padding: 15px;
            }

            .product-card {
                width: 100%;
            }
        }
    </style>
</head>

<body>

<!-- HEADER -->
<div class="header">

    <h2>Customer Dashboard</h2>

    <a href="customer_dashboard.php?action=logout"
       class="logout-btn"
       onclick="return confirm('Logout now?');">
        Logout
    </a>

</div>


<!-- MESSAGE -->
<?php if ($message): ?>

    <div class="alert">
        <?php echo htmlspecialchars($message); ?>
    </div>

<?php endif; ?>


<!-- 1. AVAILABLE PRODUCTS -->
<div class="section">

    <h3>Available Jerseys</h3>

    <div class="product-list">

        <?php foreach ($products as $product): ?>

            <div class="product-card">

                <div class="product-image">

                    <?php if (!empty($product['image'])): ?>

                        <img
                            src="<?php echo htmlspecialchars($product['image']); ?>"
                            alt="<?php echo htmlspecialchars($product['name']); ?>"
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

                <form method="GET" action="customer_dashboard.php">

                    <input
                        type="hidden"
                        name="action"
                        value="order"
                    >

                    <input
                        type="hidden"
                        name="product_id"
                        value="<?php echo $product['id']; ?>"
                    >

                    <button
                        type="submit"
                        class="order-button"
                    >
                        Order Now
                    </button>

                </form>

            </div>

        <?php endforeach; ?>

    </div>

</div>


<!-- 2. ORDER FORM -->
<?php

if (
    isset($_GET['action']) &&
    $_GET['action'] === 'order' &&
    isset($_GET['product_id'])
):

    $selectedId = (int)$_GET['product_id'];
    $selectedProduct = null;

    foreach ($products as $p) {

        if ($p['id'] === $selectedId) {
            $selectedProduct = $p;
            break;
        }

    }

    if ($selectedProduct !== null):

?>

<div class="section">

    <h3>Place Your Order</h3>

    <p>
        <strong>Jersey:</strong>
        <?php echo htmlspecialchars($selectedProduct['name']); ?>
    </p>

    <p>
        <strong>Price:</strong>
        ৳<?php echo number_format($selectedProduct['price'], 2); ?>
    </p>

    <form method="POST" action="customer_dashboard.php">

        <input
            type="hidden"
            name="action"
            value="place_order"
        >

        <input
            type="hidden"
            name="product_id"
            value="<?php echo $selectedProduct['id']; ?>"
        >


        <label>Size:</label>

        <select name="size" required>

            <option value="S">S</option>
            <option value="M" selected>M</option>
            <option value="L">L</option>
            <option value="XL">XL</option>

        </select>


        <label>Quantity:</label>

        <input
            type="number"
            name="quantity"
            min="1"
            value="1"
            required
        >


        <label>Customer Name:</label>

        <input
            type="text"
            name="customer_name"
            required
        >


        <label>Phone Number:</label>

        <input
            type="text"
            name="phone_number"
            placeholder="01712345678"
            maxlength="11"
            pattern="01[0-9]{9}"
            required
        >


        <label>Address:</label>

        <textarea
            name="address"
            required
        ></textarea>


        <label>Payment Method:</label>

        <select name="payment_method" required>

            <option value="Cash on Delivery">
                Cash on Delivery
            </option>

            <option value="Card Payment">
                Card Payment
            </option>

            <option value="Mobile Banking">
                Mobile Banking
            </option>

        </select>


        <button
            type="submit"
            class="place-order-button"
        >
            Place Order
        </button>

    </form>

</div>

<?php

    endif;

endif;

?>


<!-- 3. ORDER HISTORY -->
<div class="section">

    <h3>My Order History</h3>

    <?php if (!empty($_SESSION['placed_orders'])): ?>

        <?php foreach ($_SESSION['placed_orders'] as $order): ?>

            <div class="order-card">

                <h4>
                    Order ID:
                    <?php echo htmlspecialchars($order['order_id']); ?>
                </h4>

                <p>
                    <strong>Date:</strong>
                    <?php echo htmlspecialchars($order['date']); ?>
                </p>

                <p>
                    <strong>Jersey:</strong>
                    <?php echo htmlspecialchars($order['product']); ?>
                </p>

                <p>
                    <strong>Size:</strong>
                    <?php echo htmlspecialchars($order['size']); ?>
                </p>

                <p>
                    <strong>Quantity:</strong>
                    <?php echo htmlspecialchars($order['quantity']); ?>
                </p>

                <p>
                    <strong>Customer Name:</strong>
                    <?php echo htmlspecialchars($order['customer']); ?>
                </p>

                <p>
                    <strong>Phone:</strong>
                    <?php echo htmlspecialchars($order['phone']); ?>
                </p>

                <p>
                    <strong>Address:</strong>
                    <?php echo htmlspecialchars($order['address']); ?>
                </p>

                <p>
                    <strong>Total:</strong>
                    ৳<?php echo number_format($order['total'], 2); ?>
                </p>

                <p>
                    <strong>Payment:</strong>
                    <?php echo htmlspecialchars($order['payment']); ?>
                </p>

                <p>
                    <strong>Status:</strong>
                    <?php echo htmlspecialchars($order['status']); ?>
                </p>

            </div>

        <?php endforeach; ?>

    <?php else: ?>

        <p>No orders placed yet.</p>

    <?php endif; ?>

</div>

</body>
</html>
```
