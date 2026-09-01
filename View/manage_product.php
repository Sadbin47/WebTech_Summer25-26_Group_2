
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Manager') {
    header("Location: login.php");
    exit;
}
require_once "../Model/db.php";
require_once "../Model/ManagerModel.php";

$database = new Database();
$connection = $database->connect();
$model = new ManagerModel($connection);

$products = $model->getProducts();
include "header.php";
?>

<div class="container">
    <h2>Manage Product</h2>
    <h3>Add Product</h3>
    <form id="productForm">
        <input
            type="text"
            name="name"
            placeholder="Jersey Name"
            required
        >
        <input
            type="text"
            name="size"
            placeholder="Size"
            required
        >
        <input
            type="text"
            name="category"
            placeholder="Category"
            required
        >
        <input
            type="number"
            name="price"
            placeholder="Price"
            step="0.01"
            required
        >
        <input
            type="number"
            name="quantity"
            placeholder="Quantity"
            required
        >
        <button type="submit">
            Add Product
        </button>
    </form>

    <p id="message"></p>
    <h3>Products</h3>
    <table border="1" cellpadding="10">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Size</th>
            <th>Category</th>
            <th>Price</th>
            <th>Stock</th>
            <th>Action</th>
        </tr>

        <?php foreach ($products as $product): ?>

            <tr id="product-<?php echo $product['id']; ?>">
                <td>
                    <?php echo $product['id']; ?>
                </td>

                <td>
                    <?php echo htmlspecialchars($product['name']); ?>
                </td>

                <td>
                    <?php echo htmlspecialchars($product['size']); ?>
                </td>

                <td>
                    <?php echo htmlspecialchars($product['category']); ?>
                </td>

                <td>
                    <?php echo $product['price']; ?>
                </td>

                <td>
                    <?php echo $product['quantity']; ?>
                </td>

                <td>
                    <button
                        onclick="deleteProduct(<?php echo $product['id']; ?>)"
                    >
                        Delete
                    </button>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>

<script>
// Add Product using AJAX
document.getElementById("productForm").addEventListener("submit", function(e) {

    e.preventDefault();

    const formData = new FormData(this);

    fetch("../Controller/ManagerController.php?action=add_product", {
        method: "POST",
        body: formData
    })

    .then(response => response.json())

    .then(data => 
    {
        document.getElementById("message").innerText = data.message;
        if (data.success) {
            document.getElementById("productForm").reset();
            setTimeout(() => 
            { location.reload();}, 800);
        }
    });

});

// Delete Product using AJAX
function deleteProduct(id) {

    if (!confirm("Delete this product?")) {
        return;
    }

    const formData = new FormData();

    formData.append("id", id);

    fetch("../Controller/ManagerController.php?action=delete_product", {
        method: "POST",
        body: formData
    })

    .then(response => response.json())
    .then(data => {
        document.getElementById("message").innerText = data.message;
        if (data.success) 
            {
            document
                .getElementById("product-" + id)
                .remove();
        }

    });

}
// Disable Mouse Wheel for Number Inputs
document.querySelectorAll('input[type="number"]').forEach(function(input) {

    input.addEventListener("wheel", function(e) {
        e.preventDefault();
    });

});

</script>

<style>

/* Container */
.container {
    width: 90%;
    margin: 30px auto;
    font-family: Arial, sans-serif;
}

/*Input Fields*/
input 
{
    padding: 7px;
    margin: 3px;
}

/*Buttons*/
button {
    padding: 7px 12px;
}
</style>

