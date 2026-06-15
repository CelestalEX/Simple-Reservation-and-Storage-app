<?php

require_once __DIR__ . '/../Database/Database.php';
require_once __DIR__ . '/../Models/Product.php';

class ProductService {

    public function addProduct(Product $product): bool {
        $db = Database::getConnection();

      $query = $db->prepare("
        INSERT INTO products 
        (name, quantity, price, sku, category, description, unit, min_quantity, use_locations, weight, volume, created_at, updated_at)
        VALUES 
        (:name, :quantity, :price, :sku, :category, :description, :unit, :min_quantity, :use_locations, :weight, :volume, :created_at, :updated_at)
      ");

      return $query->execute([
        ':name' => $product->name,
        ':quantity' => $product->quantity,
        ':price'=> $product->price,
        ':sku' => $product->sku,
        ':category' => $product->category,
        ':description' => $product->description,
        ':unit' => $product->unit,
        ':min_quantity' => $product->minQuantity,
        ':use_locations' => $product->useLocations ? 1 : 0,
        ':weight'=> $product->weight,
        ':volume'=> $product->volume,
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
          $p = new Product();
          $p->id = (int)$row['id'];
          $p->name = $row['name'];
          $p->quantity = (int)$row['quantity'];
          $p->sku = $row['sku'];
          $p->category = $row['category'];
          $p->description = $row['description'];
          $p->unit = $row['unit'];
          $p->minQuantity = (int)$row['min_quantity'];
          $p->createdAt = $row['created_at'];
          $p->updatedAt = $row['updated_at'];
          $p->price = (float)$row['price'];
          $p->useLocations = (bool)$row['use_locations'];
          $p->weight = (float)$row['weight'];
          $p->volume = (float)$row['volume'];

          $products[] = $p;
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

        $p = new Product();
        $p->id = (int)$rows['id'];
        $p->name = $rows['name'];
        $p->sku = $rows['sku'];
        $p->category = $rows['category'];
        $p->price = (float)$rows['price'];
        $p->quantity = (int)$rows['quantity'];
        $p->minQuantity = (int)$rows['min_quantity'];
        $p->unit = $rows['unit'];
        $p->description = $rows['description'];
        $p->weight = (float)$rows['weight'];
        $p->volume = (float)$rows['volume'];
        $p->useLocations = (bool)$rows['use_locations'];
        $p->createdAt = $rows['created_at'];
        $p->updatedAt = $rows['updated_at'];

        return $p;
    }

    public function updateProduct(Product $product): bool {
        $db = Database::getConnection();

        $query = $db->prepare(
          'UPDATE products SET
            name = :name, 
            quantity = :quantity, 
            price = :price,
            sku = :sku,
            category = :category,
            description = :description,
            unit = :unit,
            min_quantity = :min_quantity,
            use_locations = :use_locations,
            weight = :weight,
            volume = :volume,
            updated_at = :updated_at
          WHERE id = :id'
        );
        return $query->execute([
            ':name' => $product->name,
            ':quantity' => $product->quantity,
            ':price' => $product->price,
            ':sku' => $product->sku,
            ':category' => $product->category,
            ':description' => $product->description,
            ':unit' => $product->unit,
            ':min_quantity' => $product->minQuantity,
            ':use_locations' => $product->useLocations ? 1 : 0,
            ':weight'=> $product->weight,
            ':volume'=> $product->volume,
            ':updated_at' => $product->updatedAt,
            ':id' => $product->id
        ]);
    }

    public function deleteProduct(int $id): bool {
        $db = Database::getConnection();

        $query = $db->prepare("DELETE FROM products WHERE id = :id");
        $query->execute([':id' => $id]);

        $reservation = $db->prepare('DELETE FROM reservations WHERE product_id = :id');
        return $reservation->execute([':id'=> $id]);
    }

    public function getWarehouseValueRaport(): array {
      $products = $this->getAllProducts();

      $report = [];
      $totalvalue = 0;

      foreach ($products as $p) {
        $totalvalue += $p->quantity * $p->price;
      }

      foreach ($products as $p) {
        $value = $p->quantity * $p->price;
        $percent = $totalvalue > 0 ? ($value / $totalvalue) * 100 : 0;

        $report[] = [
          'name'=> $p->name,
          'quantity'=> $p->quantity,
          'price'=> $p->price,
          'value'=> $value,
          'percent'=> $percent
        ];
      }

      return [
        'items'=> $report,
        'total'=> $totalvalue
      ];

    }

}
