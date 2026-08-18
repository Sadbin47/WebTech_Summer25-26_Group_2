
<?php
// Manager Dashboard - keeping the jerseys under control!
?>

<!DOCTYPE html>
<html>

<head>
    <title>Manager Dashboard</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            color: #222;
            font-size: 13px;
        }

        /* Sidebar  */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 180px;
            height: 100vh;
            background: #222;
            padding-top: 15px;
        }

        .sidebar h3 {
            color: white;
            text-align: center;
            font-size: 16px;
            margin: 0 0 20px 0;
        }

        .sidebar a {
            display: block;
            color: #ddd;
            text-decoration: none;
            padding: 9px 15px;
            font-size: 13px;
        }

        .sidebar a:hover {
            background: #444;
            color: white;
        }

        /* Main area  */
        .main {
            margin-left: 180px;
            padding: 15px 20px;
        }

        /* Header  */
        .header {
            background: white;
            padding: 10px 15px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
        }

        .header h2 {
            margin: 0;
            font-size: 18px;
        }

        /* Dashboard  */
        .cards {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }

        .card {
            background: white;
            border: 1px solid #ddd;
            padding: 12px;
            flex: 1;
        }

        .card h4 {
            margin: 0 0 5px 0;
            color: #666;
            font-size: 12px;
        }

        .card p {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }

        /* Sections  */
        .section {
            background: white;
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 15px;
        }

        .section h3 {
            margin-top: 0;
            font-size: 15px;
        }

        /* Inputs */
        input,
        select {
            padding: 6px;
            font-size: 12px;
            border: 1px solid #ccc;
            margin-right: 4px;
            margin-bottom: 5px;
        }

        /* Buttons  */
        button {
            padding: 6px 10px;
            font-size: 12px;
            border: none;
            background: #333;
            color: white;
            cursor: pointer;
            margin-right: 3px;
        }

        button:hover {
            background: #555;
        }

        .edit {
            background: #2878d4;
        }

        .delete {
            background: #d33;
        }

        /* Inventory table  */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            padding: 7px;
            border: 1px solid #ddd;
            text-align: left;
            font-size: 12px;
        }

        th {
            background: #eee;
        }

        /* Stock status  */
        .low {
            color: red;
            font-weight: bold;
        }

        .available {
            color: green;
            font-weight: bold;
        }

        /* Report buttons  */
        .report-button {
            margin-right: 8px;
        }

        /* Small screens  */
        @media screen and (max-width: 700px) {
            .sidebar {
                width: 140px;
            }

            .main {
                margin-left: 140px;
                padding: 10px;
            }

            .cards {
                flex-direction: column;
            }

            th,
            td {
                padding: 5px;
                font-size: 11px;
            }
        }
    </style>
</head>

<body>

<!-- Sidebar  -->
<div class="sidebar">
    <h3>Jersey Manager</h3>
    <a href="#">Dashboard</a>
    <a href="#">Inventory</a>
    <a href="#">Categories</a>
    <a href="#">Stock</a>
    <a href="#">Sales</a>
    <a href="#">Reports</a>
</div>

<!-- Main content  -->
<div class="main">

    <!-- Header  -->
    <div class="header">
        <h2>Manager Dashboard</h2>
    </div>

    <!-- Dashboard -->
    <div class="cards">
        <div class="card">
            <h4>Total Jerseys</h4>
            <p>120</p>
        </div>

        <div class="card">
            <h4>Total Stock</h4>
            <p>850</p>
        </div>

        <div class="card">
            <h4>Low Stock</h4>
            <p>12</p>
        </div>

        <div class="card">
            <h4>Total Sales</h4>
            <p>৳85,000</p>
        </div>
    </div>

    <!-- Add jersey -->
    <div class="section">
        <h3>Add Jersey</h3>

        <form>
            <input type="text" placeholder="Jersey Name">

            <select>
                <option>Football</option>
            </select>
            <input type="number" placeholder="Price">
            <input type="number" placeholder="Stock">
            <button type="submit">Add Jersey</button>
        </form>
    </div>

    <!--Inventory-->
    <div class="section">
        <h3>Jersey Inventory</h3>
        <input type="text" placeholder="Search Jersey">

        <select>
            <option>All Jerseys</option>
            <option>Football</option>
        </select>

        <table>
            <tr>
                <th>ID</th>
                <th>Jersey</th>
                <th>Category</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Status</th>
                <th>Action</th>
            </tr>

            <tr>
                <td>1</td>
                <td>Argentina Jersey</td>
                <td>Football</td>
                <td>৳2500</td>
                <td class="available">50</td>
                <td class="available">Available</td>
                <td>
                    <button class="edit">Edit</button>
                    <button class="delete">Delete</button>
                </td>
            </tr>

            <tr>
                <td>2</td>
                <td>Brazil Jersey</td>
                <td>Football</td>
                <td>৳2200</td>
                <td class="low">5</td>
                <td class="low">Low Stock</td>
                <td>
                    <button class="edit">Edit</button>
                    <button class="delete">Delete</button>
                </td>
            </tr>

            <tr>
                <td>3</td>
                <td>Real Madrid Jersey</td>
                <td>Football</td>
                <td>৳2800</td>
                <td class="available">35</td>
                <td class="available">Available</td>
                <td>
                    <button class="edit">Edit</button>
                    <button class="delete">Delete</button>
                </td>
            </tr>

            <tr>
                <td>4</td>
                <td>Barcelona Jersey</td>
                <td>Football</td>
                <td>৳2700</td>
                <td class="low">4</td>
                <td class="low">Low Stock</td>
                <td>
                    <button class="edit">Edit</button>
                    <button class="delete">Delete</button>
                </td>
            </tr>

            <tr>
                <td>5</td>
                <td>Manchester United Jersey</td>
                <td>Football</td>
                <td>৳2600</td>
                <td class="available">28</td>
                <td class="available">Available</td>
                <td>
                    <button class="edit">Edit</button>
                    <button class="delete">Delete</button>
                </td>
            </tr>
        </table>
    </div>

    <!-- Reports -->
    <div class="section">
        <h3>Reports</h3>

        <button class="report-button">Sales Report</button>
        <button class="report-button">Stock Report</button>
        <button class="report-button">Category Report</button>
    </div>

</div>

</body>
</html>

