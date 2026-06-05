<?php

require_once __DIR__ . "/../helpers/InputHelper.php";
require_once __DIR__ ."/../Services/OrderService.php";
require_once __DIR__ ."/../helpers/IdSelectorHelper.php";
require_once __DIR__ ."/../Services/ProductService.php";

class OrderController{

  private OrderService $orderService;

  private ProductService $productService;

  public function __construct(){
    $this->orderService = new OrderService();
    $this->productService = new ProductService();
  }

  public function create(): void{
    $customer = InputHelper::readString("Nazwa klienta: ");
    $orderId = $this->orderService->createOrder($customer);

    echo "Utworzono zamówienie ID: $orderId\n";
  }

  public function addItem(): void{
    IdSelectorHelper::showOrders();
    $orderId = InputHelper::readInt("ID zamówienia: ");
    IdSelectorHelper::showProducts();
    $productId = InputHelper::readInt("ID produktu: ");
    $quantity = InputHelper::readInt("Ilość: ");

    if($this->orderService->addItem($orderId, $productId, $quantity)){
      echo "Dodano pozycję.\n";
    } else {
      echo "Nie można dodać pozycji - za mało towaru.\n";
    }
  }

  public function list(): void {
    $orders = $this->orderService->getAllOrders();

    $headers = ["ID", "Klient", "Status", "Utworzono"];
    $rows = [];

    foreach ($orders as $o) {
        $rows[] = [
            $o->id,
            $o->customerName,
            $o->status,
            $o->createdAt
        ];
    }

    TableRenderer::render($headers, $rows);
  }

  public function details(): void {
    IdSelectorHelper::showOrders();
    $id = InputHelper::readInt("ID zamówienia: ");
    $items = $this->orderService->getOrderItems($id);

    $headers = ["Produkt", "Ilość", "Cena", "Razem"];
    $rows = [];

    foreach ($items as $i) {

        if ($i->productId === null) {
          $name = "Nieznany produkt (ID = NULL)";
        } else {
          $product = $this->productService->getProductById($i->productId);
          $name = $product ? $product->name : "Nieznany produkt (ID = {$i->productId})";
        }

        $rows[] = [
            $name,
            $i->quantity,
            number_format($i->price,2)." zł",
            number_format($i->total,2)." zł"
        ];
    }

    TableRenderer::render($headers, $rows);
  }

  public function finalize(): void {
    IdSelectorHelper::showOrders(); 
    $id = InputHelper::readInt("ID zamówienia: ");

    if ($this->orderService->finalizeOrder($id)) {
        echo "Zamówienie zrealizowane.\n";
    }
  }

  public function cancel(): void {
    IdSelectorHelper::showOrders();
    $id = InputHelper::readInt("ID zamówienia: ");

    if ($this->orderService->cancelOrder($id)) {
        echo "Zamówienie anulowane.\n";
    }
  }
}

?>