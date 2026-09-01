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

$monthlyTarget = 100000.00;
$targetPercent = $monthlyTarget > 0 ? min(100, ($monthlySales / $monthlyTarget) * 100) : 0;

$message = $_SESSION['salesman_message'] ?? '';
$error = $_SESSION['salesman_error'] ?? '';
unset($_SESSION['salesman_message'], $_SESSION['salesman_error']);

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
        * { box-sizing: border-box; }

        body {
            margin: 0;
            background: #121212;
            color: #f1f1f1;
            font-family: Arial, Helvetica, sans-serif;
        }

        .sales-main {
            width: 94%;
            max-width: 1180px;
            margin: auto;
            padding: 22px 0 45px;
        }

        .breadcrumb {
            margin-bottom: 5px;
            color: #b7b7b7;
            font-size: 14px;
        }

        h1 {
            margin: 0 0 24px;
            font-size: 24px;
        }

        h2 {
            margin: 0 0 18px;
            font-size: 19px;
        }

        .message {
            margin-bottom: 18px;
            padding: 12px 14px;
            border: 1px solid #245d3d;
            border-radius: 7px;
            background: #173323;
            color: #bce8cb;
        }

        .message.error {
            border-color: #813838;
            background: #3b1d1d;
            color: #ffbebe;
        }

        .panel {
            margin-bottom: 24px;
            padding: 20px;
            border: 1px solid #353535;
            border-radius: 12px;
            background: #181818;
        }

        .target-card {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 16px;
            align-items: center;
            margin-bottom: 24px;
            padding: 18px 20px;
            border: 1px solid #353535;
            border-radius: 10px;
            background: #181818;
        }

        .target-card small { color: #a9a9a9; }
        .target-card strong { display: block; margin-top: 5px; font-size: 21px; }

        .progress {
            width: 100%;
            height: 8px;
            margin-top: 11px;
            overflow: hidden;
            border-radius: 20px;
            background: #303030;
        }

        .progress div {
            height: 100%;
            border-radius: 20px;
            background: #2f7edb;
        }

        .target-percent {
            min-width: 78px;
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            color: #8dc1ff;
        }

        .product-form {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr auto;
            gap: 12px;
            align-items: end;
            margin-bottom: 12px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            color: #d4d4d4;
            font-size: 13px;
            font-weight: bold;
        }

        input, select, textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #3b3b3b;
            border-radius: 7px;
            background: #1b1b1b;
            color: #f5f5f5;
            font: inherit;
            outline: none;
        }

        input:focus, select:focus, textarea:focus {
            border-color: #2f7edb;
        }

        input[readonly] {
            color: #e7e7e7;
            background: #202020;
        }

        textarea { min-height: 80px; resize: vertical; }

        .product-info {
            min-height: 20px;
            margin-bottom: 18px;
            color: #9f9f9f;
            font-size: 13px;
        }

        button, .button-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            min-height: 39px;
            padding: 9px 16px;
            border: 1px solid transparent;
            border-radius: 8px;
            background: #2f7edb;
            color: #fff;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
        }

        button:hover, .button-link:hover { filter: brightness(1.08); }
        button:disabled { opacity: .45; cursor: not-allowed; }

        .button-outline {
            border-color: #484848;
            background: transparent;
            color: #ededed;
        }

        .button-danger {
            border-color: #8f2c2c;
            background: transparent;
            color: #ff7474;
        }

        .search-row {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 12px;
            align-items: center;
            margin: 15px 0 8px;
        }

        .search-count { color: #c9c9c9; font-size: 14px; }

        .table-wrap { overflow-x: auto; }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 13px 10px;
            border-bottom: 1px solid #333;
            text-align: left;
            vertical-align: middle;
        }

        th {
            color: #cfcfcf;
            font-size: 13px;
        }

        td { font-size: 14px; }

        .sort-button {
            min-height: 0;
            padding: 0;
            border: 0;
            background: transparent;
            color: #cfcfcf;
        }

        .qty-form {
            display: flex;
            gap: 6px;
            align-items: center;
        }

        .qty-form input {
            width: 68px;
            padding: 8px;
        }

        .qty-form button { min-height: 34px; padding: 7px 10px; }

        .empty-row {
            padding: 28px 10px;
            text-align: center;
            color: #8c8c8c;
        }

        .order-total {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 20px;
            align-items: end;
            margin-top: 22px;
            padding-top: 18px;
            border-top: 1px solid #343434;
        }

        .order-total span {
            display: block;
            margin-bottom: 5px;
            color: #bdbdbd;
            font-size: 13px;
        }

        .order-total strong { font-size: 22px; }
        .price-box { text-align: right; }

        .proceed-form { margin-top: 16px; }
        .proceed-form button { width: 100%; }

        .two-column {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        .field-full { grid-column: 1 / -1; }

        .promo-row {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 9px;
        }

        .promo-message {
            min-height: 18px;
            margin-top: 6px;
            color: #9db9d8;
            font-size: 12px;
        }

        .payment-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 18px;
        }

        .payment-actions button { width: 100%; }

        .small-text { color: #999; font-size: 12px; }
        .status { font-weight: bold; color: #ffcf73; }

        .section-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .section-heading a { color: #8dc1ff; text-decoration: none; font-size: 13px; }

        @media (max-width: 800px) {
            .product-form, .two-column {
    grid-template-columns: 1fr;
}
            .field-full { grid-column: auto; }
            .order-total { grid-template-columns: 1fr 1fr; }
        }

        @media (max-width: 520px) {
            .sales-main { width: 92%; }
            .target-card { grid-template-columns: 1fr; }
            .target-percent { text-align: left; }
            .payment-actions, .order-total { grid-template-columns: 1fr; }
            .price-box { text-align: left; }
        }
    </style>
</head>
<body data-cart-quantity="<?php echo (int) $cartSummary['total_quantity']; ?>">
<?php include 'header.php'; ?>

<main class="sales-main">
    <?php if ($message !== ''): ?>
        <div class="message"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <div class="message error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($page === 'sale'): ?>
        <div class="breadcrumb">Salesman dashboard / New order</div>
        <h1>Build order</h1>

        <section class="target-card">
            <div>
                <small>Monthly sales target</small>
                <strong>BDT <?php echo number_format($monthlySales, 0); ?> / <?php echo number_format($monthlyTarget, 0); ?></strong>
                <div class="progress">
                    <div style="width: <?php echo number_format($targetPercent, 2, '.', ''); ?>%;"></div>
                </div>
            </div>
            <div class="target-percent"><?php echo number_format($targetPercent, 0); ?>%</div>
        </section>

        <section class="panel" id="sale">
            <form class="product-form" method="POST" action="../Controller/SalesmanController.php" onsubmit="return validateAddItem()">
                <input type="hidden" name="action" value="add_to_cart">
                <input type="hidden" id="jersey_id" name="jersey_id" value="">

                <div>
                    <label for="jersey_name">Jersey</label>
                    <select id="jersey_name" required>
                        <?php if (!$jerseyNames): ?>
                            <option value="">No jersey available</option>
                        <?php else: ?>
                            <?php foreach ($jerseyNames as $name): ?>
                                <option value="<?php echo htmlspecialchars($name); ?>"><?php echo htmlspecialchars($name); ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div>
                    <label for="jersey_size">Size</label>
                    <select id="jersey_size" required></select>
                </div>

                <div>
                    <label for="sale_quantity">Qty</label>
                    <input id="sale_quantity" type="number" name="quantity" min="1" value="1" required>
                </div>

                <button type="submit" <?php echo !$jerseyNames ? 'disabled' : ''; ?>>+ Add to order</button>
            </form>

            <div class="product-info" id="productInfo"></div>

            <div class="search-row">
                <input type="text" id="cartSearch" placeholder="Search this order by jersey name">
                <div class="search-count"><span id="visibleItemCount"><?php echo count($cartSummary['items']); ?></span> items</div>
            </div>

            <div class="table-wrap">
                <table id="cartTable">
                    <thead>
                        <tr>
                            <th><button class="sort-button" type="button" onclick="sortCart('name')">Jersey name ⇅</button></th>
                            <th>Size</th>
                            <th>Unit price</th>
                            <th><button class="sort-button" type="button" onclick="sortCart('quantity')">Quantity ⇅</button></th>
                            <th>Subtotal</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="cartBody">
                    <?php if (!$cartSummary['items']): ?>
                        <tr class="no-cart-items"><td colspan="6" class="empty-row">No jersey added to this order yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($cartSummary['items'] as $item): ?>
                            <tr class="cart-row"
                                data-name="<?php echo htmlspecialchars(strtolower($item['name'])); ?>"
                                data-quantity="<?php echo (int) $item['quantity']; ?>">
                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                <td><?php echo htmlspecialchars($item['size']); ?></td>
                                <td>৳<?php echo number_format((float) $item['unit_price'], 0); ?></td>
                                <td>
                                    <form class="qty-form" method="POST" action="../Controller/SalesmanController.php">
                                        <input type="hidden" name="action" value="update_quantity">
                                        <input type="hidden" name="jersey_id" value="<?php echo (int) $item['id']; ?>">
                                        <input type="number" name="quantity" min="1" max="<?php echo (int) $item['available_stock']; ?>" value="<?php echo (int) $item['quantity']; ?>" required>
                                        <button class="button-outline" type="submit">Update</button>
                                    </form>
                                </td>
                                <td>৳<?php echo number_format((float) $item['subtotal'], 0); ?></td>
                                <td>
                                    <form method="POST" action="../Controller/SalesmanController.php" onsubmit="return confirm('Remove this jersey from the order?');">
                                        <input type="hidden" name="action" value="remove_item">
                                        <input type="hidden" name="jersey_id" value="<?php echo (int) $item['id']; ?>">
                                        <button class="button-danger" type="submit">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="order-total">
                <div>
                    <span>Total quantity</span>
                    <strong><?php echo (int) $cartSummary['total_quantity']; ?></strong>
                </div>
                <div class="price-box">
                    <span>Total price</span>
                    <strong>৳<?php echo number_format((float) $cartSummary['subtotal'], 0); ?></strong>
                </div>
            </div>

            <form class="proceed-form" method="POST" action="../Controller/SalesmanController.php" onsubmit="return validateCheckout()">
                <input type="hidden" name="action" value="go_payment">
                <button type="submit" <?php echo (int) $cartSummary['total_quantity'] <= 0 ? 'disabled' : ''; ?>>Proceed to payment →</button>
            </form>
        </section>

        <section class="panel" id="history">
            <div class="section-heading">
                <h2>Sales history</h2>
                <a href="#sale">Back to current order</a>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Purchased jerseys</th>
                            <th>Qty</th>
                            <th>Total price</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!$salesHistory): ?>
                        <tr><td colspan="8" class="empty-row">No confirmed sales yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($salesHistory as $order): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($order['customer_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($order['customer_phone'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($order['customer_email'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($order['purchased_jerseys'] ?? 'N/A'); ?></td>
                                <td><?php echo (int) $order['total_quantity']; ?></td>
                                <td>৳<?php echo number_format((float) $order['total_amount'], 0); ?></td>
                                <td><?php echo htmlspecialchars($order['purchase_date'] ?? substr($order['created_at'], 0, 10)); ?></td>
                                <td><span class="status"><?php echo htmlspecialchars($order['status']); ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>       

    <?php else: ?>
        <div class="breadcrumb">Salesman dashboard / New order / Payment</div>
        <h1>Customer details</h1>

        <section class="panel">
            <?php if ($confirmed && (int) $cartSummary['total_quantity'] <= 0): ?>
                <div class="message">The previous order is saved. Start a new sale when you are ready.</div>
            <?php endif; ?>

            <form id="paymentForm" method="POST" action="../Controller/SalesmanController.php" onsubmit="return validatePayment(event)">
                <div class="two-column">
                    <div class="field-full">
                        <label for="customer_name">Customer name</label>
                        <input id="customer_name" type="text" name="customer_name" value="<?php echo $lastCustomerName; ?>" placeholder="Full name" minlength="2" required>
                    </div>

                    <div>
                        <label for="customer_phone">Phone number</label>
                        <input id="customer_phone" type="text" name="customer_phone" value="<?php echo $lastCustomerPhone; ?>" placeholder="01XXX-XXXXXX" required>
                    </div>

                    <div>
                        <label for="customer_email">Email address</label>
                        <input id="customer_email" type="email" name="customer_email" placeholder="name@email.com">
                    </div>

                    <div>
                        <label for="payment_quantity">Total quantity</label>
                        <input id="payment_quantity" type="number" value="<?php echo (int) $cartSummary['total_quantity']; ?>" readonly>
                    </div>

                    <div>
                        <label for="totalPrice">Total price (৳)</label>
                        <input id="totalPrice" type="number" step="0.01" value="<?php echo number_format((float) $cartSummary['subtotal'], 2, '.', ''); ?>" readonly>
                    </div>

                    <div>
                        <label for="promoInput">Promo code</label>
                        <div class="promo-row">
                            <input id="promoInput" type="text" name="promo_code" maxlength="30" placeholder="Example: JT10">
                            <button type="button" onclick="applyPromo()">Apply promo</button>
                        </div>
                        <div class="promo-message" id="promoMessage">Promo is checked using AJAX without reloading the page.</div>
                    </div>

                    <div>
                        <label for="purchase_date">Date of purchase</label>
                        <input id="purchase_date" type="date" name="purchase_date" max="<?php echo $today; ?>" value="<?php echo $today; ?>" required>
                    </div>
                </div>

                <div class="payment-actions">
                    <button type="submit" name="action" value="confirm_order" <?php echo (int) $cartSummary['total_quantity'] <= 0 ? 'disabled' : ''; ?>>✓ Confirm order</button>
                    <button class="button-outline" type="submit" name="action" value="cancel_order" formnovalidate>× Exit</button>
                </div>
            </form>
        </section>

        <section class="panel" id="confirmed-orders">
            <div class="section-heading">
                <h2>Confirmed orders</h2>
                <a href="salesman_dashboard.php?page=sale">Start new sale</a>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Purchased jerseys</th>
                            <th>Qty</th>
                            <th>Total price</th>
                            <th>Date</th>
                            <th>Order status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!$salesHistory): ?>
                        <tr><td colspan="8" class="empty-row">No confirmed orders yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($salesHistory as $order): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($order['customer_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($order['customer_phone'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($order['customer_email'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($order['purchased_jerseys'] ?? 'N/A'); ?></td>
                                <td><?php echo (int) $order['total_quantity']; ?></td>
                                <td>৳<?php echo number_format((float) $order['total_amount'], 0); ?></td>
                                <td><?php echo htmlspecialchars($order['purchase_date'] ?? substr($order['created_at'], 0, 10)); ?></td>
                                <td><span class="status"><?php echo htmlspecialchars($order['status']); ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    <?php endif; ?>
</main>

<script>
const jerseyData = <?php echo $jerseyJson ?: '[]'; ?>;
let sortDirection = { name: 1, quantity: 1 };
const baseSubtotal = <?php echo json_encode((float) $cartSummary['subtotal']); ?>;

function initializeJerseySelector() {
    const nameSelect = document.getElementById('jersey_name');
    const sizeSelect = document.getElementById('jersey_size');

    if (!nameSelect || !sizeSelect || jerseyData.length === 0) return;

    function buildSizes() {
        const selectedName = nameSelect.value;
        const variants = jerseyData.filter(item => item.name === selectedName);
        sizeSelect.innerHTML = '';

        variants.forEach(item => {
            const option = document.createElement('option');
            option.value = item.size;
            option.textContent = item.size;
            sizeSelect.appendChild(option);
        });

        syncVariant();
    }

    function syncVariant() {
        const selected = jerseyData.find(item =>
            item.name === nameSelect.value && item.size === sizeSelect.value
        );

        const hiddenId = document.getElementById('jersey_id');
        const quantityInput = document.getElementById('sale_quantity');
        const info = document.getElementById('productInfo');

        if (!selected) {
            hiddenId.value = '';
            info.textContent = '';
            return;
        }

        hiddenId.value = selected.id;
        quantityInput.max = selected.quantity;
        info.textContent = 'Price: ৳' + Number(selected.price).toLocaleString() +
            ' | Available stock: ' + selected.quantity +
            ' | Category: ' + selected.category;
    }

    nameSelect.addEventListener('change', buildSizes);
    sizeSelect.addEventListener('change', syncVariant);
    buildSizes();
}

function validateAddItem() {
    const jerseyId = document.getElementById('jersey_id').value;
    const quantity = Number(document.getElementById('sale_quantity').value);

    if (!jerseyId) {
        alert('Please select a jersey and size.');
        return false;
    }

    if (!Number.isInteger(quantity) || quantity <= 0) {
        alert('Quantity must be at least 1.');
        return false;
    }

    return true;
}

function validateCheckout() {
    const totalQuantity = Number(document.body.dataset.cartQuantity || 0);

    if (totalQuantity <= 0) {
        alert('Add at least one jersey before checkout.');
        return false;
    }

    return true;
}

function filterCart() {
    const input = document.getElementById('cartSearch');
    if (!input) return;

    const searchText = input.value.trim().toLowerCase();
    const rows = Array.from(document.querySelectorAll('#cartBody .cart-row'));
    let visible = 0;

    rows.forEach(row => {
        const show = row.dataset.name.includes(searchText);
        row.style.display = show ? '' : 'none';
        if (show) visible++;
    });

    const count = document.getElementById('visibleItemCount');
    if (count) count.textContent = visible;
}

function sortCart(type) {
    const tbody = document.getElementById('cartBody');
    if (!tbody) return;

    const rows = Array.from(tbody.querySelectorAll('.cart-row'));
    if (rows.length === 0) return;

    const direction = sortDirection[type];

    rows.sort((a, b) => {
        if (type === 'quantity') {
            return (Number(a.dataset.quantity) - Number(b.dataset.quantity)) * direction;
        }

        return a.dataset.name.localeCompare(b.dataset.name) * direction;
    });

    rows.forEach(row => tbody.appendChild(row));
    sortDirection[type] *= -1;
}

async function applyPromo() {
    const promoInput = document.getElementById('promoInput');
    const totalPrice = document.getElementById('totalPrice');
    const promoMessage = document.getElementById('promoMessage');

    if (!promoInput || !totalPrice || !promoMessage) return;

    const code = promoInput.value.trim().toUpperCase();
    promoInput.value = code;

    if (code === '') {
        totalPrice.value = Number(baseSubtotal).toFixed(2);
        promoMessage.textContent = 'Enter a promo code first.';
        return;
    }

    promoMessage.textContent = 'Checking promo...';

    const body = new URLSearchParams();
    body.append('action', 'check_promo');
    body.append('promo_code', code);

    try {
        const response = await fetch('../Controller/SalesmanController.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
            },
            body: body.toString()
        });

        const data = await response.json();

        if (data.valid) {
            totalPrice.value = Number(data.final_total).toFixed(2);
            promoMessage.textContent = data.message +
                ' Discount: ৳' + Number(data.discount_amount).toFixed(2);
        } else {
            totalPrice.value = Number(baseSubtotal).toFixed(2);
            promoMessage.textContent = data.message || 'Promo could not be applied.';
        }
    } catch (error) {
        totalPrice.value = Number(baseSubtotal).toFixed(2);
        promoMessage.textContent = 'Promo check failed. Please try again.';
    }
}

function validatePayment(event) {
    const clickedAction = event.submitter ? event.submitter.value : 'confirm_order';

    if (clickedAction === 'cancel_order') {
        return true;
    }

    const totalQuantity = Number(document.getElementById('payment_quantity').value || 0);
    const name = document.getElementById('customer_name').value.trim();
    const phone = document.getElementById('customer_phone').value.trim();
    const email = document.getElementById('customer_email').value.trim();
    const purchaseDate = document.getElementById('purchase_date').value;

    if (totalQuantity <= 0) {
        alert('The order has no items.');
        return false;
    }

    if (name.length < 2) {
        alert('Customer name must be at least 2 characters.');
        return false;
    }

    const phoneDigits = phone.replace(/\D/g, '');
    if (phoneDigits.length < 10 || phoneDigits.length > 15) {
        alert('Enter a valid phone number.');
        return false;
    }

    if (email !== '' && !document.getElementById('customer_email').checkValidity()) {
        alert('Enter a valid email address.');
        return false;
    }

    if (purchaseDate === '') {
        alert('Select the purchase date.');
        return false;
    }

    return confirm('Confirm this customer order?');
}

document.addEventListener('DOMContentLoaded', function () {
    initializeJerseySelector();

    const search = document.getElementById('cartSearch');
    if (search) search.addEventListener('input', filterCart);

    const promo = document.getElementById('promoInput');
    if (promo) {
        promo.addEventListener('input', function () {
            const totalPrice = document.getElementById('totalPrice');
            const promoMessage = document.getElementById('promoMessage');
            if (totalPrice) totalPrice.value = Number(baseSubtotal).toFixed(2);
            if (promoMessage) promoMessage.textContent = 'Click Apply promo to check this code.';
        });
    }
});
</script>
</body>
</html>
