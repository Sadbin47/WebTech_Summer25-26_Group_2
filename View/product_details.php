<?php
session_start();

$products = [
    1 => [
        'name' => 'Argentina Home Jersey 2026',
        'price' => 1200,
        'image' => 'https://images.unsplash.com/photo-1522778119026-d647f0596c20?auto=format&fit=crop&w=800&q=80',
        'description' => 'Premium quality official home jersey. Breathable fabric designed for maximum comfort.'
    ],
    2 => [
        'name' => 'Real Madrid Home Kit',
        'price' => 1350,
        'image' => 'https://images.unsplash.com/photo-1508098682722-e99c43a406b2?auto=format&fit=crop&w=800&q=80',
        'description' => 'Classic white kit with modern sweat-wicking technology.'
    ],
    3 => [
        'name' => 'Barcelona Away Jersey',
        'price' => 1250,
        'image' => 'https://images.unsplash.com/photo-1577219491135-ce391730fb2c?auto=format&fit=crop&w=800&q=80',
        'description' => 'Lightweight away kit crafted for optimal flexibility on and off the field.'
    ],
    4 => [
        'name' => 'Arsenal Home Kit',
        'price' => 1150,
        'image' => 'https://images.unsplash.com/photo-1518091043644-c1d4457512c6?auto=format&fit=crop&w=800&q=80',
        'description' => 'Iconic red and white design engineered with durable athletic materials.'
    ]
];

$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product = $products[$productId] ?? null;

if (!$product) {
    header("Location: customer_dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Details</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f6fa;
            margin: 0;
            padding: 40px;
        }

        .details-card {
            max-width: 900px;
            margin: 0 auto;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        .jersey-view img {
            width: 100%;
            max-height: 400px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #ddd;
        }

        .jersey-info {
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .price {
            font-size: 22px;
            color: #198754;
            font-weight: bold;
            margin: 15px 0;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        select, input[type="number"] {
            padding: 8px 12px;
            border-radius: 5px;
            border: 1px solid #ccc;
            width: 100%;
        }

        .btn {
            background: #198754;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
        }

        .back-link {
            display: inline-block;
            margin-top: 15px;
            color: #555;
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="details-card">
    <div class="jersey-view">
        <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
    </div>
    <div class="jersey-info">
        <h1><?php echo htmlspecialchars($product['name']); ?></h1>
        <p class="price">৳ <?php echo number_format($product['price'], 2); ?></p>
        <p><?php echo htmlspecialchars($product['description']); ?></p>
        
        <form method="POST" action="customer_dashboard.php">
            <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
            
            <div class="form-group" style="margin-top: 15px;">
                <label for="size">Select Size:</label>
                <select name="size" id="size" required>
                    <option value="M">Medium (M)</option>
                    <option value="L">Large (L)</option>
                    <option value="XL">Extra Large (XL)</option>
                    <option value="XXL">Double Extra Large (XXL)</option>
                </select>
            </div>

            <div class="form-group">
                <label for="quantity">Quantity:</label>
                <input type="number" id="quantity" name="quantity" value="1" min="1" max="10">
            </div>

            <button type="submit" class="btn">Add to Cart</button>
        </form>
        
        <a href="customer_dashboard.php" class="back-link">← Back to Dashboard</a>
    </div>
</div>

</body>
</html>