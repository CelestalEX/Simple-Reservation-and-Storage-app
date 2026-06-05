<?php

  require_once __DIR__ ."/../Models/Reservation.php";
  require_once __DIR__ ."/../Database/Database.php";
  require_once __DIR__ ."/ProductService.php";

  class ReservationService {

    private ProductService $productService;

    public function __construct() {
      $this->productService = new productService();
    }

    public function createReservation(int $productId, int $quantity): bool {
      $db = Database::getConnection();

      // Pobieranie produktu
      $query = $db->prepare("SELECT * FROM products WHERE id = :id");
      $query->execute([':id' => $productId]);
      $product = $query->fetch(PDO::FETCH_ASSOC);

      if (!$product) {
        echo "Produkt nie istnieje.\n";
        return false;
      }

      // Stan po rezerwacji
      $remaining = $product['quantity'] - $quantity;

      // Jeśli zamówienie obniżyło by stan poniżej minimum
      if ($remaining < $product['min_quantity']) {
        echo "Nie można utworzyć rezerwacji — obniżyłaby stan poniżej minimum ({$product['min_quantity']}).\n";
        return false;
      }

      // Jeśli nie ma wystarczająco dużo produktu
      if ($product['quantity'] < $quantity) {
        echo "Nie można utworzyć rezerwacji — za mało towaru.\n";
        return false;
      }

      // Dodaj rezerwację
      $stmt = $db->prepare("
        INSERT INTO reservations (product_id, quantity, created_at)
        VALUES (:product_id, :quantity, :created_at)
      ");

      $stmt->execute([
        ':product_id' => $productId,
        ':quantity' => $quantity,
        ':created_at' => date('Y-m-d H:i:s')
      ]);

      // Zmniejsz stan magazynowy
      $update = $db->prepare("
        UPDATE products SET quantity = quantity - :q WHERE id = :id
      ");
      $update->execute([':q' => $quantity, ':id' => $productId]);

      return true;
    }

    public function getAllReservations(): array {
      $db = Database::getConnection();

      $query = $db->query("SELECT * FROM reservations");
      $rows = $query->fetchAll(PDO::FETCH_ASSOC);

      $reservations = [];

      foreach ($rows as $row) {
        $reservations[] = new Reservation(
          $row["id"],
          $row["product_id"],
          $row["quantity"],
          $row["created_at"],
        );
        echo"\n";
      }

      return $reservations;

    }

    public function cancelReservation(int $id): bool {
      $db = Database::getConnection();

      $query = $db->prepare("SELECT * FROM reservations WHERE id = :id");
      $query->execute([':id' => $id]);

      $row = $query->fetch(PDO::FETCH_ASSOC);

      if (!$row) {
        echo "Rezerwacja nie istnieje.\n";
        return false;
      }

      $update = $db->prepare("
        UPDATE products 
        SET quantity = quantity + :q 
        WHERE id = :id
      ");
      $update->execute([
        ':q' => $row['quantity'],
        ':id' => $row['product_id']
      ]);

      $delete = $db->prepare('DELETE FROM reservations WHERE id = :id');
      $delete->execute([':id' => $id]);

      return true;
    }

  }

?>