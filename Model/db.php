<<<<<<< Updated upstream
=======
<?php

class Database
{
    private string $host = 'localhost';
    private string $database = 'jerseytrack_db';
    private string $username = 'root';
    private string $password = '';

    public function connect(): PDO
    {
        try {
            $server = new PDO(
                "mysql:host={$this->host};charset=utf8mb4",
                $this->username,
                $this->password
            );
            $server->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $server->exec(
                "CREATE DATABASE IF NOT EXISTS {$this->database}
                 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
            );

            $connection = new PDO(
                "mysql:host={$this->host};dbname={$this->database};charset=utf8mb4",
                $this->username,
                $this->password
            );
            $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            $this->createUsersTable($connection);
            $this->createSettingsTable($connection);
            $this->createOrdersTable($connection);
            $this->upgradeOrdersTable($connection);
            $this->createJerseysTable($connection);
            $this->createOrderItemsTable($connection);
            $this->createPromoCodesTable($connection);
            $this->createRestockRequestsTable($connection);
            $this->createDefaultAdmin($connection);
            $this->createDefaultManager($connection);

            // Demo data so the Salesman POS can be tested before Manager module is ready.
            $this->seedDemoJerseys($connection);
            $this->seedPromoCodes($connection);

            return $connection;
        } catch (PDOException $exception) {
            exit('Database connection failed. Please start MySQL from XAMPP.');
        }
    }

    private function createUsersTable(PDO $connection): void
    {
        $connection->exec(
            "CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                username VARCHAR(50) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                role VARCHAR(20) NOT NULL DEFAULT 'Customer',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )"
        );
    }

    private function createDefaultAdmin(PDO $connection): void
    {
        $connection->exec(
            "CREATE TABLE IF NOT EXISTS app_meta (
                meta_key VARCHAR(50) PRIMARY KEY,
                meta_value VARCHAR(100) NOT NULL
            )"
        );

        $seedCheck = $connection->prepare(
            'SELECT meta_value FROM app_meta WHERE meta_key = :meta_key'
        );
        $seedCheck->execute(['meta_key' => 'admin_seeded']);

        if ($seedCheck->fetch()) {
            return;
        }

        $check = $connection->prepare('SELECT id FROM users WHERE username = :username');
        $check->execute(['username' => 'admin']);

        if (!$check->fetch()) {
            $insert = $connection->prepare(
                'INSERT INTO users (name, username, password, role)
                 VALUES (:name, :username, :password, :role)'
            );
            $insert->execute([
                'name' => 'Administrator',
                'username' => 'admin',
                'password' => password_hash('admin', PASSWORD_DEFAULT),
                'role' => 'Admin'
            ]);
        }

        $markSeeded = $connection->prepare(
            'INSERT INTO app_meta (meta_key, meta_value) VALUES (:meta_key, :meta_value)'
        );
        $markSeeded->execute([
            'meta_key' => 'admin_seeded',
            'meta_value' => '1'
        ]);
    }
  
private function createDefaultManager(PDO $connection): void
{
    $seedCheck = $connection->prepare(
        'SELECT meta_value FROM app_meta WHERE meta_key = :meta_key'
    );

    $seedCheck->execute([
        'meta_key' => 'manager_seeded'
    ]);

    if ($seedCheck->fetch()) {
        return;
    }

    $check = $connection->prepare(
        'SELECT id FROM users WHERE username = :username'
    );

    $check->execute([
        'username' => 'manager'
    ]);

    if (!$check->fetch()) {
        $insert = $connection->prepare(
            'INSERT INTO users (name, username, password, role)
             VALUES (:name, :username, :password, :role)'
        );

        $insert->execute([
            'name' => 'Manager',
            'username' => 'manager',
            'password' => password_hash('manager', PASSWORD_DEFAULT),
            'role' => 'Manager'
        ]);
    }

    $markSeeded = $connection->prepare(
        'INSERT INTO app_meta (meta_key, meta_value)
         VALUES (:meta_key, :meta_value)'
    );

    $markSeeded->execute([
        'meta_key' => 'manager_seeded',
        'meta_value' => '1'
    ]);
}

    private function createSettingsTable(PDO $connection): void
    {
        $connection->exec(
            "CREATE TABLE IF NOT EXISTS system_settings (
                id INT PRIMARY KEY,
                tax_rate DECIMAL(5,2) NOT NULL DEFAULT 5.00,
                shipping_zone VARCHAR(50) NOT NULL DEFAULT 'Inside Dhaka'
            )"
        );

        $statement = $connection->prepare(
            'INSERT IGNORE INTO system_settings (id, tax_rate, shipping_zone)
             VALUES (1, 5.00, :shipping_zone)'
        );
        $statement->execute(['shipping_zone' => 'Inside Dhaka']);
    }

    private function createOrdersTable(PDO $connection): void
    {
        // Keep the original columns so Admin/Customer code remains compatible.
        $connection->exec(
            "CREATE TABLE IF NOT EXISTS orders (
                id INT AUTO_INCREMENT PRIMARY KEY,
                customer_id INT NULL,
                total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                status VARCHAR(20) NOT NULL DEFAULT 'Processing',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )"
        );
    }

    private function upgradeOrdersTable(PDO $connection): void
    {
        // These extra columns support Salesman POS orders without removing old columns.
        $this->addColumnIfMissing($connection, 'orders', 'salesman_id', 'INT NULL AFTER customer_id');
        $this->addColumnIfMissing($connection, 'orders', 'customer_name', 'VARCHAR(100) NULL AFTER salesman_id');
        $this->addColumnIfMissing($connection, 'orders', 'customer_phone', 'VARCHAR(30) NULL AFTER customer_name');
        $this->addColumnIfMissing($connection, 'orders', 'customer_email', 'VARCHAR(120) NULL AFTER customer_phone');
        $this->addColumnIfMissing($connection, 'orders', 'subtotal_amount', 'DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER customer_email');
        $this->addColumnIfMissing($connection, 'orders', 'total_quantity', 'INT NOT NULL DEFAULT 0 AFTER subtotal_amount');
        $this->addColumnIfMissing($connection, 'orders', 'discount_amount', 'DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER total_quantity');
        $this->addColumnIfMissing($connection, 'orders', 'promo_code', 'VARCHAR(30) NULL AFTER discount_amount');
        $this->addColumnIfMissing($connection, 'orders', 'purchase_date', 'DATE NULL AFTER promo_code');
    }

    private function addColumnIfMissing(
        PDO $connection,
        string $table,
        string $column,
        string $definition
    ): void {
        $check = $connection->query(
            "SHOW COLUMNS FROM `{$table}` LIKE " . $connection->quote($column)
        );

        if (!$check->fetch()) {
            $connection->exec(
                "ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}"
            );
        }
    }

    private function createJerseysTable(PDO $connection): void
    {
        $connection->exec(
            "CREATE TABLE IF NOT EXISTS jerseys (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(120) NOT NULL,
                size VARCHAR(10) NOT NULL,
                category VARCHAR(50) NOT NULL DEFAULT 'Club Jersey',
                price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                quantity INT NOT NULL DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_jersey_variant (name, size)
            )"
        );
    }

    private function createOrderItemsTable(PDO $connection): void
    {
        $connection->exec(
            "CREATE TABLE IF NOT EXISTS order_items (
                id INT AUTO_INCREMENT PRIMARY KEY,
                order_id INT NOT NULL,
                jersey_id INT NOT NULL,
                jersey_name VARCHAR(120) NOT NULL,
                size VARCHAR(10) NOT NULL,
                unit_price DECIMAL(10,2) NOT NULL,
                quantity INT NOT NULL,
                subtotal DECIMAL(10,2) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX (order_id),
                INDEX (jersey_id)
            )"
        );
    }

    private function createPromoCodesTable(PDO $connection): void
    {
        $connection->exec(
            "CREATE TABLE IF NOT EXISTS promo_codes (
                id INT AUTO_INCREMENT PRIMARY KEY,
                code VARCHAR(30) NOT NULL UNIQUE,
                discount_percent DECIMAL(5,2) NOT NULL DEFAULT 0.00,
                start_date DATE NOT NULL,
                expiry_date DATE NOT NULL,
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )"
        );
    }

    private function createRestockRequestsTable(PDO $connection): void
    {
        $connection->exec(
            "CREATE TABLE IF NOT EXISTS restock_requests (
                id INT AUTO_INCREMENT PRIMARY KEY,
                salesman_id INT NOT NULL,
                jersey_id INT NOT NULL,
                requested_quantity INT NOT NULL,
                reason VARCHAR(255) NULL,
                status VARCHAR(20) NOT NULL DEFAULT 'Pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX (salesman_id),
                INDEX (jersey_id)
            )"
        );
    }

    private function seedDemoJerseys(PDO $connection): void
    {
        $count = (int) $connection->query('SELECT COUNT(*) FROM jerseys')->fetchColumn();

        if ($count > 0) {
            return;
        }

        $insert = $connection->prepare(
            'INSERT INTO jerseys (name, size, category, price, quantity)
             VALUES (:name, :size, :category, :price, :quantity)'
        );

        $demoJerseys = [
            ['Arsenal Home Kit', 'S', 'Club Jersey', 2800, 12],
            ['Arsenal Home Kit', 'M', 'Club Jersey', 2800, 10],
            ['Arsenal Home Kit', 'L', 'Club Jersey', 2800, 8],
            ['Arsenal Away Kit', 'S', 'Club Jersey', 2800, 9],
            ['Arsenal Away Kit', 'M', 'Club Jersey', 2800, 11],
            ['Arsenal Away Kit', 'L', 'Club Jersey', 2800, 7],
            ['Liverpool Home Kit', 'M', 'Club Jersey', 3000, 10],
            ['Real Madrid Home Kit', 'L', 'Club Jersey', 3200, 6]
        ];

        foreach ($demoJerseys as $jersey) {
            $insert->execute([
                'name' => $jersey[0],
                'size' => $jersey[1],
                'category' => $jersey[2],
                'price' => $jersey[3],
                'quantity' => $jersey[4]
            ]);
        }
    }

    private function seedPromoCodes(PDO $connection): void
    {
        $insert = $connection->prepare(
            'INSERT IGNORE INTO promo_codes
             (code, discount_percent, start_date, expiry_date, is_active)
             VALUES (:code, :discount, :start_date, :expiry_date, :is_active)'
        );

        $promos = [
            ['JT10', 10, '2026-01-01', '2030-12-31', 1],
            ['SAVE5', 5, '2026-01-01', '2030-12-31', 1],
            ['OLD10', 10, '2025-01-01', '2025-12-31', 1]
        ];

        foreach ($promos as $promo) {
            $insert->execute([
                'code' => $promo[0],
                'discount' => $promo[1],
                'start_date' => $promo[2],
                'expiry_date' => $promo[3],
                'is_active' => $promo[4]
            ]);
        }
    }
}
>>>>>>> Stashed changes
