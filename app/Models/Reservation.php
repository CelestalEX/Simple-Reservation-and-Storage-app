<?php

class Reservation {

  public ?int $id;
  public int $productId;
  public int $quantity;
  public string $createdAt;

  public function __construct(int $id, int $productId, int $quantity, string $createdAt) {
    $this->id = $id;
    $this->productId = $productId;
    $this->quantity = $quantity;
    $this->createdAt = $createdAt;
  }

}

?>