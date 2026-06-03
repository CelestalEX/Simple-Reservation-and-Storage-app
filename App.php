<?php

require_once __DIR__ ."/./app/helpers/InputHelper.php";
require_once __DIR__ ."/./app/Controller/ProductController.php";
require_once __DIR__ ."/./app/Controller/ReservationController.php";
require_once __DIR__ ."/./app/view/MenuRenderer.php";

class App {

    private ProductController $productController;
    private ReservationController $reservationController;

    public function __construct() {
        $this->productController = new ProductController();
        $this->reservationController = new ReservationController();
    }

    public function run(): void {
        while (true) {
            MenuRenderer::mainMenu();
            $choice = InputHelper::readInt("Wybierz opcję: ");

            switch ($choice) {
                case 1:
                    $this->warehouseMenu();
                    break;

                case 2:
                    $this->reservationMenu();
                    break;

                case 3:
                    echo "Zamykanie aplikacji...\n";
                    exit;
            }
        }
    }

    private function warehouseMenu(): void {
        while (true) {
            MenuRenderer::warehouseMenu();
            $choice = InputHelper::readInt("Wybierz opcję: ");

            switch ($choice) {
                case 1: $this->productController->add(); break;
                case 2: $this->productController->list(); break;
                case 3: $this->productController->edit(); break;
                case 4: $this->productController->delete(); break;
                case 5: return;
            }
        }
    }

    private function reservationMenu(): void {
        while (true) {
            MenuRenderer::reservationMenu();
            $choice = InputHelper::readInt("Wybierz opcję: ");

            switch ($choice) {
                case 1: $this->reservationController->add(); break;
                case 2: $this->reservationController->list(); break;
                case 3: $this->reservationController->cancel(); break;
                case 4: return;
            }
        }
    }
}

?>
