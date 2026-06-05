<?php

require_once __DIR__ . '/../view/TableRenderer.php';
require_once __DIR__ .'/../Models/Product.php';
require_once __DIR__ .'/../helpers/InputHelper.php';

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

        $headers = ["Lp", "ID", "Nazwa", "Cena", "SKU", "Kategoria", "Ilość", "Min", "Jedn.", "Utworzono", "Zaktualizowano"];
        $rows = [];

        $i = 1;
        foreach ($products as $p) {
            $rows[] = [
                $i++,
                $p->id,
                $p->name,
                number_format($p->price,2)." zł",
                $p->sku,
                $p->category,
                $p->quantity,
                $p->minQuantity,
                $p->unit,
                $p->createdAt,
                $p->updatedAt,
            ];
        }

        TableRenderer::render($headers, $rows);
    }

    public function add(): void {
        
      $name = InputHelper::readString("Podaj nazwę Produktu: ");
      if($name === null) return;

      $sku = InputHelper::readString("Podaj SKU Produktu: ");
      if($sku === null) return;

      $category = InputHelper::readString("Podaj kategorię Produktu: ");
      if($category === null) return;

      $price = InputHelper::readFloat("Podaj cenę Produktu: ");
      if($price === null) return;

      $description = InputHelper::readString("Podaj opis Produktu (opcjonalnie)", true);

      $unit = InputHelper::readString("Podaj Jednostkę (szt/kg/l): ");
      if($unit === null) return;

      $quantity = InputHelper::readInt("Ilość początkowa: ");
      if($quantity === null) return;

      $minQuantity = InputHelper::readInt("Minimalny stan magazynowy: ");
      if($minQuantity === null) return;


      $now = date('Y-m-d H:i:s');

      $product = new Product(
        null,
        $name,
        $quantity,
        $price,
        $sku,
        $category,
        $description,
        $unit,
        $minQuantity,
        $now,
        $now
      );

      $this->productService->addProduct($product);

      echo "Produkt dodany!\n";
    }

    public function edit(): void {
      $this->list();

      $id = InputHelper::readInt("Podaj ID produktu do edycji: ", true);

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

      $price = InputHelper::readFloat("Nowa cena ({$product->price}): ", true);
      if ($price === null) $price = $product->price;

      $description = InputHelper::readString("Nowy opis ({$product->description}): ", true);
      if ($description === null) $description = $product->description;

      $unit = InputHelper::readString("Nowa jednostka ({$product->unit}): ", true);
      if ($unit === null) $unit = $product->unit;

      $quantityInput = InputHelper::readInt("Nowa ilość ({$product->quantity}): ", true);
      $quantity = $quantityInput === null ? $product->quantity : (int)$quantityInput;

      $minInput = InputHelper::readInt("Nowy minimalny stan ({$product->minQuantity}): ", true);
      $minQuantity = $minInput === null ? $product->minQuantity : (int)$minInput;

      $updatedAt = date('Y-m-d H:i:s');

      $updated = new Product(
        $id,
        $name,
        $quantity,
        $price,
        $sku,
        $category,
        $description,
        $unit,
        $minQuantity,
        $product->createdAt,
        $updatedAt
      );

      $this->productService->updateProduct($updated);
      echo "Produkt zaktualizowany!\n";
    }

    public function delete(): void {
      $this->list();
      echo "Podaj ID produktu do usunięcia: ";
      $id = (int) trim(fgets(STDIN));

      $product = $this->productService->getProductById($id);

      if (!$product) {
        echo "Produkt o ID $id nie istnieje. \n";
        return;
      }

      echo "Czy na pewno chcesz usunąć '{$product->name}'? (t/n)";
      $confirm = trim(fgets(STDIN));

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