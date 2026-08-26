<?php

class UserModel
{
    private PDO $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    public function findByUsername(string $username): array|false
    {
        $statement = $this->connection->prepare(
            'SELECT id, name, username, password, role
             FROM users
             WHERE username = :username'
        );
        $statement->execute(['username' => $username]);

        return $statement->fetch();
    }

    public function findById(int $id): array|false
    {
        $statement = $this->connection->prepare(
            'SELECT id, name, username, password, role FROM users WHERE id = :id'
        );
        $statement->execute(['id' => $id]);
        return $statement->fetch();
    }

    public function getAllUsers(): array
    {
        $statement = $this->connection->query(
            'SELECT id, name, username, role FROM users ORDER BY id DESC'
        );
        return $statement->fetchAll();
    }

    public function createUser(string $name, string $username, string $password, string $role): bool
    {
        $statement = $this->connection->prepare(
            'INSERT INTO users (name, username, password, role)
             VALUES (:name, :username, :password, :role)'
        );
        return $statement->execute([
            'name' => $name,
            'username' => $username,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'role' => $role
        ]);
    }

    public function updateRole(int $id, string $role): bool
    {
        $statement = $this->connection->prepare(
            'UPDATE users SET role = :role WHERE id = :id'
        );
        return $statement->execute(['role' => $role, 'id' => $id]);
    }

    public function deleteUser(int $id): bool
    {
        $statement = $this->connection->prepare('DELETE FROM users WHERE id = :id');
        return $statement->execute(['id' => $id]);
    }

    public function updateProfile(int $id, string $name, string $username): bool
    {
        $statement = $this->connection->prepare(
            'UPDATE users SET name = :name, username = :username WHERE id = :id'
        );
        return $statement->execute([
            'name' => $name,
            'username' => $username,
            'id' => $id
        ]);
    }

    public function updatePassword(int $id, string $password): bool
    {
        $statement = $this->connection->prepare(
            'UPDATE users SET password = :password WHERE id = :id'
        );
        return $statement->execute([
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'id' => $id
        ]);
    }

    public function getSettings(): array
    {
        $statement = $this->connection->query(
            'SELECT tax_rate, shipping_zone FROM system_settings WHERE id = 1'
        );
        return $statement->fetch() ?: ['tax_rate' => 5, 'shipping_zone' => 'Inside Dhaka'];
    }

    public function saveSettings(float $taxRate, string $shippingZone): bool
    {
        $statement = $this->connection->prepare(
            'UPDATE system_settings
             SET tax_rate = :tax_rate, shipping_zone = :shipping_zone
             WHERE id = 1'
        );
        return $statement->execute([
            'tax_rate' => $taxRate,
            'shipping_zone' => $shippingZone
        ]);
    }
}
