<?php

class OrderItem {
  public ?int $id;
  public int $orderId;
  public int $productId;
  public int $quantity;
  public float $price;
  public float $total;

  public function __construct(
    ?int $id,
    int $orderId,
    int $productId,
    int $quantity,
    float $price
  ) {
    $this->id = $id;
    $this->orderId = $orderId;
    $this->productId = $productId;
    $this->quantity = $quantity;
    $this->price = $price;
    $this->total = $quantity * $price;
  }
}


?>