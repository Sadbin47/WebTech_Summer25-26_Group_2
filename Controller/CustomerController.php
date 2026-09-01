<?php

require_once __DIR__ . '/../Model/JerseyModel.php';

class CustomerController
{
    private JerseyModel $jerseyModel;

    public function __construct(PDO $connection)
    {
        $this->jerseyModel = new JerseyModel($connection);
    }

    public function dashboard(): array
    {
        return $this->jerseyModel->getAvailableJerseys();
    }

    public function productDetails(int $id): array|false
    {
        return $this->jerseyModel->findById($id);
    }

    public function checkout(int $id, int $quantity): array|false
    {
        $jersey = $this->jerseyModel->findById($id);

        if (!$jersey) {
            return false;
        }

        if ($quantity < 1) {
            $quantity = 1;
        }

        if ($quantity > $jersey['quantity']) {
            $quantity = $jersey['quantity'];
        }

        $jersey['order_quantity'] = $quantity;
        $jersey['subtotal'] = $jersey['price'] * $quantity;

        return $jersey;
    }

    public function placeOrder(
        int $customerId,
        string $customerName,
        int $jerseyId,
        int $quantity
    ): bool {
        return $this->jerseyModel->createOrder(
            $customerId,
            $customerName,
            $jerseyId,
            $quantity
        );
    }

    public function orderHistory(int $customerId): array
    {
        return $this->jerseyModel->getCustomerOrders($customerId);
    }
}
?>