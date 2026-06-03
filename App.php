<?php

    require_once __DIR__ . '/app/Services/ProductService.php';
    require_once __DIR__ . '/app/Models/Product.php';
    require_once __DIR__ . "/app/Services/ReservationService.php";

class App {

    private ProductService $productService;
    private ReservationService $reservationService;

    public function __construct() {
        $this->productService = new ProductService();
        $this->reservationService = new ReservationService();
    }

    public function run(): void {
        while (true) {
            $this->printMenu();
            $choice = $this->readInt("Wybierz opcję: ");       

            switch ($choice) {
                case "1":
                    $this->warehouseMenu();
                    break;

                case "2":
                    $this->reservationMenu();
                    break;

                case "3":
                    echo "Zamykanie aplikacji...\n";
                    exit;

                default:
                    echo "Niepoprawna opcja.\n";
            }
        }
    }

    private function printMenu(): void {
        echo "\n=== MENU MAGAZYNU ===\n";
        echo "1. Magazyn\n";
        echo "2. Rezerwacje\n";
        echo "3. Wyjście\n";
    }

    private function warehouseMenu (): void {
      while (true) {
        echo "\n=== MAGAZYN ===\n";
        echo "1. Dodaj produkt\n";
        echo "2. Wyświetl produkty\n";
        echo "3. Edytuj produkt\n";
        echo "4. Usuń produkt\n";
        echo "5. Powrót\n";

        echo "Wybierz opcję: ";
        $choice = trim(fgets(STDIN));

        switch ($choice) {
          case "1":
            $this->addProductOption();
            break;

          case "2":
            $this->listProducts();
            break;

          case "3":
            $this->editProduct();
            break;

          case "4":
            $this->deleteProduct();
            break;

          case "5":
            return; 

          default:
            echo "Niepoprawna opcja.\n";
        }
    }
    }

    private function reservationMenu (): void {
      while (true) {
        echo "\n=== REZERWACJE ===\n";
        echo "1. Dodaj rezerwację\n";
        echo "2. Wyświetl rezerwację\n";
        echo "3. Anuluj rezerwację\n";
        echo "4. Powrót\n";

        $choice = $this->readInt("Wybierz opcję: ");

        switch ($choice) {
          case "1":
            $this->addReservation();
            break;
            
          case "2":
            $this->listReservations();
            break;

          case "3":
            $this->cancelReservation();
            break;

          case "4":
            return;

          default:
            echo "Niepoprawna opcja\n";
      }
    }
    }

    private function printTable(array $headers, array $rows): void {

    $widths = [];
    foreach ($headers as $i => $header) {
        $widths[$i] = strlen($header);
    }

    foreach ($rows as $row) {
        foreach ($row as $i => $cell) {
            $widths[$i] = max($widths[$i], strlen((string)$cell));
        }
    }

    $drawLine = function() use ($widths) {
        echo "+";
        foreach ($widths as $w) {
            echo str_repeat("-", $w + 2) . "+";
        }
        echo "\n";
    };

    $drawLine();
    echo "|";
    foreach ($headers as $i => $header) {
        echo " " . str_pad($header, $widths[$i]) . " |";
    }
    echo "\n";
    $drawLine();

    foreach ($rows as $row) {
        echo "|";
        foreach ($row as $i => $cell) {
            echo " " . str_pad($cell, $widths[$i]) . " |";
        }
        echo "\n";
    }

    $drawLine();
    }

    private function addProductOption(): void {
        echo "Nazwa produktu: ";
        $name = trim(fgets(STDIN));
        echo "Ilość: ";
        $quantity = trim(fgets(STDIN));

        $product = new Product(null, $name, $quantity);
        $this->productService->addProduct($product);

        echo "Produkt dodany!\n";
    }

    private function listProducts(): void {
        $products = $this->productService->getAllProducts();

        echo "\n=== LISTA PRODUKTÓW ===\n";

        if (empty($products)) {
            echo "Brak produktów w bazie.\n";
            return;
        }

        $headers = ["Lp", "ID Rez.", "Nazwa.", "Ilość"];
        $rows = [];

        $index = 1;
        foreach ($products as $p) {
          $rows[] = [
            $index,
            $p->id,
            $p->name,
            $p->quantity,
          ];
          $index++;
        }

        $this->printTable($headers, $rows);
    }

    private function readInt(string $label): int {
        echo $label;
        $input = trim(fgets(STDIN));

        if (!ctype_digit($input)) {
            echo "Wartość musi być liczbą dodatnią.\n";
            return $this->readInt($label);
        }

        return (int) $input;
    }  

    private function editProduct(): void {

      $this->listProducts();

      $id = $this->readInt("Podaj ID produktu do edycji: ");

      $product = $this->productService->getProductById($id);

      if (!$product) {
        echo "Produkt o ID $id nie istnieje.\n";
        return;
      }

      echo "Nowa nazwa dla produktu: ({$product->name}) : ";
      $name = trim(fgets(STDIN));
      if ($name === ""){
        $name = $product->name;
      }

      echo "Nowa Ilość dla produktu: $name, stara ilość({$product->quantity}): ";
      $quantityInput = trim(fgets(STDIN));
      if ($quantityInput === ""){
          $quantityInput = $product->quantity;
      } 

      $updated = new Product($id, $name, $quantityInput);
      $this->productService->updateProduct($updated);
      echo "Produkt zaktualizowany!\n";
    }

    private function deleteProduct(): void {
      $this->listProducts();
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

    private function addReservation(): void {

      $this->listProducts();

      $productId = $this->readInt("Podaj ID produktu: ");
      $quantity = $this->readInt("Podaj ilość do rezerwacji: ");

      $service = new ReservationService();

      if ($service->createReservation($productId, $quantity)) {
        echo "Rezerwacja utworzona! \n";
      } else {
        echo "Nie można dokonać rezerwacji - za mało produktu. \n";
      }
    }

    


    private function listReservations(): void {
      $service = new ReservationService();
      $reservations = $service->getAllReservations();

      echo "\n=== LISTA REZERWACJI ===\n";

      if(empty($reservations)) {
        echo "Brak rezerwacji\n";
        return;
      }

      $headers = ["Lp", "ID Rez.", "ID Prod.", "Ilość", "Data"];
      $rows = []; 

      $index = 1;
      foreach ($reservations as $r) {
        $rows[] = [
          $index,
          $r->id,
          $r->productId,
          $r->quantity,
          $r->createdAt
        ];
        $index++;
      }

      $this->printTable($headers, $rows);
    }

    private function cancelReservation(): void {

      $this->listReservations();

      $id = $this->readInt("Podaj ID rezerwacji do anulowania: ");

      $service = new ReservationService();

      if ($service->cancelReservation($id)) {
        echo "Rezerwacja anulowana\n";
      } else {
        echo "Rezerwacja nie istnieje\n";
      }
    }
}
