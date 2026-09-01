<?php
// Controller/CustomerController.php

require_once __DIR__ . '/../Model/JerseyModel.php';

class CustomerController
{
    private JerseyModel $jerseyModel;

    public function __construct(PDO $connection)
    {
        $this->jerseyModel = new JerseyModel($connection);
    }

    // Display the customer dashboard with available jerseys
    public function dashboard(): void
    {
        // Fetch all available jerseys from DB (quantity > 0)
        $jerseys = $this->jerseyModel->getAvailableJerseys();

        // Pass data to view
        require_once __DIR__ . '/../View/customer_dashboard.php';
    }

    // Display product details for a selected jersey
    public function productDetails(): void
    {
        $jerseyId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $selectedQty = isset($_GET['quantity']) ? max(1, (int)$_GET['quantity']) : 1;

        // Fetch specific jersey by ID
        $jersey = $this->jerseyModel->findById($jerseyId);

        if (!$jersey) {
            header("Location: customer_dashboard.php");
            exit();
        }

        // Pass data to view
        require_once __DIR__ . '/../View/product_details.php';
    }

    // Add selected jersey to session cart
    public function addToCart(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $jerseyId = isset($_POST['jersey_id']) ? (int)$_POST['jersey_id'] : 0;
            $quantity = isset($_POST['quantity']) ? max(1, (int)$_POST['quantity']) : 1;

            $jersey = $this->jerseyModel->findById($jerseyId);

            if ($jersey) {
                if (!isset($_SESSION['cart'])) {
                    $_SESSION['cart'] = [];
                }

                $found = false;
                foreach ($_SESSION['cart'] as &$item) {
                    if ($item['id'] === $jersey['id']) {
                        $item['quantity'] += $quantity;
                        $found = true;
                        break;
                    }
                }

                if (!$found) {
                    $_SESSION['cart'][] = [
                        'id' => $jersey['id'],
                        'name' => $jersey['name'],
                        'size' => $jersey['size'],
                        'price' => $jersey['price'],
                        'quantity' => $quantity
                    ];
                }
            }

            header("Location: checkout.php");
            exit();
        }
    }
}
?>