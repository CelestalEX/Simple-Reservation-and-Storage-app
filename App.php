<?php

require_once __DIR__ ."/./app/helpers/InputHelper.php";
require_once __DIR__ ."/./app/Controller/ProductController.php";
require_once __DIR__ ."/./app/Controller/ReservationController.php";
require_once __DIR__ ."/./app/Controller/OrderController.php";
require_once __DIR__ ."/./app/Controller/LocationController.php";
require_once __DIR__ ."/./app/view/MenuRenderer.php";

class App {

    private ProductController $productController;
    private ReservationController $reservationController;
    private OrderController $orderController;
    private LocationController $locationController;

    public function __construct() {
        $this->productController = new ProductController();
        $this->reservationController = new ReservationController();
        $this->orderController = new OrderController();
        $this->locationController = new LocationController();
    }

    public function run(): void {
        while (true) {
            MenuRenderer::mainMenu();
            $choice = InputHelper::readInt("\nWybierz opcję: ", true);

            switch ($choice) {
                case 1:
                    $this->warehouseMenu();
                    break;

                case 2:
                    $this->reservationMenu();
                    break;

                case 3:
                    $this->orderMenu();
                    break;

                case 4:
                    $this->locationsMenu();
                    break;

                case 0:
                    echo "Zamykanie aplikacji...\n";
                    exit;
            }
        }
    }

    private function warehouseMenu(): void {
        while (true) {
            MenuRenderer::warehouseMenu();
            $choice = InputHelper::readInt("\nWybierz opcję: ", true);

            switch ($choice) {
                case 1: $this->productController->add(); break;
                case 2: $this->productController->list(); break;
                case 3: $this->productController->edit(); break;
                case 4: $this->productController->delete(); break;
                case 5: $this->productController->reportValue(); break;
                case 0: return;
            }
        }
    }

    private function reservationMenu(): void {
        while (true) {
            MenuRenderer::reservationMenu();
            $choice = InputHelper::readInt("\nWybierz opcję: ");

            switch ($choice) {
                case 1: $this->reservationController->add(); break;
                case 2: $this->reservationController->list(); break;
                case 3: $this->reservationController->cancel(); break;
                case 0: return;
            }
        }
    }

    private function orderMenu(): void {
        while (true) {
            MenuRenderer::orderMenu();
            $choice = InputHelper::readInt("\nWybierz opcję: ");

            switch ($choice) {
              case 1: $this->orderController->create(); break;
              case 2: $this->orderController->addItem(); break;
              case 3: $this->orderController->list(); break;
              case 4: $this->orderController->details(); break;
              case 5: $this->orderController->editItem(); break;
              case 6: $this->orderController->removeItem(); break;
              case 7: $this->orderController->finalize(); break;
              case 8: $this->orderController->cancel(); break;
              case 0: return;
            }
        }
      }

    private function locationsMenu(): void {
      while (true) {
        MenuRenderer::locationsMenu();
        $choice = InputHelper::readInt("\nWybierz opcję: ");

        switch ($choice) {
          case 1: $this->locationController->listLocations(); break;
          case 2: $this->locationController->createLocation(); break;
          case 3: $this->locationController->editLocation(); break;
          case 4: $this->locationController->deleteLocation(); break;
          case 5: $this->locationController->addProductToLocation(); break;
          case 6: $this->locationController->moveProduct(); break;
          case 7: $this->locationController->showProductLocations(); break;
          case 8: $this->locationController->showLocationContents(); break;
          case 0: return;
        }
      };
    }
}
?>
