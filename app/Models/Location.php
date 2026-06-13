<?php

  class Location {
    public int $id;
    public string $code;
    public ?string $description;
    public string $type;
    public string $status;
    public ?int $maxUnits;
    public ?float $maxWeight;
    public ?float $maxVolume;
    public string $createdAt;

  }

?>