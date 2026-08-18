<?php header("Content-type: text/css"); ?>

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
    margin: 0;
}

h2 {
    color: #ffffff;
    text-shadow: 0 2px 4px rgba(0,0,0,0.2);
    margin-bottom: 20px;
}

form {
    background: rgba(255, 255, 255, 0.95);
    padding: 30px 40px;
    border-radius: 12px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.3);
    width: 100%;
    max-width: 450px;
}

table {
    width: 100%;
    border-collapse: collapse;
}

td {
    padding: 10px 0;
    vertical-align: middle;
}

td:first-child {
    width: 35%;
}

label {
    font-weight: 600;
    font-size: 14px;
    color: #333333;
}

input[type="text"],
input[type="email"],
input[type="password"],
select,
input[type="file"] {
    width: 100%;
    padding: 10px;
    border: 1px solid #cccccc;
    border-radius: 6px;
    box-sizing: border-box;
    font-size: 14px;
    transition: border-color 0.3s;
}

input[type="text"]:focus,
input[type="email"]:focus,
input[type="password"]:focus,
select:focus {
    border-color: #667eea;
    outline: none;
    box-shadow: 0 0 5px rgba(102, 126, 234, 0.5);
}

input[type="checkbox"] {
    margin-right: 8px;
    transform: scale(1.2);
}

button[type="submit"],
input[type="submit"] {
    background: #667eea;
    color: #ffffff;
    border: none;
    padding: 12px 20px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: bold;
    transition: background 0.3s;
    width: 48%;
}

button[type="submit"]:hover,
input[type="submit"]:hover {
    background: #764ba2;
}

input[type="reset"] {
    background: #e2e8f0;
    color: #4a5568;
    border: none;
    padding: 12px 20px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: bold;
    transition: background 0.3s;
    width: 48%;
    float: right;
}

input[type="reset"]:hover {
    background: #cbd5e0;
}

a {
    color: #ffffff;
    text-decoration: none;
    font-weight: bold;
    margin-top: 15px;
    display: inline-block;
    transition: opacity 0.3s;
}

a:hover {
    opacity: 0.8;
    text-decoration: underline;
}