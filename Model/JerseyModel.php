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
}
