<?php

require_once __DIR__ . '/../Services/ProductService.php';
require_once __DIR__ . '/../Services/OrderService.php';
require_once __DIR__ . '/../Services/ReservationService.php';
require_once __DIR__ . '/../view/TableRenderer.php';

class IdSelectorHelper {

    private ProductService $productService;

    public function __construct(){
      $this->productService = new ProductService();
    }

    public static function showProducts(): void {
        $service = new ProductService();
        $products = $service->getAllProducts();

        if (empty($products)) {
            echo "Brak produktów w magazynie.\n";
            return;
        }

        $headers = ["ID", "Nazwa", "Cena", "SKU", "Ilość", "Min"];
        $rows = [];

        foreach ($products as $p) {
            $rows[] = [
                $p->id,
                $p->name,
                number_format($p->price, 2) . " zł",
                $p->sku,
                $p->quantity,
                $p->minQuantity
            ];
        }

        echo "\n=== LISTA PRODUKTÓW ===\n";
        TableRenderer::render($headers, $rows);
    }

    public static function showOrders(): void {
        $service = new OrderService();
        $orders = $service->getAllOrders();

        if (empty($orders)) {
            echo "Brak zamówień.\n";
            return;
        }

        $headers = ["ID", "Klient", "Status", "Utworzono"];
        $rows = [];

        foreach ($orders as $o) {
            $rows[] = [
                $o->id,
                $o->customerName,
                $o->status,
                $o->createdAt
            ];
        }

        echo "\n=== LISTA ZAMÓWIEŃ ===\n";
        TableRenderer::render($headers, $rows);
    }

    public static function showReservations(): void {
        $service = new ReservationService();
        $reservations = $service->getAllReservations();
        $productService = new ProductService();

        if (empty($reservations)) {
            echo "Brak rezerwacji.\n";
            return;
        }

        $headers = ["ID", "Produkt ID", "Ilość", "Utworzono"];
        $rows = [];

        foreach ($reservations as $r) {

            $product = $productService->getProductById($r->productId);
            $productName = $product ? $product->name : "Nieznany produkt";

            $rows[] = [
                $r->id,
                $productName,
                $r->quantity,
                $r->createdAt
            ];
        }

        echo "\n=== LISTA REZERWACJI ===\n";
        TableRenderer::render($headers, $rows);
    }

    public static function showItems(int $orderId): void {
      $orderService = new OrderService();
      $productService = new ProductService();

      $items = $orderService->getOrderItems($orderId);

      if (empty($items)) {
        echo "Brak pozycji w zamówieniu";
        return;
      }

      $headers = ["ID pozycji", "Produkt", "Ilość", "Cena", "Razem"];
        $rows = [];

        foreach ($items as $i) {
            $product = $productService->getProductById($i->productId);
            $name = $product ? $product->name : "Nieznany produkt";

            $rows[] = [
                $i->id,
                $name,
                $i->quantity,
                number_format($i->price, 2) . " zł",
                number_format($i->total, 2) . " zł"
            ];
        }

        TableRenderer::render($headers, $rows);
        
    }
}
