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
            $server->exec("CREATE DATABASE IF NOT EXISTS {$this->database} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

            $connection = new PDO(
                "mysql:host={$this->host};dbname={$this->database};charset=utf8mb4",
                $this->username,
                $this->password
            );
            $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            $this->createUsersTable($connection);
            $this->createSettingsTable($connection);
            $this->createDefaultAdmin($connection);

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
}
