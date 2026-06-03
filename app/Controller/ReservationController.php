<?php

require_once __DIR__ . '/../view/TableRenderer.php';
require_once __DIR__ .'/../helpers/InputHelper.php';
require_once __DIR__ .'/../Services/ProductService.php';
require_once __DIR__ .'/../Services/ReservationService.php';


class ReservationController {

    private ReservationService $service;
    private ProductService $productService;

    public function __construct() {
        $this->service = new ReservationService();
        $this->productService = new ProductService();
    }

    public function list(): void {
        $reservations = $this->service->getAllReservations();

        if (empty($reservations)) {
            echo "Brak rezerwacji.\n";
            return;
        }

        $headers = ["Lp", "ID Rez.", "ID Prod.", "Ilość", "Data"];
        $rows = [];

        $i = 1;
        foreach ($reservations as $r) {
            $rows[] = [
                $i++,
                $r->id,
                $r->productId,
                $r->quantity,
                $r->createdAt
            ];
        }

        TableRenderer::render($headers, $rows);
    }

    public function add(): void {

      $this->productService->getAllProducts();

      $productId = InputHelper::readInt("Podaj ID produktu: ");
      $quantity = InputHelper::readInt("Podaj ilość do rezerwacji: ");

      $service = new ReservationService();

      if ($service->createReservation($productId, $quantity)) {
        echo "Rezerwacja utworzona! \n";
      } else {
        echo "Nie można dokonać rezerwacji - za mało produktu. \n";
      }
    }

    public function cancel(): void {
      $this->service->getAllReservations();

      $id = InputHelper::readInt("Podaj ID rezerwacji do anulowania: ");

      $service = new ReservationService();

      if ($service->cancelReservation($id)) {
        echo "Rezerwacja anulowana\n";
      } else {
        echo "Rezerwacja nie istnieje\n";
      }
    }
}

?>
