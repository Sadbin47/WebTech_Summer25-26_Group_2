<?php header('Content-Type: text/css'); ?>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    background: #f2f2f2;
    color: #222222;
    font-family: Arial, sans-serif;
    text-align: center;
}

h2 {
    margin: 35px 0 15px;
}

form {
    width: 420px;
    margin: 0 auto;
    padding: 20px;
    border: 1px solid #cccccc;
    background: #ffffff;
    text-align: left;
}

table {
    width: 100%;
    border-collapse: collapse;
}

td {
    padding: 8px 4px;
}

td:first-child {
    width: 35%;
}

label {
    font-size: 14px;
    font-weight: bold;
}

input[type="text"],
input[type="email"],
input[type="password"],
select {
    width: 100%;
    padding: 8px;
    border: 1px solid #aaaaaa;
}

input[type="checkbox"] {
    margin-right: 5px;
}

button,
input[type="submit"],
input[type="reset"] {
    margin-right: 5px;
    padding: 8px 15px;
    border: 1px solid #555555;
    background: #eeeeee;
    color: #222222;
    cursor: pointer;
}

button,
input[type="submit"] {
    background: #28795c;
    color: #ffffff;
}

a {
    color: #245a45;
}

.message {
    width: 420px;
    margin: 0 auto 15px;
    padding: 10px;
    color: #ffffff;
}

.error {
    background: #b94747;
}

.success {
    background: #28795c;
}
