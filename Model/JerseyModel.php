<?php

class JerseyModel
{
    private PDO $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    public function getAllJerseys(): array
    {
        $statement = $this->connection->query(
            'SELECT id, name, size, category, price, quantity
             FROM jerseys
             ORDER BY name ASC, size ASC'
        );

        return $statement->fetchAll();
    }

    public function getAvailableJerseys(): array
    {
        $statement = $this->connection->query(
            'SELECT id, name, size, category, price, quantity
             FROM jerseys
             WHERE quantity > 0
             ORDER BY name ASC, size ASC'
        );

        return $statement->fetchAll();
    }

    public function findById(int $jerseyId): ?array
    {
        $statement = $this->connection->prepare(
            'SELECT id, name, size, category, price, quantity
             FROM jerseys
             WHERE id = :id'
        );
        $statement->execute(['id' => $jerseyId]);
        $jersey = $statement->fetch();

        return $jersey ?: null;
    }

    public function createRestockRequest(
        int $salesmanId,
        int $jerseyId,
        int $requestedQuantity,
        string $reason
    ): bool {
        $statement = $this->connection->prepare(
            'INSERT INTO restock_requests
             (salesman_id, jersey_id, requested_quantity, reason, status)
             VALUES (:salesman_id, :jersey_id, :requested_quantity, :reason, :status)'
        );

        return $statement->execute([
            'salesman_id' => $salesmanId,
            'jersey_id' => $jerseyId,
            'requested_quantity' => $requestedQuantity,
            'reason' => $reason !== '' ? $reason : null,
            'status' => 'Pending'
        ]);
    }

    public function getRestockRequestsBySalesman(int $salesmanId): array
    {
        $statement = $this->connection->prepare(
            'SELECT r.id, r.requested_quantity, r.reason, r.status, r.created_at,
                    j.name AS jersey_name, j.size
             FROM restock_requests r
             INNER JOIN jerseys j ON j.id = r.jersey_id
             WHERE r.salesman_id = :salesman_id
             ORDER BY r.id DESC'
        );
        $statement->execute(['salesman_id' => $salesmanId]);

        return $statement->fetchAll();
    }
}
