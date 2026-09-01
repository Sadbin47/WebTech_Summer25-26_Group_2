<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (($_SESSION['role'] ?? '') !== 'Salesman') {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../Model/db.php';
require_once __DIR__ . '/../Model/JerseyModel.php';
require_once __DIR__ . '/../Model/OrderModel.php';

$connection = (new Database())->connect();
$jerseyModel = new JerseyModel($connection);
$orderModel = new OrderModel($connection);

if (!isset($_SESSION['sales_cart']) || !is_array($_SESSION['sales_cart'])) {
    $_SESSION['sales_cart'] = [];
}

$page = $_GET['page'] ?? 'sale';

if (!in_array($page, ['sale', 'payment'], true)) {
    $page = 'sale';
}

$confirmed = isset($_GET['confirmed']) && $_GET['confirmed'] === '1';
$cartSummary = $orderModel->getCartDetails($_SESSION['sales_cart']);

if ($page === 'payment' && (int) $cartSummary['total_quantity'] <= 0 && !$confirmed) {
    header('Location: salesman_dashboard.php?page=sale');
    exit;
}

$availableJerseys = $jerseyModel->getAvailableJerseys();
$salesHistory = $orderModel->getSalesmanOrders((int) $_SESSION['user_id']);
$monthlySales = $orderModel->getMonthlySalesBySalesman((int) $_SESSION['user_id']);

// Simple Salesman target progress for rubric/display.
$monthlyTarget = 100000.00;
$targetPercent = $monthlyTarget > 0
    ? min(100, ($monthlySales / $monthlyTarget) * 100)
    : 0;

$message = $_SESSION['salesman_message'] ?? '';
$error = $_SESSION['salesman_error'] ?? '';
unset($_SESSION['salesman_message'], $_SESSION['salesman_error']);

// Cookies are read to prefill the last customer's name and phone.
$lastCustomerName = htmlspecialchars($_COOKIE['last_customer_name'] ?? '');
$lastCustomerPhone = htmlspecialchars($_COOKIE['last_customer_phone'] ?? '');
$today = date('Y-m-d');

$jerseyNames = [];
foreach ($availableJerseys as $jersey) {
    $jerseyNames[$jersey['name']] = true;
}
$jerseyNames = array_keys($jerseyNames);

$jerseyJson = json_encode(
    $availableJerseys,
    JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salesman Dashboard - JerseyTrack</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #f4f7fb;
            color: #252b33;
            font-family: Arial, Helvetica, sans-serif;
        }

        .sales-main {
            width: 92%;
            max-width: 1180px;
            margin: auto;
            padding: 24px 0 45px;
        }

        .breadcrumb {
            margin-bottom: 7px;
            color: #777;
            font-size: 13px;
        }

        h1 {
            margin: 0 0 22px;
            color: #252b33;
            font-size: 26px;
            font-weight: 700;
        }

        h2 {
            margin: 0;
            color: #252b33;
            font-size: 19px;
            font-weight: 700;
        }

        .section-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 18px;
            padding-bottom: 12px;
            border-bottom: 1px solid #e5e8eb;
        }

        .section-heading p {
            margin: 4px 0 0;
            color: #777;
            font-size: 13px;
        }

        .message {
            margin-bottom: 18px;
            padding: 12px 14px;
            border-left: 4px solid #3cb371;
            border-radius: 6px;
            background: #edf8f2;
            color: #25734d;
            font-weight: 600;
        }

        .message.error {
            border-left-color: #c94b4b;
            background: #fff0f0;
            color: #9d3131;
        }

        .panel {
            margin-bottom: 24px;
            padding: 22px;
            border: 1px solid #e1e5e9;
            border-radius: 8px;
            background: #ffffff;
        }

        .target-card {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 18px;
            align-items: center;
            margin-bottom: 24px;
            padding: 20px 22px;
            border: 1px solid #e1e5e9;
            border-left: 4px solid #3cb371;
            border-radius: 8px;
            background: #ffffff;
        }

        .target-card small {
            color: #777;
            font-weight: 600;
        }

        .target-card strong {
            display: block;
            margin-top: 6px;
            color: #252b33;
            font-size: 20px;
        }

        .progress {
            width: 100%;
            height: 9px;
            margin-top: 12px;
            overflow: hidden;
            border-radius: 20px;
            background: #e8eeeb;
        }

        .progress div {
            height: 100%;
            border-radius: 20px;
            background: #3cb371;
        }

        .target-percent {
            min-width: 82px;
            text-align: center;
            color: #2e8b57;
            font-size: 21px;
            font-weight: 700;
        }

        .product-form {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr auto;
            gap: 12px;
            align-items: end;
            margin-bottom: 10px;
        }

        .customer-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            color: #333;
            font-size: 13px;
            font-weight: 700;
        }

        input,
        select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #cfd6dc;
            border-radius: 5px;
            background: #ffffff;
            color: #252b33;
            font: inherit;
            outline: none;
        }

        input::placeholder {
            color: #9a9a9a;
        }

        input:focus,
        select:focus {
            border-color: #3cb371;
            box-shadow: 0 0 0 2px rgba(60, 179, 113, .10);
        }

        input[readonly] {
            background: #f5f7f8;
            color: #252b33;
            font-weight: 600;
        }

        button,
        .button-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 40px;
            padding: 9px 16px;
            border: 1px solid #3cb371;
            border-radius: 5px;
            background: #3cb371;
            color: #ffffff;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: .2s ease;
        }

        button:hover,
        .button-link:hover {
            border-color: #2e8b57;
            background: #2e8b57;
        }

        button:disabled {
            border-color: #a7cdb8;
            background: #a7cdb8;
            opacity: .75;
            cursor: not-allowed;
        }

        .button-outline {
            border-color: #3cb371;
            background: #ffffff;
            color: #2e8b57;
        }

        .button-outline:hover {
            background: #edf8f2;
            color: #25734d;
        }

        .button-danger {
            border-color: #3cb371;
            background: #ffffff;
            color: #2e8b57;
        }

        .button-danger:hover {
            border-color: #2e8b57;
            background: #edf8f2;
            color: #25734d;
        }

        .product-info {
            min-height: 20px;
            margin: 5px 0 18px;
            padding: 10px 12px;
            border-left: 3px solid #3cb371;
            background: #f5faf7;
            color: #5f6a64;
            font-size: 13px;
        }

        .search-row {
            display: grid;
            grid-template-columns: 1fr 220px;
            gap: 12px;
            margin: 16px 0;
        }

        .table-wrap {
            overflow-x: auto;
            border: 1px solid #e1e5e9;
            border-radius: 7px;
            background: #ffffff;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 760px;
        }

        .data-table th,
        .data-table td {
            padding: 13px 14px;
            border-bottom: 1px solid #e5e8eb;
            text-align: left;
            vertical-align: middle;
        }

        .data-table th {
            background: #f6f8f9;
            color: #2f3a35;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .2px;
            white-space: nowrap;
        }

        .data-table td {
            color: #38413d;
            font-size: 14px;
        }

        .data-table tbody tr:last-child td {
            border-bottom: none;
        }

        .data-table tbody tr:hover {
            background: #f8fcfa;
        }

        .keyword {
            color: #252b33;
            font-weight: 700;
        }

        .muted-text {
            color: #777;
            font-size: 12px;
        }

        .money {
            color: #24734c;
            font-weight: 700;
            white-space: nowrap;
        }

        .qty-form {
            display: flex;
            gap: 7px;
            align-items: center;
        }

        .qty-form input {
            width: 74px;
            min-width: 74px;
        }

        .qty-form button {
            min-height: 35px;
            padding: 7px 10px;
        }

        .order-summary {
            display: flex;
            justify-content: flex-end;
            gap: 16px;
            margin-top: 18px;
            padding-top: 16px;
            border-top: 1px solid #e5e8eb;
        }

        .summary-total {
            min-width: 170px;
            padding: 14px 16px;
            border: 1px solid #e1e5e9;
            border-radius: 7px;
            background: #f8fbf9;
        }

        .order-summary span {
            color: #777;
            font-size: 12px;
            font-weight: 600;
        }

        .order-summary strong {
            display: block;
            margin-top: 5px;
            color: #252b33;
            font-size: 20px;
        }

        .checkout-row {
            display: flex;
            justify-content: flex-end;
            margin-top: 16px;
        }

        .payment-summary {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-top: 18px;
        }

        .summary-box {
            padding: 15px 16px;
            border: 1px solid #e1e5e9;
            border-left: 4px solid #3cb371;
            border-radius: 7px;
            background: #f8fbf9;
        }

        .summary-box span {
            display: block;
            margin-bottom: 5px;
            color: #777;
            font-size: 12px;
            font-weight: 600;
        }

        .summary-box strong {
            color: #252b33;
            font-size: 20px;
        }

        .payment-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .phone-message {
            min-height: 18px;
            margin-top: 6px;
            color: #777;
            font-size: 12px;
            font-weight: 600;
        }

        .phone-message.success {
            color: #2e8b57;
        }

        .phone-message.error {
            color: #c94b4b;
        }

        .status {
            display: inline-block;
            padding: 5px 10px;
            border: 1px solid #a7d8be;
            border-radius: 20px;
            background: #edf8f2;
            color: #25734d;
            font-size: 12px;
            font-weight: 700;
        }

        .empty {
            padding: 26px 10px !important;
            color: #777 !important;
            text-align: center !important;
        }

        @media (max-width: 760px) {
            .sales-main {
                width: 95%;
            }

            .product-form,
            .customer-grid,
            .payment-summary,
            .search-row {
                grid-template-columns: 1fr;
            }

            .target-card {
                grid-template-columns: 1fr;
            }

            .target-percent {
                text-align: left;
            }

            .order-summary,
            .payment-actions {
                flex-direction: column;
            }

            .summary-total {
                width: 100%;
            }
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>

<main class="sales-main">
    <?php if ($message !== ''): ?>
        <div class="message">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <div class="message error">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if ($page === 'sale'): ?>
        <div class="breadcrumb">Salesman Dashboard / New Sale</div>
        <h1>Build Order</h1>

        <section class="target-card">
            <div>
                <small>Monthly Sales Target</small>
                <strong>
                    BDT <?php echo number_format($monthlySales, 0); ?>
                    / BDT <?php echo number_format($monthlyTarget, 0); ?>
                </strong>
                <div class="progress">
                    <div style="width: <?php echo number_format($targetPercent, 2, '.', ''); ?>%;"></div>
                </div>
            </div>
            <div class="target-percent">
                <?php echo number_format($targetPercent, 0); ?>%
            </div>
        </section>

        <section class="panel" id="sale">
            <div class="section-heading">
                <div>
                    <h2>Select Jersey</h2>
                    <p>Choose a jersey, size and quantity to build the current order.</p>
                </div>
            </div>

            <form
                class="product-form"
                method="POST"
                action="../Controller/SalesmanController.php"
                onsubmit="return validateAddProduct()"
            >
                <input type="hidden" name="action" value="add_to_cart">
                <input type="hidden" id="jerseyId" name="jersey_id" value="">

                <div>
                    <label for="jerseyName">Jersey Name</label>
                    <select id="jerseyName" required>
                        <option value="">Select jersey</option>
                        <?php foreach ($jerseyNames as $jerseyName): ?>
                            <option value="<?php echo htmlspecialchars($jerseyName); ?>">
                                <?php echo htmlspecialchars($jerseyName); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="jerseySize">Size</label>
                    <select id="jerseySize" required disabled>
                        <option value="">Select size</option>
                    </select>
                </div>

                <div>
                    <label for="saleQuantity">Quantity</label>
                    <input
                        id="saleQuantity"
                        type="number"
                        name="quantity"
                        min="1"
                        value="1"
                        required
                    >
                </div>

                <button type="submit">Add to Order</button>
            </form>

            <div id="productInfo" class="product-info">
                Select a jersey and size to see price and stock.
            </div>

            <div class="search-row">
                <input
                    id="cartSearch"
                    type="text"
                    placeholder="Search selected jerseys by name..."
                    oninput="filterCart()"
                >

                <select id="cartSort" onchange="sortCart()">
                    <option value="name_asc">Jersey Name A-Z</option>
                    <option value="name_desc">Jersey Name Z-A</option>
                    <option value="qty_asc">Quantity Low-High</option>
                    <option value="qty_desc">Quantity High-Low</option>
                </select>
            </div>

            <div class="table-wrap">
                <table id="cartTable" class="data-table">
                    <thead>
                        <tr>
                            <th>Jersey Name</th>
                            <th>Size</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Subtotal</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!$cartSummary['items']): ?>
                        <tr class="empty-row">
                            <td colspan="6" class="empty">
                                No jerseys selected yet.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($cartSummary['items'] as $item): ?>
                            <tr
                                class="cart-item-row"
                                data-name="<?php echo htmlspecialchars(strtolower($item['name'])); ?>"
                                data-quantity="<?php echo (int) $item['quantity']; ?>"
                            >
                                <td><span class="keyword"><?php echo htmlspecialchars($item['name']); ?></span></td>
                                <td><?php echo htmlspecialchars($item['size']); ?></td>
                                <td>
                                    <form
                                        class="qty-form"
                                        method="POST"
                                        action="../Controller/SalesmanController.php"
                                    >
                                        <input type="hidden" name="action" value="update_quantity">
                                        <input type="hidden" name="jersey_id" value="<?php echo (int) $item['id']; ?>">
                                        <input
                                            type="number"
                                            name="quantity"
                                            min="1"
                                            max="<?php echo (int) $item['available_stock']; ?>"
                                            value="<?php echo (int) $item['quantity']; ?>"
                                            required
                                        >
                                        <button type="submit" class="button-outline">Update</button>
                                    </form>
                                </td>
                                <td><span class="money">BDT <?php echo number_format((float) $item['unit_price'], 2); ?></span></td>
                                <td><span class="money">BDT <?php echo number_format((float) $item['subtotal'], 2); ?></span></td>
                                <td>
                                    <form method="POST" action="../Controller/SalesmanController.php">
                                        <input type="hidden" name="action" value="remove_item">
                                        <input type="hidden" name="jersey_id" value="<?php echo (int) $item['id']; ?>">
                                        <button type="submit" class="button-danger">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="order-summary">
                <div class="summary-total">
                    <span>Total Jerseys</span>
                    <strong id="totalQuantityText">
                        <?php echo (int) $cartSummary['total_quantity']; ?>
                    </strong>
                </div>
                <div class="summary-total">
                    <span>Total Price</span>
                    <strong>
                        BDT <?php echo number_format((float) $cartSummary['subtotal'], 2); ?>
                    </strong>
                </div>
            </div>

            <div class="checkout-row">
                <form
                    method="POST"
                    action="../Controller/SalesmanController.php"
                    onsubmit="return validateProceed()"
                >
                    <input type="hidden" name="action" value="go_payment">
                    <button
                        type="submit"
                        <?php echo (int) $cartSummary['total_quantity'] <= 0 ? 'disabled' : ''; ?>
                    >
                        Proceed to Payment
                    </button>
                </form>
            </div>
        </section>

        <section class="panel" id="history">
            <div class="section-heading">
                <div>
                    <h2>Sales History</h2>
                    <p>Review your previously confirmed sales records.</p>
                </div>
            </div>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Customer</th>
                            <th>Purchased Jerseys</th>
                            <th>Qty</th>
                            <th>Total</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!$salesHistory): ?>
                        <tr>
                            <td colspan="7" class="empty">No sales recorded yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($salesHistory as $sale): ?>
                            <tr>
                                <td><span class="keyword">#<?php echo (int) $sale['id']; ?></span></td>
                                <td>
                                    <span class="keyword"><?php echo htmlspecialchars($sale['customer_name'] ?? ''); ?></span><br>
                                    <span class="muted-text"><?php echo htmlspecialchars($sale['customer_phone'] ?? ''); ?></span>
                                </td>
                                <td><?php echo htmlspecialchars($sale['purchased_jerseys'] ?? ''); ?></td>
                                <td><?php echo (int) $sale['total_quantity']; ?></td>
                                <td><span class="money">BDT <?php echo number_format((float) $sale['total_amount'], 2); ?></span></td>
                                <td><?php echo htmlspecialchars($sale['purchase_date'] ?? $sale['created_at']); ?></td>
                                <td><span class="status"><?php echo htmlspecialchars($sale['status']); ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

    <?php else: ?>
        <div class="breadcrumb">Salesman Dashboard / New Sale / Payment</div>
        <h1>Customer Information & Payment</h1>

        <?php if ((int) $cartSummary['total_quantity'] > 0): ?>
            <section class="panel">
                <div class="section-heading">
                    <div>
                        <h2>Customer Information</h2>
                        <p>Enter customer details and review the order total before confirmation.</p>
                    </div>
                </div>

                <form
                    id="paymentForm"
                    method="POST"
                    action="../Controller/SalesmanController.php"
                    onsubmit="return validatePayment()"
                >
                    <input type="hidden" name="action" value="confirm_order">

                    <div class="customer-grid">
                        <div>
                            <label for="customerName">Customer Name</label>
                            <input
                                id="customerName"
                                type="text"
                                name="customer_name"
                                value="<?php echo $lastCustomerName; ?>"
                                placeholder="Enter customer name"
                                minlength="2"
                                required
                            >
                        </div>

                        <div>
                            <label for="customerPhone">Phone Number</label>
                            <input
                                id="customerPhone"
                                type="text"
                                name="customer_phone"
                                value="<?php echo $lastCustomerPhone; ?>"
                                placeholder="01XXXXXXXXX"
                                maxlength="15"
                                required
                            >
                            <div id="phoneMessage" class="phone-message"></div>
                        </div>

                        <div>
                            <label for="customerEmail">Email Address</label>
                            <input
                                id="customerEmail"
                                type="email"
                                name="customer_email"
                                placeholder="name@email.com"
                            >
                        </div>

                        <div>
                            <label for="purchaseDate">Date of Purchase</label>
                            <input
                                id="purchaseDate"
                                type="date"
                                name="purchase_date"
                                value="<?php echo $today; ?>"
                                max="<?php echo $today; ?>"
                                required
                            >
                        </div>
                    </div>

                    <div class="payment-summary">
                        <div class="summary-box">
                            <span>Total Quantity</span>
                            <strong><?php echo (int) $cartSummary['total_quantity']; ?></strong>
                        </div>

                        <div class="summary-box">
                            <span>Total Price</span>
                            <strong>BDT <?php echo number_format((float) $cartSummary['subtotal'], 2); ?></strong>
                        </div>
                    </div>

                    <div class="payment-actions">
                        <button type="submit">Confirm Order</button>
                    </div>
                </form>

                <form
                    method="POST"
                    action="../Controller/SalesmanController.php"
                    onsubmit="return confirm('Cancel this current transaction?');"
                    style="margin-top: 10px;"
                >
                    <input type="hidden" name="action" value="cancel_order">
                    <button type="submit" class="button-danger">Exit</button>
                </form>
            </section>
        <?php endif; ?>

        <section class="panel" id="confirmed-orders">
            <div class="section-heading">
                <div>
                    <h2>Confirmed Orders</h2>
                    <p>Customer and order details saved from completed sales.</p>
                </div>
            </div>

            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Customer Name</th>
                            <th>Phone Number</th>
                            <th>Email Address</th>
                            <th>Purchased Jerseys</th>
                            <th>Total Qty</th>
                            <th>Total Price</th>
                            <th>Purchase Date</th>
                            <th>Order Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!$salesHistory): ?>
                        <tr>
                            <td colspan="8" class="empty">No confirmed sales yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($salesHistory as $sale): ?>
                            <tr>
                                <td><span class="keyword"><?php echo htmlspecialchars($sale['customer_name'] ?? ''); ?></span></td>
                                <td><?php echo htmlspecialchars($sale['customer_phone'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($sale['customer_email'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($sale['purchased_jerseys'] ?? ''); ?></td>
                                <td><?php echo (int) $sale['total_quantity']; ?></td>
                                <td><span class="money">BDT <?php echo number_format((float) $sale['total_amount'], 2); ?></span></td>
                                <td><?php echo htmlspecialchars($sale['purchase_date'] ?? $sale['created_at']); ?></td>
                                <td><span class="status"><?php echo htmlspecialchars($sale['status']); ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 18px;">
                <a class="button-link button-outline" href="salesman_dashboard.php?page=sale">
                    Back to New Sale
                </a>
            </div>
        </section>
    <?php endif; ?>
</main>

<script>
const jerseyData = <?php echo $jerseyJson ?: '[]'; ?>;

function initializeJerseySelector()
{
    const jerseyName = document.getElementById('jerseyName');
    const jerseySize = document.getElementById('jerseySize');

    if (!jerseyName || !jerseySize) {
        return;
    }

    jerseyName.addEventListener('change', function () {
        loadSizesForJersey();
    });

    jerseySize.addEventListener('change', function () {
        updateSelectedJersey();
    });
}

function loadSizesForJersey()
{
    const jerseyName = document.getElementById('jerseyName');
    const jerseySize = document.getElementById('jerseySize');
    const jerseyId = document.getElementById('jerseyId');
    const productInfo = document.getElementById('productInfo');

    if (!jerseyName || !jerseySize || !jerseyId) {
        return;
    }

    const selectedName = jerseyName.value;

    jerseySize.innerHTML = '<option value="">Select size</option>';
    jerseyId.value = '';

    if (!selectedName) {
        jerseySize.disabled = true;
        if (productInfo) {
            productInfo.textContent = 'Select a jersey and size to see price and stock.';
        }
        return;
    }

    const variants = jerseyData.filter(function (item) {
        return item.name === selectedName;
    });

    variants.forEach(function (item) {
        const option = document.createElement('option');
        option.value = item.size;
        option.textContent = item.size;
        jerseySize.appendChild(option);
    });

    jerseySize.disabled = false;
}

function updateSelectedJersey()
{
    const jerseyName = document.getElementById('jerseyName');
    const jerseySize = document.getElementById('jerseySize');
    const jerseyId = document.getElementById('jerseyId');
    const quantity = document.getElementById('saleQuantity');
    const productInfo = document.getElementById('productInfo');

    if (!jerseyName || !jerseySize || !jerseyId) {
        return;
    }

    const selected = jerseyData.find(function (item) {
        return item.name === jerseyName.value && item.size === jerseySize.value;
    });

    if (!selected) {
        jerseyId.value = '';
        if (productInfo) {
            productInfo.textContent = 'Select a jersey and size to see price and stock.';
        }
        return;
    }

    jerseyId.value = selected.id;

    if (quantity) {
        quantity.max = selected.quantity;
    }

    if (productInfo) {
        productInfo.textContent =
            'Price: BDT ' + Number(selected.price).toFixed(2) +
            ' | Available stock: ' + selected.quantity +
            ' | Category: ' + selected.category;
    }
}

function validateAddProduct()
{
    const jerseyId = document.getElementById('jerseyId');
    const quantity = document.getElementById('saleQuantity');

    if (!jerseyId || jerseyId.value === '') {
        alert('Please select a jersey and size.');
        return false;
    }

    const qty = Number(quantity.value);

    if (!Number.isInteger(qty) || qty <= 0) {
        alert('Quantity must be at least 1.');
        return false;
    }

    if (quantity.max && qty > Number(quantity.max)) {
        alert('Quantity cannot be greater than available stock.');
        return false;
    }

    return true;
}

function validateProceed()
{
    const totalQuantity = Number(
        document.getElementById('totalQuantityText')?.textContent || 0
    );

    if (totalQuantity <= 0) {
        alert('Add at least one jersey before checkout.');
        return false;
    }

    return true;
}

function filterCart()
{
    const search = (document.getElementById('cartSearch')?.value || '')
        .trim()
        .toLowerCase();

    document.querySelectorAll('.cart-item-row').forEach(function (row) {
        const name = row.dataset.name || '';
        row.style.display = name.includes(search) ? '' : 'none';
    });
}

function sortCart()
{
    const tableBody = document.querySelector('#cartTable tbody');
    const sortValue = document.getElementById('cartSort')?.value || 'name_asc';

    if (!tableBody) {
        return;
    }

    const rows = Array.from(tableBody.querySelectorAll('.cart-item-row'));

    if (rows.length === 0) {
        return;
    }

    rows.sort(function (a, b) {
        if (sortValue === 'qty_asc') {
            return Number(a.dataset.quantity) - Number(b.dataset.quantity);
        }

        if (sortValue === 'qty_desc') {
            return Number(b.dataset.quantity) - Number(a.dataset.quantity);
        }

        const nameA = a.dataset.name || '';
        const nameB = b.dataset.name || '';

        if (sortValue === 'name_desc') {
            return nameB.localeCompare(nameA);
        }

        return nameA.localeCompare(nameB);
    });

    rows.forEach(function (row) {
        tableBody.appendChild(row);
    });
}

async function checkCustomerPhone()
{
    const phoneInput = document.getElementById('customerPhone');
    const messageBox = document.getElementById('phoneMessage');
    const customerName = document.getElementById('customerName');
    const customerEmail = document.getElementById('customerEmail');

    if (!phoneInput || !messageBox) {
        return;
    }

    const phone = phoneInput.value.trim();
    const phoneDigits = phone.replace(/\D/g, '');

    // JavaScript validation before AJAX request.
    if (phoneDigits.length < 10 || phoneDigits.length > 15) {
        messageBox.className = 'phone-message error';
        messageBox.textContent = 'Enter a valid phone number.';
        return;
    }

    messageBox.className = 'phone-message';
    messageBox.textContent = 'Checking customer...';

    const formData = new FormData();
    formData.append('action', 'check_phone');
    formData.append('phone', phoneDigits);

    try {
        // AJAX request without reloading the page.
        const response = await fetch(
            '../Controller/SalesmanController.php',
            {
                method: 'POST',
                body: formData
            }
        );

        // Controller returns JSON.
        const data = await response.json();

        if (!response.ok) {
            messageBox.className = 'phone-message error';
            messageBox.textContent = data.message || 'Phone checking failed.';
            return;
        }

        if (data.found) {
            if (customerName) {
                customerName.value = data.customer_name || '';
            }

            if (customerEmail) {
                customerEmail.value = data.customer_email || '';
            }

            messageBox.className = 'phone-message success';
            messageBox.textContent = data.message;
        } else {
            // Do not clear typed name/email automatically for a new number.
            messageBox.className = 'phone-message';
            messageBox.textContent = data.message;
        }
    } catch (error) {
        messageBox.className = 'phone-message error';
        messageBox.textContent = 'Unable to check phone number.';
    }
}

function validatePayment()
{
    const name = document.getElementById('customerName')?.value.trim() || '';
    const phone = document.getElementById('customerPhone')?.value.trim() || '';
    const email = document.getElementById('customerEmail')?.value.trim() || '';
    const date = document.getElementById('purchaseDate')?.value || '';
    const phoneDigits = phone.replace(/\D/g, '');

    let message = '';

    if (name.length < 2) {
        message += 'Customer name must be at least 2 characters.\n';
    }

    if (phoneDigits.length < 10 || phoneDigits.length > 15) {
        message += 'Enter a valid phone number.\n';
    }

    if (email !== '' && !/^\S+@\S+\.\S+$/.test(email)) {
        message += 'Enter a valid email address.\n';
    }

    if (date === '') {
        message += 'Purchase date is required.\n';
    }

    if (message !== '') {
        alert(message);
        return false;
    }

    return confirm('Confirm this order?');
}

document.addEventListener('DOMContentLoaded', function () {
    initializeJerseySelector();

    const customerPhone = document.getElementById('customerPhone');

    if (customerPhone) {
        customerPhone.addEventListener('blur', checkCustomerPhone);
    }
});
</script>
</body>
</html>
