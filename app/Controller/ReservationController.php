<?php

require_once __DIR__ . '/../view/TableRenderer.php';
require_once __DIR__ .'/../helpers/InputHelper.php';
require_once __DIR__ .'/../helpers/IdSelectorHelper.php';
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

    $headers = ["Lp", "ID Rez.", "Produkt", "Ilość", "Data"];
    $rows = [];

    $i = 1;

    foreach ($reservations as $r) {

      $product = $this->productService->getProductById($r->productId);
      $productName = $product ? $product->name : "Nieznany produkt";

      $rows[] = [
        $i++,
        $r->id,
        $productName,
        $r->quantity,
        $r->createdAt
      ];
    }

    TableRenderer::render($headers, $rows);
  }

  public function add(): void {

    IdSelectorHelper::showProducts();

    $productId = InputHelper::readInt("Podaj ID produktu (entre by anulować akcję): ", true);

    if ($productId === null || $productId === "") {
        echo "Powrót do menu";
        return;
      }

    $quantity = InputHelper::readInt("Podaj ilość do rezerwacji: ");

    $service = new ReservationService();

    if ($service->createReservation($productId, $quantity)) {
      echo "Rezerwacja utworzona! \n";
    } else {
      echo "Nie można dokonać rezerwacji - za mało produktu. \n";
    }
  }

    public function cancel(): void {

      IdSelectorHelper::showReservations();

      $id = InputHelper::readInt("Podaj ID rezerwacji do anulowania (enter by anulować akcję): ", true);

      if ($id === null || $id === "") {
        echo "Powrót do menu";
        return;
      }

      $confirm = strtolower(InputHelper::readString("Czy napewno anulować rezerwację ID $id? (t/N)")) ?: "n";

      if ($confirm !== "t") {
        echo "Anulowane operację.\n";
        return; 
      }
    

      if ($this->service->cancelReservation($id)) {
        echo "Rezerwacja anulowana\n";
      }
    }
}

?>
