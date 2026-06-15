<?php

class Product {
    public ?int $id = null;
    public string $name;
    public int $quantity;
    public string $sku;
    public string $category;
    public ?string $description = null;
    public string $unit;
    public int $minQuantity = 0;
    public float $price = 0.0;
    public bool $useLocations = true;
    public float $weight = 0.0;
    public float $volume = 0.0;
    public string $createdAt;
    public string $updatedAt;
}


?>