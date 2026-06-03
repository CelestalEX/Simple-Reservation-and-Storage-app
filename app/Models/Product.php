<?php

class Product {
    public ?int $id;
    public string $name;
    public int $quantity;
    public string $sku;
    public string $category;
    public ?string $description;
    public string $unit;
    public int $minQuantity;
    public string $createdAt;
    public string $updatedAt;

    public function __construct(
        ?int $id,
        string $name,
        int $quantity,
        string $sku,
        string $category,
        ?string $description,
        string $unit,
        int $minQuantity,
        string $createdAt,
        string $updatedAt
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->quantity = $quantity;
        $this->sku = $sku;
        $this->category = $category;
        $this->description = $description;
        $this->unit = $unit;
        $this->minQuantity = $minQuantity;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }
}

?>