<?php

require_once __DIR__ . '/../Database/Database.php';
require_once __DIR__ . '/../Models/Product.php';

class ProductService {

    public function addProduct(Product $product): void {
        $db = Database::getConnection();

      $stmt = $db->prepare("
        INSERT INTO products 
        (name, quantity, sku, category, description, unit, min_quantity, created_at, updated_at)
        VALUES 
        (:name, :quantity, :sku, :category, :description, :unit, :min_quantity, :created_at, :updated_at)
      ");

      $stmt->execute([
        ':name' => $product->name,
        ':quantity' => $product->quantity,
        ':sku' => $product->sku,
        ':category' => $product->category,
        ':description' => $product->description,
        ':unit' => $product->unit,
        ':min_quantity' => $product->minQuantity,
        ':created_at' => $product->createdAt,
        ':updated_at' => $product->updatedAt
        ]);
      }
    
    public function getAllProducts(): array {
        $db = Database::getConnection();

        $query = $db->query("SELECT * FROM products");
        $rows = $query->fetchAll(PDO::FETCH_ASSOC);

        $products = [];
        foreach ($rows as $row) {
            $products[] = new Product(
                $row['id'],
                $row['name'],
                $row['quantity'],
                $row['sku'],
                $row['category'],
                $row['description'],
                $row['unit'],
                $row['min_quantity'],
                $row['created_at'],
                $row['updated_at']
            );
        }

        return $products;
    }

    public function getProductById(int $id): ?Product {
        $db = Database::getConnection();

        $query = $db->prepare('SELECT * FROM products WHERE id = :id');
        $query->execute([':id' => $id]);

        $rows = $query->fetch(PDO::FETCH_ASSOC);

        if(!$rows){
            return null;
        }

        return new Product(
          $rows['id'],
          $rows['name'],
          $rows['quantity'],
          $rows['sku'],
          $rows['category'],
          $rows['description'],
          $rows['unit'],
          $rows['min_quantity'],
          $rows['created_at'],
          $rows['updated_at']
    );
    }

    public function updateProduct(Product $product): void {
        $db = Database::getConnection();

        $query = $db->prepare('UPDATE products SET name = :name, quantity = :quantity WHERE id = :id');
        $query->execute([
            ':name'=> $product->name,
            ':quantity' => $product->quantity,
            ":id" => $product->id
        ]);
    }

    public function deleteProduct(int $id): void {
        $db = Database::getConnection();

        $query = $db->prepare("DELETE FROM products WHERE id = :id");
        $query->execute([':id' => $id]);

        $reservation = $db->prepare('DELETE FROM reservations WHERE product_id = :id');
        $reservation->execute([':id'=> $id]);
    }


}
