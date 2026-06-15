<?php

require_once __DIR__ . '/../Services/ProductService.php';
require_once __DIR__ . '/../Services/OrderService.php';
require_once __DIR__ . '/../Services/ReservationService.php';
require_once __DIR__ . '/../Services/LocationService.php';
require_once __DIR__ . '/../Services/ProductLocationService.php';
require_once __DIR__ . '/../view/TableRenderer.php';

class IdSelectorHelper {

    private ProductService $productService;
    private LocationService $locationService;
    private ProductLocationService $productLocationService;

    public function __construct(){
      $this->productService = new ProductService();
      $this->locationService = new LocationService();
      $this->productLocationService = new ProductLocationService();
    }

    public static function showProducts(): void {
      $service = new ProductService();
      $products = $service->getAllProducts();

      if (empty($products)) {
        echo "Brak produktów w magazynie.\n";
        return;
      }

      echo "\n=== LISTA PRODUKTÓW ===\n";

      $headers = [
        "ID", "Nazwa", "SKU", "Kategoria",
        "Ilość", "Min", "Jedn.",
        "Cena", "Waga", "Objętość",
        "Lokacje?"
      ];

      $rows = [];

      foreach ($products as $p) {
        $rows[] = [
            $p->id,
            $p->name,
            $p->sku,
            $p->category,
            $p->quantity,
            $p->minQuantity,
            $p->unit,
            number_format($p->price, 2) . " zł",
            number_format($p->weight, 2) . " kg",
            number_format($p->volume, 3) . " m3",
            $p->useLocations ? "TAK" : "NIE"
        ];
      }

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
        echo "Brak pozycji w zamówieniu\n";
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

    public static function showLocations(): void {
      $locationService = new LocationService();
      $productLocationService = new ProductLocationService();
      
      $locations = $locationService->getAll();

    echo "\n=== LISTA LOKALIZACJI ===\n";

    if (empty($locations)) {
        echo "Brak lokalizacji.\n";
        return;
    }

    $headers = [
        "ID", "Kod", "Typ", "Status",
        "Zajęte szt", "Max szt", "Wolne szt",
        "Zajęte kg", "Max kg", "Wolne kg",
        "Zajęte m3", "Max m3", "Wolne m3"
    ];

    $rows = [];

    foreach ($locations as $loc) {
        $usage = $productLocationService->getLocationUsage($loc->id);
        $free = $productLocationService->getFreeCapacity($loc, $usage);

        $rows[] = [
          $loc->id,
            $loc->code,
            $loc->type,
            $loc->status,

            $usage['units'],
            $loc->maxUnits ?? "-",
            $free['units'] ?? "-",

            number_format($usage['weight'], 2),
            $loc->maxWeight ?? "-",
            $free['weight'] ?? "-",

            number_format($usage['volume'], 3),
            $loc->maxVolume ?? "-",
            $free['volume'] ?? "-"
        ];
    }

    TableRenderer::render($headers, $rows);
}
}