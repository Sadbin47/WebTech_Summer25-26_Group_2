<?php

session_start();

require_once '../Model/db.php';
require_once '../Model/JerseyModel.php';
require_once '../Model/OrderModel.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$isJsonRequest = $action === 'check_promo';

if (($_SESSION['role'] ?? '') !== 'Salesman') {
    if ($isJsonRequest) {
        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
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

$database = new Database();
$connection = $database->connect();
$jerseyModel = new JerseyModel($connection);
$orderModel = new OrderModel($connection);

if (!isset($_SESSION['sales_cart']) || !is_array($_SESSION['sales_cart'])) {
    $_SESSION['sales_cart'] = [];
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../View/salesman_dashboard.php?page=sale');
    exit;
}

try {
    if ($action === 'add_to_cart') {
        $jerseyId = (int) ($_POST['jersey_id'] ?? 0);
        $quantity = (int) ($_POST['quantity'] ?? 0);
        $jersey = $jerseyModel->findById($jerseyId);

        if (!$jersey || $quantity <= 0) {
            returnToSalesman('Please select a valid jersey and quantity.', true, 'sale', 'sale');
        }

        $currentQuantity = (int) ($_SESSION['sales_cart'][$jerseyId] ?? 0);
        $newQuantity = $currentQuantity + $quantity;

        if ($newQuantity > (int) $jersey['quantity']) {
            returnToSalesman(
                'Only ' . (int) $jersey['quantity'] . ' piece(s) are available for this jersey.',
                true,
                'sale',
                'sale'
            );
        }

        $_SESSION['sales_cart'][$jerseyId] = $newQuantity;
        returnToSalesman('Jersey added to the current order.', false, 'sale', 'sale');
    }

    if ($action === 'update_quantity') {
        $jerseyId = (int) ($_POST['jersey_id'] ?? 0);
        $quantity = (int) ($_POST['quantity'] ?? 0);
        $jersey = $jerseyModel->findById($jerseyId);

        if (!$jersey || !isset($_SESSION['sales_cart'][$jerseyId])) {
            returnToSalesman('Order item not found.', true, 'sale', 'sale');
        }

        if ($quantity <= 0) {
            unset($_SESSION['sales_cart'][$jerseyId]);
            returnToSalesman('Item removed from the order.', false, 'sale', 'sale');
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
        returnToSalesman('Quantity updated.', false, 'sale', 'sale');
    }

    if ($action === 'remove_item') {
        $jerseyId = (int) ($_POST['jersey_id'] ?? 0);
        unset($_SESSION['sales_cart'][$jerseyId]);
        returnToSalesman('Item removed from the order.', false, 'sale', 'sale');
    }

    if ($action === 'go_payment') {
        $summary = $orderModel->getCartDetails($_SESSION['sales_cart']);

        // Server-side protection in addition to JavaScript zero-item validation.
        if ((int) $summary['total_quantity'] <= 0) {
            returnToSalesman('Add at least one jersey before checkout.', true, 'sale', 'sale');
        }

        header('Location: ../View/salesman_dashboard.php?page=payment');
        exit;
    }

    if ($action === 'check_promo') {
        $code = trim($_POST['promo_code'] ?? '');
        $summary = $orderModel->getCartDetails($_SESSION['sales_cart']);

        if ((int) $summary['total_quantity'] <= 0) {
            jsonResponse([
                'valid' => false,
                'message' => 'The order has no items.',
                'final_total' => 0
            ], 422);
        }

        if ($code === '') {
            jsonResponse([
                'valid' => false,
                'message' => 'Enter a promo code.',
                'final_total' => $summary['subtotal']
            ], 422);
        }

        $promo = $orderModel->validatePromo($code, (float) $summary['subtotal']);
        jsonResponse($promo, $promo['valid'] ? 200 : 422);
    }

    if ($action === 'confirm_order') {
        $name = trim($_POST['customer_name'] ?? '');
        $phone = trim($_POST['customer_phone'] ?? '');
        $email = trim($_POST['customer_email'] ?? '');
        $purchaseDate = trim($_POST['purchase_date'] ?? '');
        $promoCode = trim($_POST['promo_code'] ?? '');

        $summary = $orderModel->getCartDetails($_SESSION['sales_cart']);
        if ((int) $summary['total_quantity'] <= 0) {
            returnToSalesman('The order has no items.', true, 'sale', 'sale');
        }

        if (strlen($name) < 2) {
            returnToSalesman('Customer name must be at least 2 characters.', true, 'payment');
        }

        $phoneDigits = preg_replace('/\D+/', '', $phone);
        if (strlen($phoneDigits) < 10 || strlen($phoneDigits) > 15) {
            returnToSalesman('Enter a valid phone number.', true, 'payment');
        }

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            returnToSalesman('Enter a valid email address.', true, 'payment');
        }

        $dateObject = DateTime::createFromFormat('Y-m-d', $purchaseDate);
        $validDate = $dateObject && $dateObject->format('Y-m-d') === $purchaseDate;

        if (!$validDate) {
            returnToSalesman('Enter a valid purchase date.', true, 'payment');
        }

        if ($purchaseDate > date('Y-m-d')) {
            returnToSalesman('Purchase date cannot be in the future.', true, 'payment');
        }

        $result = $orderModel->createPosOrder(
            (int) $_SESSION['user_id'],
            [
                'name' => $name,
                'phone' => $phone,
                'email' => $email,
                'purchase_date' => $purchaseDate
            ],
            $_SESSION['sales_cart'],
            $promoCode
        );

        // Cookie usage for quick POS service on the next customer form.
        setcookie('last_customer_name', $name, time() + (86400 * 7), '/');
        setcookie('last_customer_phone', $phone, time() + (86400 * 7), '/');

        $_SESSION['sales_cart'] = [];
        $_SESSION['salesman_message'] =
            'Order #' . $result['order_id'] . ' confirmed successfully. Final total: BDT ' .
            number_format((float) $result['total_amount'], 2) . '.';

        header('Location: ../View/salesman_dashboard.php?page=payment&confirmed=1#confirmed-orders');
        exit;
    }

    if ($action === 'cancel_order') {
        $_SESSION['sales_cart'] = [];
        returnToSalesman('Current transaction cancelled.', false, 'sale', 'sale');
    }

    if ($action === 'request_restock') {
        $jerseyId = (int) ($_POST['jersey_id'] ?? 0);
        $requestedQuantity = (int) ($_POST['requested_quantity'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');
        $jersey = $jerseyModel->findById($jerseyId);

        if (!$jersey || $requestedQuantity <= 0) {
            returnToSalesman('Select a valid jersey and restock quantity.', true, 'sale', 'restock');
        }

        if (strlen($reason) > 255) {
            returnToSalesman('Restock reason must be within 255 characters.', true, 'sale', 'restock');
        }

        $jerseyModel->createRestockRequest(
            (int) $_SESSION['user_id'],
            $jerseyId,
            $requestedQuantity,
            $reason
        );

        returnToSalesman('Restock request submitted.', false, 'sale', 'restock');
    }
} catch (InvalidArgumentException | RuntimeException $exception) {
    $page = in_array($action, ['confirm_order', 'check_promo'], true) ? 'payment' : 'sale';

    if ($action === 'check_promo') {
        jsonResponse([
            'valid' => false,
            'message' => $exception->getMessage()
        ], 422);
    }

    returnToSalesman($exception->getMessage(), true, $page);
} catch (PDOException $exception) {
    if ($action === 'check_promo') {
        jsonResponse([
            'valid' => false,
            'message' => 'Database request failed.'
        ], 500);
    }

    returnToSalesman('Database request failed. Please try again.', true, 'sale');
}

returnToSalesman('Unknown Salesman action.', true, 'sale');
