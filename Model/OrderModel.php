<?php

class OrderModel
{
    private PDO $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    public function getCartDetails(array $cart): array
    {
        $items = [];
        $totalQuantity = 0;
        $subtotal = 0.00;

        if (!$cart) {
            return [
                'items' => [],
                'total_quantity' => 0,
                'subtotal' => 0.00
            ];
        }

        $statement = $this->connection->prepare(
            'SELECT id, name, size, price, quantity
             FROM jerseys
             WHERE id = :id'
        );

        foreach ($cart as $jerseyId => $quantity) {
            $jerseyId = (int) $jerseyId;
            $quantity = (int) $quantity;

            if ($jerseyId <= 0 || $quantity <= 0) {
                continue;
            }

            $statement->execute(['id' => $jerseyId]);
            $jersey = $statement->fetch();

            if (!$jersey) {
                continue;
            }

            $unitPrice = (float) $jersey['price'];
            $itemSubtotal = $unitPrice * $quantity;

            $items[] = [
                'id' => (int) $jersey['id'],
                'name' => $jersey['name'],
                'size' => $jersey['size'],
                'unit_price' => $unitPrice,
                'quantity' => $quantity,
                'available_stock' => (int) $jersey['quantity'],
                'subtotal' => $itemSubtotal
            ];

            $totalQuantity += $quantity;
            $subtotal += $itemSubtotal;
        }

        return [
            'items' => $items,
            'total_quantity' => $totalQuantity,
            'subtotal' => round($subtotal, 2)
        ];
    }

    public function validatePromo(string $code, float $subtotal): array
    {
        $code = strtoupper(trim($code));

        if ($code === '') {
            return [
                'valid' => true,
                'message' => 'No promo code used.',
                'code' => '',
                'discount_percent' => 0.00,
                'discount_amount' => 0.00,
                'final_total' => round($subtotal, 2)
            ];
        }

        $statement = $this->connection->prepare(
            'SELECT code, discount_percent, start_date, expiry_date, is_active
             FROM promo_codes
             WHERE code = :code
             LIMIT 1'
        );
        $statement->execute(['code' => $code]);
        $promo = $statement->fetch();

        if (!$promo) {
            return $this->invalidPromo('Promo code not found.', $subtotal);
        }

        if ((int) $promo['is_active'] !== 1) {
            return $this->invalidPromo('This promo code is inactive.', $subtotal);
        }

        // PHP server-side date validation required by the rubric.
        $today = new DateTimeImmutable('today');
        $startDate = new DateTimeImmutable($promo['start_date']);
        $expiryDate = new DateTimeImmutable($promo['expiry_date']);

        if ($today < $startDate) {
            return $this->invalidPromo('This promo code is not active yet.', $subtotal);
        }

        if ($today > $expiryDate) {
            return $this->invalidPromo('This promo code has expired.', $subtotal);
        }

        $discountPercent = (float) $promo['discount_percent'];
        $discountAmount = round(($subtotal * $discountPercent) / 100, 2);
        $finalTotal = max(0, round($subtotal - $discountAmount, 2));

        return [
            'valid' => true,
            'message' => $discountPercent . '% discount applied.',
            'code' => $promo['code'],
            'discount_percent' => $discountPercent,
            'discount_amount' => $discountAmount,
            'final_total' => $finalTotal
        ];
    }

    private function invalidPromo(string $message, float $subtotal): array
    {
        return [
            'valid' => false,
            'message' => $message,
            'code' => '',
            'discount_percent' => 0.00,
            'discount_amount' => 0.00,
            'final_total' => round($subtotal, 2)
        ];
    }

    public function createPosOrder(
        int $salesmanId,
        array $customer,
        array $cart,
        string $promoCode
    ): array {
        if (!$cart) {
            throw new InvalidArgumentException('The order has no items.');
        }

        $this->connection->beginTransaction();

        try {
            $items = [];
            $subtotal = 0.00;
            $totalQuantity = 0;

            $stockStatement = $this->connection->prepare(
                'SELECT id, name, size, price, quantity
                 FROM jerseys
                 WHERE id = :id
                 FOR UPDATE'
            );

            foreach ($cart as $jerseyId => $quantity) {
                $jerseyId = (int) $jerseyId;
                $quantity = (int) $quantity;

                if ($jerseyId <= 0 || $quantity <= 0) {
                    throw new InvalidArgumentException('Invalid item quantity found in cart.');
                }

                $stockStatement->execute(['id' => $jerseyId]);
                $jersey = $stockStatement->fetch();

                if (!$jersey) {
                    throw new RuntimeException('A selected jersey no longer exists.');
                }

                if ((int) $jersey['quantity'] < $quantity) {
                    throw new RuntimeException(
                        'Not enough stock for ' . $jersey['name'] . ' - Size ' . $jersey['size'] . '.'
                    );
                }

                $unitPrice = (float) $jersey['price'];
                $itemSubtotal = $unitPrice * $quantity;

                $items[] = [
                    'jersey_id' => (int) $jersey['id'],
                    'name' => $jersey['name'],
                    'size' => $jersey['size'],
                    'unit_price' => $unitPrice,
                    'quantity' => $quantity,
                    'subtotal' => $itemSubtotal
                ];

                $subtotal += $itemSubtotal;
                $totalQuantity += $quantity;
            }

            // Promo is checked again here on final commit. Never trust AJAX/client value.
            $promo = $this->validatePromo($promoCode, $subtotal);

            if (trim($promoCode) !== '' && !$promo['valid']) {
                throw new InvalidArgumentException($promo['message']);
            }

            $insertOrder = $this->connection->prepare(
                'INSERT INTO orders
                (customer_id, salesman_id, customer_name, customer_phone, customer_email,
                 subtotal_amount, total_quantity, discount_amount, promo_code,
                 total_amount, status, purchase_date)
                 VALUES
                (NULL, :salesman_id, :customer_name, :customer_phone, :customer_email,
                 :subtotal_amount, :total_quantity, :discount_amount, :promo_code,
                 :total_amount, :status, :purchase_date)'
            );

            $insertOrder->execute([
                'salesman_id' => $salesmanId,
                'customer_name' => $customer['name'],
                'customer_phone' => $customer['phone'],
                'customer_email' => $customer['email'] !== '' ? $customer['email'] : null,
                'subtotal_amount' => round($subtotal, 2),
                'total_quantity' => $totalQuantity,
                'discount_amount' => $promo['discount_amount'],
                'promo_code' => $promo['code'] !== '' ? $promo['code'] : null,
                'total_amount' => $promo['final_total'],
                'status' => 'Processing',
                'purchase_date' => $customer['purchase_date']
            ]);

            $orderId = (int) $this->connection->lastInsertId();

            $insertItem = $this->connection->prepare(
                'INSERT INTO order_items
                (order_id, jersey_id, jersey_name, size, unit_price, quantity, subtotal)
                 VALUES
                (:order_id, :jersey_id, :jersey_name, :size, :unit_price, :quantity, :subtotal)'
            );

            $updateStock = $this->connection->prepare(
                'UPDATE jerseys
                 SET quantity = quantity - :quantity
                 WHERE id = :jersey_id'
            );

            foreach ($items as $item) {
                $insertItem->execute([
                    'order_id' => $orderId,
                    'jersey_id' => $item['jersey_id'],
                    'jersey_name' => $item['name'],
                    'size' => $item['size'],
                    'unit_price' => $item['unit_price'],
                    'quantity' => $item['quantity'],
                    'subtotal' => $item['subtotal']
                ]);

                $updateStock->execute([
                    'quantity' => $item['quantity'],
                    'jersey_id' => $item['jersey_id']
                ]);
            }

            $this->connection->commit();

            return [
                'order_id' => $orderId,
                'total_quantity' => $totalQuantity,
                'subtotal' => round($subtotal, 2),
                'discount_amount' => $promo['discount_amount'],
                'total_amount' => $promo['final_total']
            ];
        } catch (Throwable $exception) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }

            throw $exception;
        }
    }

    public function getSalesmanOrders(int $salesmanId): array
    {
        $statement = $this->connection->prepare(
            "SELECT o.id, o.customer_name, o.customer_phone, o.customer_email,
                    o.total_quantity, o.subtotal_amount, o.discount_amount,
                    o.total_amount, o.promo_code, o.purchase_date, o.status, o.created_at,
                    GROUP_CONCAT(
                        CONCAT(oi.jersey_name, ' [', oi.size, '] x', oi.quantity)
                        ORDER BY oi.id SEPARATOR ', '
                    ) AS purchased_jerseys
             FROM orders o
             LEFT JOIN order_items oi ON oi.order_id = o.id
             WHERE o.salesman_id = :salesman_id
             GROUP BY o.id
             ORDER BY o.id DESC"
        );
        $statement->execute(['salesman_id' => $salesmanId]);

        return $statement->fetchAll();
    }

    public function getMonthlySalesBySalesman(int $salesmanId): float
    {
        $statement = $this->connection->prepare(
            "SELECT COALESCE(SUM(total_amount), 0)
             FROM orders
             WHERE salesman_id = :salesman_id
               AND YEAR(COALESCE(purchase_date, DATE(created_at))) = YEAR(CURDATE())
               AND MONTH(COALESCE(purchase_date, DATE(created_at))) = MONTH(CURDATE())"
        );
        $statement->execute(['salesman_id' => $salesmanId]);

        return (float) $statement->fetchColumn();
    }
}
