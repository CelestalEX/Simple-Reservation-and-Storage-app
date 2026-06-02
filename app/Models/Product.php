<?php

    class Product {
        public ?int $id;
        public string $name;
        public int $quantity;

        public function __construct(?int $id, string $name, int $quantity) {
            $this->id = $id;
            $this->name = $name;
            $this->quantity = $quantity;
        }
    }

?>