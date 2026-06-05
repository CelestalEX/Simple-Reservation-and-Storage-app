<?php

require_once __DIR__ . '/../Database/Database.php';
require_once __DIR__ .'/../Models/Order.php';
require_once __DIR__ .'/../Models/OrderItem.php';

class OrderService {

  public function createOrder(string $customerName): int {
    $db = Database::getConnection();

    $now = date('Y-m-d H:i:s');

    $query = $db->prepare('
      INSERT INTO orders (customer_name, status, created_at, updated_at)
      VALUES (:name, "nowe", :created, :updated)
    ');

    $query->execute([
      ':name'=> $customerName,
      ':created'=> $now,
      ':updated'=> $now
    ]);

    return (int)$db->lastInsertId();
  }

  public function addItem(int $orderId, int $productId, int $quantity): bool {
    $db = Database::getConnection();

    $productQuery = $db->prepare("SELECT * FROM products WHERE id = :id");
    $productQuery->execute([':id' => $productId]);
    $product = $productQuery->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        return false;
    }

    $remaining = $product['quantity'] - $quantity;

    // Jeśli zamówienie obniżyło by stan poniżej minimum
    if ($remaining < $product['min_quantity']) {
      echo "Liczba zamówionego towaru obniżyłaby stan produktu poniżej minimum ({$product['min_quantity']}).\n";
      return false;
    }

    // Jeśli nie ma wystarczająco dużo produktu
    if ($product["quantity"] < $quantity) {
      echo "Nie można dodać pozycji - za mało towaru\n";
      return false;
    }

    // Dodawanie pozycji
    $price = (float)$product['price'];
    $total = $price * $quantity;

    $query = $db->prepare("
        INSERT INTO order_items (order_id, product_id, quantity, price, total)
        VALUES (:order_id, :product_id, :quantity, :price, :total)
    ");

    $query->execute([
        ':order_id' => $orderId,
        ':product_id' => $productId,
        ':quantity' => $quantity,
        ':price' => $price,
        ':total' => $total
    ]);

    $update = $db->prepare("
        UPDATE products SET quantity = quantity - :q WHERE id = :id
    ");
    $update->execute([':q' => $quantity, ':id' => $productId]);

    return true;
  }

  public function getAllOrders(): array {
    $db = Database::getConnection();
    $rows = $db->query("SELECT * FROM orders")->fetchAll(PDO::FETCH_ASSOC);

    $orders = [];
    foreach ($rows as $r) {
        $orders[] = new Order(
            $r['id'],
            $r['customer_name'],
            $r['status'],
            $r['created_at'],
            $r['updated_at']
        );
    }
    return $orders;
  }

  public function getOrderItems(int $orderId): array {
    $db = Database::getConnection();

    $query = $db->prepare("SELECT * FROM order_items WHERE order_id = :id");
    $query->execute([':id' => $orderId]);

    $rows = $query->fetchAll(PDO::FETCH_ASSOC);
    $items = [];

    foreach ($rows as $r) {
        $items[] = new OrderItem(
            $r['id'],
            $r['order_id'],
            $r['product_id'],
            $r['quantity'],
            $r['price']
        );
    }

    return $items;
  }

  public function cancelOrder(int $orderId): bool {
    $db = Database::getConnection();

    $items = $this->getOrderItems($orderId);

    foreach ($items as $item) {
        $query = $db->prepare("
            UPDATE products SET quantity = quantity + :q WHERE id = :id
        ");
        $query->execute([':q' => $item->quantity, ':id' => $item->productId]);
    }

    $query = $db->prepare("
        UPDATE orders SET status = 'anulowane' WHERE id = :id
    ");
    return $query->execute([':id' => $orderId]);
  }

  public function finalizeOrder(int $orderId): bool {
    $db = Database::getConnection();

    $query = $db->prepare("
        UPDATE orders SET status = 'zrealizowane' WHERE id = :id
    ");

    return $query->execute([':id' => $orderId]);
  }
}

?>