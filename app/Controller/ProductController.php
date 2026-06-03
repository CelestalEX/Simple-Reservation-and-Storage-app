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

        $headers = ["Lp", "ID", "Nazwa", "SKU", "Kategoria", "Ilość", "Min", "Jedn.", "Utworzono", "Zaktualizowano"];
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
                $p->createdAt,
                $p->updatedAt,
            ];
        }

        TableRenderer::render($headers, $rows);
    }

    public function add(): void {
        echo "Nazwa produktu: ";
      $name = trim(fgets(STDIN));
      if($name === "") return;

      echo "SKU (unikalny kod): ";
      $sku = trim(fgets(STDIN));
      if($sku === "") return;

      echo "Kategoria: ";
      $category = trim(fgets(STDIN));
      if($category === "") return;

      echo "Opis (opcjonalnie): ";
      $description = trim(fgets(STDIN));
      if ($description === "") $description = null;

      echo "Jednostka (szt/kg/l): ";
      $unit = trim(fgets(STDIN));
      if($unit === "") return;

      $quantity = InputHelper::readInt("Ilość początkowa: ");
      $minQuantity = InputHelper::readInt("Minimalny stan magazynowy: ");

      $now = date('Y-m-d H:i:s');

      $product = new Product(
        null,
        $name,
        $quantity,
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

      $id = InputHelper::readInt("Podaj ID produktu do edycji: ");

      $product = $this->productService->getProductById($id);

      if (!$product) {
        echo "Produkt o ID $id nie istnieje.\n";
        return;
      }

      echo "Nowa nazwa ({$product->name}): ";
      $name = trim(fgets(STDIN));
      if ($name === "") $name = $product->name;

      echo "Nowe SKU ({$product->sku}): ";
      $sku = trim(fgets(STDIN));
      if ($sku === "") $sku = $product->sku;

      echo "Nowa kategoria ({$product->category}): ";
      $category = trim(fgets(STDIN));
      if ($category === "") $category = $product->category;

      echo "Nowy opis ({$product->description}): ";
      $description = trim(fgets(STDIN));
      if ($description === "") $description = $product->description;

      echo "Nowa jednostka ({$product->unit}): ";
      $unit = trim(fgets(STDIN));
      if ($unit === "") $unit = $product->unit;

      echo "Nowa ilość ({$product->quantity}): ";
      $quantityInput = trim(fgets(STDIN));
      $quantity = $quantityInput === "" ? $product->quantity : (int)$quantityInput;

      echo "Nowy minimalny stan ({$product->minQuantity}): ";
      $minInput = trim(fgets(STDIN));
      $minQuantity = $minInput === "" ? $product->minQuantity : (int)$minInput;

      $updatedAt = date('Y-m-d H:i:s');

      $updated = new Product(
        $id,
        $name,
        $quantity,
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
}

?>