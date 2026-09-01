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

            $statement->execute([
                'id' => $jerseyId
            ]);

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

    public function findCustomerByPhone(string $phone): ?array
    {
        $statement = $this->connection->prepare(
            'SELECT customer_name, customer_phone, customer_email
             FROM orders
             WHERE customer_phone = :phone
             AND customer_name IS NOT NULL
             ORDER BY id DESC
             LIMIT 1'
        );

        $statement->execute([
            'phone' => $phone
        ]);

        $customer = $statement->fetch();

        return $customer ?: null;
    }

    public function createPosOrder(
        int $salesmanId,
        array $customer,
        array $cart
    ): array {
        if (!$cart) {
            throw new InvalidArgumentException('The order has no items.');
        }

        $this->connection->beginTransaction();

        try {
            $items = [];
            $subtotal = 0.00;
            $totalQuantity = 0;

            // Lock stock rows while the order is being committed.
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
                    throw new InvalidArgumentException(
                        'Invalid item quantity found in cart.'
                    );
                }

                $stockStatement->execute([
                    'id' => $jerseyId
                ]);

                $jersey = $stockStatement->fetch();

                if (!$jersey) {
                    throw new RuntimeException(
                        'A selected jersey no longer exists.'
                    );
                }

                // PHP stock validation before final POS commit.
                if ((int) $jersey['quantity'] < $quantity) {
                    throw new RuntimeException(
                        'Not enough stock for ' .
                        $jersey['name'] .
                        ' - Size ' .
                        $jersey['size'] .
                        '.'
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

            $insertOrder = $this->connection->prepare(
                'INSERT INTO orders
                (
                    customer_id,
                    salesman_id,
                    customer_name,
                    customer_phone,
                    customer_email,
                    subtotal_amount,
                    total_quantity,
                    total_amount,
                    status,
                    purchase_date
                )
                VALUES
                (
                    NULL,
                    :salesman_id,
                    :customer_name,
                    :customer_phone,
                    :customer_email,
                    :subtotal_amount,
                    :total_quantity,
                    :total_amount,
                    :status,
                    :purchase_date
                )'
            );

            $insertOrder->execute([
                'salesman_id' => $salesmanId,
                'customer_name' => $customer['name'],
                'customer_phone' => $customer['phone'],
                'customer_email' => $customer['email'] !== ''
                    ? $customer['email']
                    : null,
                'subtotal_amount' => round($subtotal, 2),
                'total_quantity' => $totalQuantity,
                'total_amount' => round($subtotal, 2),
                'status' => 'Processing',
                'purchase_date' => $customer['purchase_date']
            ]);

            $orderId = (int) $this->connection->lastInsertId();

            $insertItem = $this->connection->prepare(
                'INSERT INTO order_items
                (
                    order_id,
                    jersey_id,
                    jersey_name,
                    size,
                    unit_price,
                    quantity,
                    subtotal
                )
                VALUES
                (
                    :order_id,
                    :jersey_id,
                    :jersey_name,
                    :size,
                    :unit_price,
                    :quantity,
                    :subtotal
                )'
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

                // Stock-out after successful sale validation.
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
                'total_amount' => round($subtotal, 2)
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
            "SELECT
                o.id,
                o.customer_name,
                o.customer_phone,
                o.customer_email,
                o.total_quantity,
                o.subtotal_amount,
                o.total_amount,
                o.purchase_date,
                o.status,
                o.created_at,
                GROUP_CONCAT(
                    CONCAT(
                        oi.jersey_name,
                        ' [',
                        oi.size,
                        '] x',
                        oi.quantity
                    )
                    ORDER BY oi.id
                    SEPARATOR ', '
                ) AS purchased_jerseys
             FROM orders o
             LEFT JOIN order_items oi ON oi.order_id = o.id
             WHERE o.salesman_id = :salesman_id
             GROUP BY
                o.id,
                o.customer_name,
                o.customer_phone,
                o.customer_email,
                o.total_quantity,
                o.subtotal_amount,
                o.total_amount,
                o.purchase_date,
                o.status,
                o.created_at
             ORDER BY o.id DESC"
        );

        $statement->execute([
            'salesman_id' => $salesmanId
        ]);

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

        $statement->execute([
            'salesman_id' => $salesmanId
        ]);

        return (float) $statement->fetchColumn();
    }
}
