<?php

class Order {
  public ?int $id;
  public string $customerName;
  public string $status;
  public string $createdAt;
  public string $updatedAt;

  public function __construct(
    ?int $id,
    string $customerName,
    string $status,
    string $createdAt,
    string $updatedAt
  ) {
    $this->id = $id;
    $this->customerName = $customerName;
    $this->status = $status;
    $this->createdAt = $createdAt;
    $this->updatedAt = $updatedAt;
  }
}


?>