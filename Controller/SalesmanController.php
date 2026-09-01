<?php

session_start();

require_once '../Model/db.php';
require_once '../Model/JerseyModel.php';
require_once '../Model/OrderModel.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$isJsonRequest = $action === 'check_phone';

// Only Salesman can use this Controller.
if (($_SESSION['role'] ?? '') !== 'Salesman') {
    if ($isJsonRequest) {
        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'found' => false,
            'valid' => false,
            'message' => 'Salesman login required.'
        ]);
        exit;
    }

    header('Location: ../View/login.php');
    exit;
}

function returnToSalesman(
    string $message,
    bool $error = false,
    string $page = 'sale',
    string $anchor = ''
): void {
    $_SESSION[$error ? 'salesman_error' : 'salesman_message'] = $message;

    $location = '../View/salesman_dashboard.php?page=' . urlencode($page);

    if ($anchor !== '') {
        $location .= '#' . $anchor;
    }

    header('Location: ' . $location);
    exit;
}

function jsonResponse(array $data, int $statusCode = 200): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../View/salesman_dashboard.php?page=sale');
    exit;
}

$database = new Database();
$connection = $database->connect();
$jerseyModel = new JerseyModel($connection);
$orderModel = new OrderModel($connection);

if (!isset($_SESSION['sales_cart']) || !is_array($_SESSION['sales_cart'])) {
    $_SESSION['sales_cart'] = [];
}

try {
    /*
        AJAX + JSON PHONE CHECK
        Checks whether the phone number already exists in previous orders.
    */
    if ($action === 'check_phone') {
        $phone = trim($_POST['phone'] ?? '');
        $phoneDigits = preg_replace('/\D+/', '', $phone);

        // PHP validation for AJAX request.
        if (strlen($phoneDigits) < 10 || strlen($phoneDigits) > 15) {
            jsonResponse([
                'found' => false,
                'valid' => false,
                'message' => 'Enter a valid phone number.'
            ], 422);
        }

        $customer = $orderModel->findCustomerByPhone($phoneDigits);

        if ($customer) {
            jsonResponse([
                'found' => true,
                'valid' => true,
                'customer_name' => $customer['customer_name'],
                'customer_email' => $customer['customer_email'] ?? '',
                'message' => 'Existing customer found. Customer details loaded.'
            ]);
        }

        jsonResponse([
            'found' => false,
            'valid' => true,
            'customer_name' => '',
            'customer_email' => '',
            'message' => 'New customer. Enter customer details.'
        ]);
    }

    // Add a jersey variant to the current session cart.
    if ($action === 'add_to_cart') {
        $jerseyId = (int) ($_POST['jersey_id'] ?? 0);
        $quantity = (int) ($_POST['quantity'] ?? 0);
        $jersey = $jerseyModel->findById($jerseyId);

        if (!$jersey || $quantity <= 0) {
            returnToSalesman(
                'Please select a valid jersey, size and quantity.',
                true,
                'sale',
                'sale'
            );
        }

        $currentQuantity = (int) ($_SESSION['sales_cart'][$jerseyId] ?? 0);
        $newQuantity = $currentQuantity + $quantity;

        if ($newQuantity > (int) $jersey['quantity']) {
            returnToSalesman(
                'Only ' . (int) $jersey['quantity'] .
                ' piece(s) are available for ' .
                $jersey['name'] . ' - Size ' . $jersey['size'] . '.',
                true,
                'sale',
                'sale'
            );
        }

        $_SESSION['sales_cart'][$jerseyId] = $newQuantity;

        returnToSalesman(
            'Jersey added to the current order.',
            false,
            'sale',
            'sale'
        );
    }

    // Update quantity of an existing cart item.
    if ($action === 'update_quantity') {
        $jerseyId = (int) ($_POST['jersey_id'] ?? 0);
        $quantity = (int) ($_POST['quantity'] ?? 0);
        $jersey = $jerseyModel->findById($jerseyId);

        if (!$jersey || !isset($_SESSION['sales_cart'][$jerseyId])) {
            returnToSalesman(
                'Order item not found.',
                true,
                'sale',
                'sale'
            );
        }

        if ($quantity <= 0) {
            unset($_SESSION['sales_cart'][$jerseyId]);
            returnToSalesman(
                'Item removed from the order.',
                false,
                'sale',
                'sale'
            );
        }

        if ($quantity > (int) $jersey['quantity']) {
            returnToSalesman(
                'Requested quantity is greater than current stock.',
                true,
                'sale',
                'sale'
            );
        }

        $_SESSION['sales_cart'][$jerseyId] = $quantity;

        returnToSalesman(
            'Quantity updated.',
            false,
            'sale',
            'sale'
        );
    }

    // Remove one item from the current order.
    if ($action === 'remove_item') {
        $jerseyId = (int) ($_POST['jersey_id'] ?? 0);
        unset($_SESSION['sales_cart'][$jerseyId]);

        returnToSalesman(
            'Item removed from the order.',
            false,
            'sale',
            'sale'
        );
    }

    // Page 1 -> Page 2.
    if ($action === 'go_payment') {
        $summary = $orderModel->getCartDetails($_SESSION['sales_cart']);

        // Server-side protection for zero-item checkout.
        if ((int) $summary['total_quantity'] <= 0) {
            returnToSalesman(
                'Add at least one jersey before checkout.',
                true,
                'sale',
                'sale'
            );
        }

        header('Location: ../View/salesman_dashboard.php?page=payment');
        exit;
    }

    // Confirm and commit the sale.
    if ($action === 'confirm_order') {
        $name = trim($_POST['customer_name'] ?? '');
        $phone = trim($_POST['customer_phone'] ?? '');
        $email = trim($_POST['customer_email'] ?? '');
        $purchaseDate = trim($_POST['purchase_date'] ?? '');

        $summary = $orderModel->getCartDetails($_SESSION['sales_cart']);

        if ((int) $summary['total_quantity'] <= 0) {
            returnToSalesman(
                'The order has no items.',
                true,
                'sale',
                'sale'
            );
        }

        // PHP customer-name validation.
        if (strlen($name) < 2) {
            returnToSalesman(
                'Customer name must be at least 2 characters.',
                true,
                'payment'
            );
        }

        // PHP phone validation and normalization.
        $phoneDigits = preg_replace('/\D+/', '', $phone);

        if (strlen($phoneDigits) < 10 || strlen($phoneDigits) > 15) {
            returnToSalesman(
                'Enter a valid phone number.',
                true,
                'payment'
            );
        }

        $phone = $phoneDigits;

        // PHP email validation.
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            returnToSalesman(
                'Enter a valid email address.',
                true,
                'payment'
            );
        }

        // PHP date validation.
        $dateObject = DateTime::createFromFormat('Y-m-d', $purchaseDate);
        $validDate = $dateObject && $dateObject->format('Y-m-d') === $purchaseDate;

        if (!$validDate) {
            returnToSalesman(
                'Enter a valid purchase date.',
                true,
                'payment'
            );
        }

        if ($purchaseDate > date('Y-m-d')) {
            returnToSalesman(
                'Purchase date cannot be in the future.',
                true,
                'payment'
            );
        }

        // OrderModel verifies current database stock again before commit.
        $result = $orderModel->createPosOrder(
            (int) $_SESSION['user_id'],
            [
                'name' => $name,
                'phone' => $phone,
                'email' => $email,
                'purchase_date' => $purchaseDate
            ],
            $_SESSION['sales_cart']
        );

        // Cookie usage: remember previous customer details for 7 days.
        setcookie(
            'last_customer_name',
            $name,
            time() + (86400 * 7),
            '/'
        );

        setcookie(
            'last_customer_phone',
            $phone,
            time() + (86400 * 7),
            '/'
        );

        // Clear the session cart after successful sale.
        $_SESSION['sales_cart'] = [];

        $_SESSION['salesman_message'] =
            'Order #' . $result['order_id'] .
            ' confirmed successfully. Total: BDT ' .
            number_format((float) $result['total_amount'], 2) . '.';

        header(
            'Location: ../View/salesman_dashboard.php?page=payment&confirmed=1#confirmed-orders'
        );
        exit;
    }

    // Exit/cancel the current transaction.
    if ($action === 'cancel_order') {
        $_SESSION['sales_cart'] = [];

        returnToSalesman(
            'Current transaction cancelled.',
            false,
            'sale',
            'sale'
        );
    }
} catch (InvalidArgumentException | RuntimeException $exception) {
    if ($action === 'check_phone') {
        jsonResponse([
            'found' => false,
            'valid' => false,
            'message' => $exception->getMessage()
        ], 422);
    }

    $page = $action === 'confirm_order' ? 'payment' : 'sale';
    returnToSalesman($exception->getMessage(), true, $page);
} catch (PDOException $exception) {
    if ($action === 'check_phone') {
        jsonResponse([
            'found' => false,
            'valid' => false,
            'message' => 'Database request failed.'
        ], 500);
    }

    returnToSalesman(
        'Database request failed. Please try again.',
        true,
        'sale'
    );
}

returnToSalesman(
    'Unknown Salesman action.',
    true,
    'sale'
);
