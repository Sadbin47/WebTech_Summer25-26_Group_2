<?php
include "../Controller/Salesman.php";
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Salesman Sell Dashboard</title>
        <style>
            *
            {
                box-sizing: border-box;
            }

            body
            {
                margin: 0;
                padding: 35px 20px;
                background-color: #e9f0ec;
                font-family: Arial, Helvetica, sans-serif;
                color: #1f2f29;
            }

            .main
            {
                width: 92%;
                max-width: 1180px;
                min-height: 650px;
                margin: 0 auto;
                background-color: #ffffff;
                border-left: 9px solid #1f8a63;
                border-radius: 10px;
                box-shadow: 0 8px 28px rgba(31, 69, 55, 0.16);
                padding: 32px 36px 38px 36px;
            }

            h1
            {
                margin: 0 0 28px 0;
                padding-bottom: 15px;
                border-bottom: 2px solid #dfe9e3;
                font-size: 28px;
                font-family: "Times New Roman", serif;
                font-style: italic;
                color: #234d3d;
            }

            form
            {
                width: 100%;
            }

            .form-area
            {
                width: 100%;
                display: flex;
                gap: 28px;
                justify-content: space-between;
                align-items: stretch;
            }

            .form-area > div
            {
                flex: 1;
                background-color: #f7faf8;
                border: 1px solid #dce8e1;
                border-radius: 8px;
                padding: 20px 22px 22px 22px;
            }

            .left-table,
            .right-table
            {
                width: 100%;
                border-collapse: separate;
                border-spacing: 0 13px;
            }

            td
            {
                font-size: 14px;
                vertical-align: middle;
            }

            .label-cell
            {
                width: 155px;
                padding-right: 12px;
                font-weight: bold;
                color: #345447;
                white-space: nowrap;
            }

            input[type="text"],
            input[type="number"],
            input[type="email"],
            input[type="date"]
            {
                width: 100%;
                height: 38px;
                border: 1px solid #bdcec5;
                border-radius: 5px;
                background-color: #ffffff;
                padding: 7px 10px;
                font-family: Arial, Helvetica, sans-serif;
                font-size: 14px;
                color: #23352e;
                outline: none;
                transition: 0.2s;
            }

            input[type="text"]:focus,
            input[type="number"]:focus,
            input[type="email"]:focus,
            input[type="date"]:focus
            {
                border-color: #1f8a63;
                box-shadow: 0 0 0 3px rgba(31, 138, 99, 0.12);
            }

            input[type="submit"],
            input[type="reset"],
            input[type="button"]
            {
                min-width: 100px;
                height: 38px;
                border: none;
                border-radius: 5px;
                background-color: #28795c;
                color: #ffffff;
                font-family: Arial, Helvetica, sans-serif;
                font-size: 13px;
                font-weight: bold;
                cursor: pointer;
                margin-right: 8px;
                padding: 0 17px;
                transition: 0.2s;
            }

            input[type="submit"]:hover,
            input[type="reset"]:hover,
            input[type="button"]:hover
            {
                background-color: #1e644a;
                transform: translateY(-1px);
            }

            input[type="reset"]
            {
                background-color: #607d73;
            }

            input[type="reset"]:hover
            {
                background-color: #4e6860;
            }

            #remove
            {
                background-color: #b95656;
            }

            #remove:hover
            {
                background-color: #994545;
            }

            .left-buttons
            {
                margin-top: 18px;
                padding-top: 18px;
                border-top: 1px solid #dce8e1;
            }

            .confirm-button
            {
                margin-top: 18px;
                padding-top: 18px;
                border-top: 1px solid #dce8e1;
                text-align: right;
            }

            .confirm-button input
            {
                min-width: 165px;
                background-color: #1f8a63;
            }

            .confirm-button input:hover
            {
                background-color: #176b4c;
            }

            .table-box
            {
                width: 100%;
                min-height: 250px;
                margin-top: 30px;
                background-color: #f7faf8;
                border: 1px solid #dce8e1;
                border-radius: 8px;
                overflow-x: auto;
                box-shadow: 0 3px 12px rgba(34, 67, 54, 0.06);
            }

            .sales-table
            {
                width: 100%;
                min-width: 850px;
                margin: 0;
                border-collapse: collapse;
                background-color: #ffffff;
                color: #23352e;
                font-family: Arial, Helvetica, sans-serif;
                font-size: 12px;
            }

            .sales-table th
            {
                background-color: #2e6b53;
                color: #ffffff;
                border: 1px solid #356f59;
                padding: 11px 8px;
                text-align: center;
                font-size: 12px;
                font-weight: bold;
            }

            .sales-table td
            {
                color: #293a33;
                border: 1px solid #d8e1dc;
                padding: 9px 8px;
                font-family: Arial, Helvetica, sans-serif;
                font-size: 12px;
                font-weight: normal;
                height: 34px;
            }

            .sales-table tbody tr:nth-child(even)
            {
                background-color: #f4f8f6;
            }

            .sales-table tbody tr:hover
            {
                background-color: #e5f3ec;
            }

            @media screen and (max-width: 900px)
            {
                body
                {
                    padding: 18px 10px;
                }

                .main
                {
                    width: 100%;
                    padding: 24px 18px 28px 18px;
                    border-left-width: 6px;
                }

                h1
                {
                    font-size: 24px;
                }

                .form-area
                {
                    display: block;
                }

                .form-area > div
                {
                    width: 100%;
                    margin-bottom: 20px;
                }

                .left-table,
                .right-table
                {
                    width: 100%;
                }

                .label-cell
                {
                    width: 145px;
                }

                .confirm-button
                {
                    text-align: left;
                }
            }

            @media screen and (max-width: 560px)
            {
                .left-table td,
                .right-table td
                {
                    display: block;
                    width: 100%;
                }

                .label-cell
                {
                    padding-bottom: 5px;
                }

                .left-buttons input,
                .confirm-button input
                {
                    width: 100%;
                    margin: 0 0 9px 0;
                }
            }
        </style>
    </head>

    <body>
        <div class="main">

            <h1>Welcome to Sell Dashboard</h1>

            <form method="post" action="">

                <div class="form-area">

                    <div>
                        <table class="left-table">

                            <tr>
                                <td class="label-cell">
                                    <label for="ProductID">Product ID:</label>
                                </td>
                                <td>
                                    <input type="text" id="ProductID" name="ProductID" value="">
                                </td>
                            </tr>

                            <tr>
                                <td class="label-cell">
                                    <label for="ProductQuantity">Product Quantity:</label>
                                </td>
                                <td>
                                    <input type="number" id="ProductQuantity" name="ProductQuantity" value="">
                                </td>
                            </tr>

                            <tr>
                                <td class="label-cell">
                                    <label for="ProductPrice">Product Price:</label>
                                </td>
                                <td>
                                    <input type="text" id="ProductPrice" name="ProductPrice" value="">
                                </td>
                            </tr>

                            <tr>
                                <td class="label-cell">
                                    <label for="TotalPrice">Total Price:</label>
                                </td>
                                <td>
                                    <input type="text" id="TotalPrice" name="TotalPrice" value="">
                                </td>
                            </tr>

                            <tr>
                                <td class="label-cell">
                                    <label for="PurchaseDate">Purchase Date:</label>
                                </td>
                                <td>
                                    <input type="date" id="PurchaseDate" name="PurchaseDate" value="">
                                </td>
                            </tr>

                        </table>

                        <div class="left-buttons">
                            <input type="button" id="back" name="back" value="BACK" onclick="history.back()">
                            <input type="reset" id="reset" name="reset" value="RESET">
                            <input type="button" id="remove" name="remove" value="REMOVE">
                        </div>
                    </div>

                    <div>
                        <table class="right-table">

                            <tr>
                                <td class="label-cell">
                                    <label for="EmployeeID">Employee ID:</label>
                                </td>
                                <td>
                                    <input type="text" id="EmployeeID" name="EmployeeID" value="">
                                </td>
                            </tr>

                            <tr>
                                <td class="label-cell">
                                    <label for="EmployeeName">Employee Name:</label>
                                </td>
                                <td>
                                    <input type="text" id="EmployeeName" name="EmployeeName" value="">
                                </td>
                            </tr>

                            <tr>
                                <td class="label-cell">
                                    <label for="CustomerName">Customer Name:</label>
                                </td>
                                <td>
                                    <input type="text" id="CustomerName" name="CustomerName" value="">
                                </td>
                            </tr>

                            <tr>
                                <td class="label-cell">
                                    <label for="Gmail">Gmail:</label>
                                </td>
                                <td>
                                    <input type="email" id="Gmail" name="Gmail" value="">
                                </td>
                            </tr>

                        </table>

                        <div class="confirm-button">
                            <input type="submit" id="submit" name="submit" value="CONFIRM SALE">
                        </div>
                    </div>

                </div>

                <div class="table-box">
                    <table class="sales-table">
                        <thead>
                            <tr>
                                <th>Sale ID</th>
                                <th>Product ID</th>
                                <th>Customer Name</th>
                                <th>Gmail</th>
                                <th>Quantity</th>
                                <th>Purchase Date</th>
                                <th>Price</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr>
                                <td>&nbsp;</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>

                            <tr>
                                <td>&nbsp;</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>

                            <tr>
                                <td>&nbsp;</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>

                            <tr>
                                <td>&nbsp;</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>

                            <tr>
                                <td>&nbsp;</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>

                            <tr>
                                <td>&nbsp;</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>

                            <tr>
                                <td>&nbsp;</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>

                            <tr>
                                <td>&nbsp;</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </form>

        </div>
    </body>
</html>