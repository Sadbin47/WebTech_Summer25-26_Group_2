<?php

class ManagerModel
{
    private PDO $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    // =========================
    // EMPLOYEE FUNCTIONS
    // =========================

    public function getEmployees(): array
    {
        $statement = $this->connection->prepare("SELECT id, name, username, created_at FROM users
             WHERE role = 'Salesman'  ORDER BY id DESC" );

        $statement->execute();

        return $statement->fetchAll();
    }

    public function addEmployee( string $name,string $username, string $password ): bool 
    {

        $check = $this->connection->prepare( "SELECT id FROM users WHERE username = :username");

        $check->execute([ 'username' => $username]);

        if ($check->fetch()) {
            return false;
        }

        $statement = $this->connection->prepare("INSERT INTO users  (name, username, password, role)
             VALUES (:name, :username, :password, 'Salesman')");

        return $statement->execute(['name' => $name,'username' => $username,'password' => password_hash($password, PASSWORD_DEFAULT)]);
    }

    public function deleteEmployee(int $id): bool
    {
        $statement = $this->connection->prepare( "DELETE FROM users  WHERE id = :id AND role = 'Salesman'"
        );

        return $statement->execute([
            'id' => $id
        ]);
    }

    public function getEmployee(int $id): ?array
    {
        $statement = $this->connection->prepare( "SELECT id, name, username FROM users WHERE id = :id AND role = 'Salesman'"
        );

        $statement->execute([
            'id' => $id
        ]);

        $employee = $statement->fetch();

        return $employee ?: null;
    }

    public function updateEmployee( int $id,string $name,string $username ): bool
     {

        $statement = $this->connection->prepare( "UPDATE users   SET name = :name, username = :username
             WHERE id = :idAND role = 'Salesman'");

        return $statement->execute([ 'id' => $id, 'name' => $name,'username' => $username]);
    }

    // PRODUCT FUNCTIONS
    public function getProducts(): array
    {
        $statement = $this->connection->prepare("SELECT *FROM jerseys ORDER BY id DESC" );

        $statement->execute();

        return $statement->fetchAll();
    }

    public function addProduct( string $name,string $size,string $category,float $price,int $quantity ): bool 
    {
        $statement = $this->connection->prepare("INSERT INTO jerseys (name, size, category, price, quantity)
         VALUES (:name, :size, :category, :price, :quantity)"
        );

        return $statement->execute([ 'name' => $name,'size' => $size,'category' => $category,'price' => $price,'quantity' => $quantity ]);
    }

    public function updateProduct( int $id,string $name,string $size,string $category,float $price,int $quantity): bool 
    {

        $statement = $this->connection->prepare("UPDATE jerseys SET name = :name,  size = :size,
                 category = :category, price = :price, quantity = :quantity WHERE id = :id"
        );

        return $statement->execute(['id' => $id,'name' => $name,'size' => $size,'category' => $category,
            'price' => $price,'quantity' => $quantity
        ]);
    }

    public function deleteProduct(int $id): bool
    {
        $statement = $this->connection->prepare(
            "DELETE FROM jerseys WHERE id = :id"
        );

        return $statement->execute([ 'id' => $id]);
    }

    // MANAGER INFORMATION
   
    public function getManager(int $id): ?array
    {
        $statement = $this->connection->prepare( "SELECT id, name, username FROM users WHERE id = :id AND role = 'Manager'"
        );

        $statement->execute(['id' => $id]);

        $manager = $statement->fetch();

        return $manager ?: null;
    }

  public function updateManager(
    int $id,
    string $name,
    string $username,
    ?string $password = null
): bool {

    if ($password !== null) {

        $statement = $this->connection->prepare(
            "UPDATE users
             SET name = :name,
                 username = :username,
                 password = :password
             WHERE id = :id
             AND role = 'Manager'"
        );

        return $statement->execute([
            'id' => $id,
            'name' => $name,
            'username' => $username,
            'password' => $password
        ]);
    }

    $statement = $this->connection->prepare(
        "UPDATE users
         SET name = :name,
             username = :username
         WHERE id = :id
         AND role = 'Manager'"
    );

    return $statement->execute([
        'id' => $id,
        'name' => $name,
        'username' => $username
    ]);
}
}