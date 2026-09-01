# JerseyTrack

JerseyTrack is a role-based jersey inventory and sales management platform built for a Web Technologies course project. It provides separate dashboards for administrators, managers, salesmen, and customers.

## Features

- User registration and secure login
- Password hashing and session-based authentication
- Role-based dashboards and navigation
- Admin user management with role assignment
- Admin profile, password, tax, and shipping settings
- Revenue and order summary for administrators
- Manager inventory and reporting interface
- Salesman point-of-sale interface
- Customer product, cart, checkout, and order history interface
- Responsive layouts for desktop and mobile screens

> **Project status:** Authentication and admin management are connected to the database. The manager, salesman, and customer dashboards currently contain interface prototypes, with their full database operations still under development.

## Tech Stack

- PHP 8+
- MySQL / MariaDB
- HTML5
- CSS3
- JavaScript
- PDO for database access
- Apache (XAMPP recommended)

## Getting Started

### Requirements

- [XAMPP](https://www.apachefriends.org/) or another PHP/MySQL environment
- PHP 8.0 or newer with the PDO MySQL extension
- Git

### Installation

1. Clone the repository into the XAMPP `htdocs` directory:

   ```bash
   git clone https://github.com/Sadbin47/WebTech_Summer25-26_Group_2.git
   ```

2. Start **Apache** and **MySQL** from the XAMPP Control Panel.

3. Open the project in your browser:

   ```text
   http://localhost/WebTech_Summer25-26_Group_2/
   ```

No SQL import is required. On the first database connection, the application automatically creates the `jerseytrack_db` database and its required tables.

### Default Admin Account

```text
Username: admin
Password: admin
```

Change the default password after the first login. If your MySQL credentials differ from the XAMPP defaults, update them in `Model/db.php`.

## Project Structure

```text
WebTech_Summer25-26_Group_2/
|-- Controller/    # Authentication and request handling
|-- Model/         # Database connection and data access
|-- View/          # Login, registration, and role dashboards
|-- index.php      # Application entry point
`-- README.md
```

## User Roles

| Role | Main Access |
| --- | --- |
| Admin | Users, roles, settings, profile, and revenue summary |
| Manager | Jersey inventory, sales overview, and reports |
| Salesman | Sales entry and sales history |
| Customer | Products, cart, checkout, and order history |

New users are registered as customers. An administrator can create users or change their roles from the admin dashboard.

## Project Purpose

This project was created for academic and educational purposes.
