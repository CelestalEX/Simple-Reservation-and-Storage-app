<?php

    require_once __DIR__ . '/app/Services/ProductService.php';
    require_once __DIR__ . '/app/Models/Product.php';

class App {

    private ProductService $productService;

    public function __construct() {
        $this->productService = new ProductService();
    }

    public function run(): void {
        while (true) {
            $this->printMenu();
            echo "Wybierz opcję: ";
            // fgets nie wyświetla komunikatu: Cannot read termcap database; using dumb terminal settings.
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
                    echo "Zamykanie aplikacji...\n";
                    exit;

                default:
                    echo "Niepoprawna opcja.\n";
            }
        }
    }

    private function printMenu(): void {
        echo "\n=== MENU MAGAZYNU ===\n";
        echo "1. Dodaj produkt\n";
        echo "2. Wyświetl produkty\n";
        echo "3. Edytuj produkt\n";
        echo "4. Usuń produkt\n";
        echo "5. Wyjście\n";
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

        foreach ($products as $p) {
            echo "{$p->id}. {$p->name} — ilość: {$p->quantity}\n";
        }
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
        var_dump($updated);
        $this->productService->updateProduct($updated);
        echo "Produkt zaktualizowany!\n";
    }

    private function deleteProduct(): void {
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
