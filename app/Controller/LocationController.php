<?php

require_once __DIR__."/../view/MenuRenderer.php";
require_once __DIR__."/../view/TableRenderer.php";
require_once __DIR__."/../helpers/InputHelper.php";
require_once __DIR__."/../Services/LocationService.php";
require_once __DIR__."/../Services/ProductLocationService.php";
require_once __DIR__."/../Services/ProductService.php";
require_once __DIR__."/../helpers/IdSelectorHelper.php";

class LocationController
{
    private LocationService $locationService;
    private ProductLocationService $productLocationService;
    private ProductService $productService;

    public function __construct()
    {
        $this->locationService = new LocationService();
        $this->productLocationService = new ProductLocationService();
        $this->productService = new ProductService();
    }

    public function listLocations(): void
    {
        $locations = $this->locationService->getAll();

        echo "\n=== LISTA LOKALIZACJI ===\n";

        if (empty($locations)) {
            echo "Brak lokalizacji.\n";
            return;
        }

        $headers = ["ID","Kod", "Opis","Utworzono"];
        $rows = [];

        foreach ($locations as $loc) {
          $rows[] = [
            $loc->id,
            $loc->code,
            (string)($loc->description ?? "-"),
            $loc->createdAt
          ];
        }

        TableRenderer::render($headers, $rows);
    }

    public function createLocation(): void
    {
        echo "\n=== DODAWANIE LOKALIZACJI ===\n";

        $code = InputHelper::readString("Kod lokalizacji (np. A1-R2-P3): ");
        $desc = InputHelper::readString("Opis (opcjonalnie): ", true);

        if ($this->locationService->create($code, $desc)) {
            echo "Lokalizacja dodana.\n";
        } else {
            echo "Błąd podczas dodawania lokalizacji.\n";
        }
    }

    public function showProductLocations(): void
    {
        echo "\n=== ROZMIESZCZENIE PRODUKTU ===\n";

        IdSelectorHelper::showProducts();
        $productId = InputHelper::readInt("Podaj ID produktu: ");
        $product = $this->productService->getProductById($productId);

        if (!$product) {
            echo "Produkt nie istnieje.\n";
            return;
        }

        $locations = $this->productLocationService->getLocationsForProduct($productId);

        echo "\nProdukt: {$product->name}\n";

        if (empty($locations)) {
            echo "Produkt nie znajduje się w żadnej lokalizacji.\n";
            return;
        }

        echo "+--------------+--------+\n";
        echo "| Lokalizacja  | Ilość  |\n";
        echo "+--------------+--------+\n";

        foreach ($locations as $pl) {
            $loc = $this->locationService->getById($pl->locationId);
            echo "| {$loc->code}           | {$pl->quantity}\n";
        }

        echo "+--------------+--------+\n";
        echo "SUMA: " . $this->productLocationService->getTotalQuantity($productId) . " szt.\n";
    }

    public function moveProduct(): void
    {
        echo "\n=== PRZESUNIĘCIE PRODUKTU ===\n";

        IdSelectorHelper::showProducts();
        $productId = InputHelper::readInt("Podaj ID produktu: ");
        $product = $this->productService->getProductById($productId);

        if (!$product) {
            echo "Produkt nie istnieje.\n";
            return;
        }

        IdSelectorHelper::showLocations();
        $from = InputHelper::readInt("ID lokalizacji źródłowej: ");
        $to = InputHelper::readInt("ID lokalizacji docelowej: ");
        $qty = InputHelper::readInt("Ilość do przesunięcia: ");

        $confirm = strtolower(
            InputHelper::readString("Potwierdzić przesunięcie $qty szt.? (t/N): ", true)
        ) ?: "n";

        if ($confirm !== "t") {
            echo "Anulowano.\n";
            return;
        }

        if ($this->productLocationService->move($productId, $from, $to, $qty)) {
            echo "Przesunięcie wykonane.\n";
        } else {
            echo "Błąd: brak ilości lub niepoprawne lokalizacje.\n";
        }
    }
}
