<?php

require_once __DIR__ . '/../view/TableRenderer.php';
require_once __DIR__ .'/../Models/Product.php';
require_once __DIR__ .'/../helpers/InputHelper.php';
require_once __DIR__ .'/../Database/Database.php';

class ProductController {

    private ProductService $productService;

    public function __construct() {
        $this->productService = new ProductService();
    }

    public function list(): void {
        $products = $this->productService->getAllProducts();

        if (empty($products)) {
            echo "Brak produktów.\n";
            return;
        }

        $headers = [
            "LP", "ID", "Nazwa", "SKU", "Kategoria", "Ilość", "Min", "Jedn.",
            "Cena", "Waga", "Objętość (m^3)", "Lokacje?", "Utworzono"
        ];
        $rows = [];

        $i = 1;
        foreach ($products as $p) {
            $rows[] = [
                $i++,
                $p->id,
                $p->name,
                $p->sku,
                $p->category,
                $p->quantity,
                $p->minQuantity,
                $p->unit,
                number_format($p->price, 2),
                number_format($p->weight, 2),
                number_format($p->volume, 2),
                $p->useLocations ? "TAK" : "NIE",
                $p->createdAt
            ];
        }

        TableRenderer::render($headers, $rows);
    }

    public function add(): void {

      $p = new Product();
      $name = InputHelper::readString("Nazwa (Enter by anulować akcję): ", true);
      if ($name === null){ 
        return; 
      }
      $p->name = $name;
      $p->sku = InputHelper::readString("SKU: ");
      $p->category = InputHelper::readString("Kategoria: ");
      $p->quantity = InputHelper::readInt("Ilość: ");
      $p->price = InputHelper::readFloat("Cena: ");
      $p->minQuantity = InputHelper::readInt("Minimalna ilość: ");
      $p->unit = InputHelper::readString("Jednostka: ");
      $p->description = InputHelper::readString("Opis (ENTER = brak): ", true) ?: null;
      $p->weight = InputHelper::readFloat("Waga (kg): ");
      $p->volume = InputHelper::readFloat("Objętość (m3): ");
      $p->createdAt = date("Y-m-d H:i:s");
      $p->updatedAt = date("Y-m-d H:i:s");

      if ($this->productService->addProduct($p)) {
        echo "Produkt dodany.\n";
      } else {
        echo "Błąd podczas dodawania produktu.\n";
      }
    }

    public function edit(): void {
      $this->list();

      $id = InputHelper::readInt("Podaj ID produktu do edycji (enter by anulować akcję): ", true);

      if ($id === null){
        echo "Powrót do menu";
        return;
      } 

      $product = $this->productService->getProductById($id);

      if (!$product) {
        echo "Produkt o ID $id nie istnieje.\n";
        return;
      }

      $name = InputHelper::readString("Nowa nazwa ({$product->name}): ", true);
      if ($name === null) $name = $product->name;

      $sku = InputHelper::readString("Nowe SKU ({$product->sku}): ", true);
      if ($sku === null) $sku = $product->sku;

      $category = InputHelper::readString("Nowa kategoria ({$product->category}): ", true);
      if ($category === null) $category = $product->category;

      $quantityInput = InputHelper::readInt("Nowa ilość ({$product->quantity}): ", true);
      $quantity = $quantityInput === null ? $product->quantity : (int)$quantityInput;

      $price = InputHelper::readFloat("Nowa cena ({$product->price}): ", true);
      if ($price === null) $price = $product->price;

      $description = InputHelper::readString("Nowy opis ({$product->description}): ", true);
      if ($description === null) $description = $product->description;

      $unit = InputHelper::readString("Nowa jednostka ({$product->unit}): ", true);
      if ($unit === null) $unit = $product->unit;

      $minInput = InputHelper::readInt("Nowy minimalny stan ({$product->minQuantity}): ", true);
      $minQuantity = $minInput === null ? $product->minQuantity : (int)$minInput;

      $useLoc = InputHelper::readString("Używać lokalizacji? ({$product->useLocations}) 1/0: ", true);
      if ($useLoc !== "") $product->useLocations = (bool)$useLoc;

      $weight = InputHelper::readString("Waga ({$product->weight}): ", true);
      if ($weight !== "") $product->weight = (float)$weight;

      $volume = InputHelper::readString("Objętość ({$product->volume}): ", true);
      if ($volume !== "") $product->volume = (float)$volume;

      $product->updatedAt = date('Y-m-d H:i:s');

      if ($this->productService->updateProduct($product)) {
        echo "Produkt zaktualizowany.\n";
      } else {
        echo "Błąd podczas aktualizacji.\n";
      }
    }

    public function delete(): void {
      $this->list();
      $id = InputHelper::readInt("Podaj ID produktu do usunięcia (enter by anulować akcję): ", true);

      if ($id === null || $id === "") {
        echo "Powrót do menu";
        return;
      }

      $product = $this->productService->getProductById($id);

      if (!$product) {
        echo "Produkt o ID $id nie istnieje. \n";
        return;
      }

      $confirm = strtolower(InputHelper::readString("Czy na pewno chcesz usunąć '{$product->name}'? (t/N): ", true)) ?? "n";

      if (strtolower($confirm) === "t") {
        $this->productService->deleteProduct($id);
        echo "Produkt usunięty.\n";
      } else {
        echo "Anulowano.\n";
      }
    }

    public function reportValue(): void {
      $data = $this->productService->getWarehouseValueRaport();

      $headers = ["Nazwa", "Ilość", "Cena", "Wartość", "Udział %"];
      $rows = [];

      foreach ($data["items"] as $item) {
        $rows[] = [
          $item["name"],
          $item["quantity"],
          number_format($item["price"],2) . " zł",
          number_format($item["value"],2) . " zł",
          number_format($item["percent"],2) ." %",
        ];
      }

      echo "\n=== RAPORT WARTOŚCI MAGAZYNU ===\n";
      TableRenderer::render($headers, $rows);

      echo "\nŁączna wartość magazynu: ".number_format($data['total'], 2) ." zł\n";
    }

}

?>