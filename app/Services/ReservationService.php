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

        // Sprawdź dostępność
        $product = $this->productService->getProductById($productId);

        if (!$product || $product->quantity < $quantity) {
            return false;
        }

        // Zmniejsz stan magazynowy
        $product->quantity -= $quantity;
        $this->productService->updateProduct($product);

        // Zapisz rezerwację
        $stmt = $db->prepare("
            INSERT INTO reservations (product_id, quantity, created_at)
            VALUES (:product_id, :quantity, :created_at)
        ");

        $stmt->execute([
            ':product_id' => $productId,
            ':quantity' => $quantity,
            ':created_at' => date('Y-m-d H:i:s')
        ]);

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
        return false;
      }

      $product = $this->productService->getProductById($row['product_id']);

      $product->quantity += $row['quantity'];
      $this->productService->updateProduct($product);

      $query = $db->prepare('DELETE FROM reservations WHERE id = :id');
      $query->execute([':id' => $id]);

      return true;
    }

  }

?>