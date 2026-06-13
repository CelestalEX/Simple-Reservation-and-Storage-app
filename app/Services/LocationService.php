<?php

require_once __DIR__. "/../Database/Database.php";
require_once __DIR__."/../Models/Location.php";

  class LocationService {

    public function getAll(): array{
      $db = Database::getConnection();
      $query = $db->query("SELECT * FROM locations ORDER BY code ASC");

      $locations = [];
      while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
        $loc = new Location();
        $loc->id = $row['id'];
        $loc->code = $row['code'];
        $loc->description = $row['description'];
        $loc->type = $row['type'];
        $loc->status = $row['status'];
        $loc->maxUnits = $row['max_units'];
        $loc->maxWeight = $row['max_weight'];
        $loc->maxVolume = $row['max_volume'];
        $loc->createdAt = $row['created_at'];
        $locations[] = $loc;
      }

      return $locations;
    }

    public function getById(int $id): ?Location{
      $db = Database::getConnection();
      $query = $db->prepare("SELECT * FROM locations WHERE id = :id");
      $query->execute([':id' => $id]);

      $row = $query->fetch(PDO::FETCH_ASSOC);
      if (!$row) {
          return null;
      }

      $loc = new Location();
      $loc->id = $row['id'];
      $loc->code = $row['code'];
      $loc->description = $row['description'];
      $loc->type = $row['type']; 
      $loc->status = $row['status'];
      $loc->maxUnits = $row['max_units'];
      $loc->maxWeight = $row['max_weight'];
      $loc->maxVolume = $row['max_volume'];
      $loc->createdAt = $row['created_at'];

      return $loc;
    }

    public function create(Location $loc): bool
    {
      $db = Database::getConnection();
        $query = $db->prepare("
            INSERT INTO locations (code, description, type, status, max_units, max_weight, max_volume)
            VALUES (:code, :description, :type, :status, :max_units, :max_weight, :max_volume)
        ");

        return $query->execute([
            ':code' => $loc->code,
            ':description' => $loc->description,
            ':type' => $loc->type,
            ':status' => $loc->status,
            ':max_units'=> $loc->maxUnits,
            ':max_weight'=> $loc->maxWeight,
            ':max_volume'=> $loc->maxVolume,
        ]);
    }

    public function update(Location $loc): bool{
      $db = Database::getConnection();
      $query = $db->prepare('
      UPDATE locations
      SET code = :code,
          description = :description,
          type = :type,
          status = :status,
          max_units = :max_units,
          max_weight = :max_weight,
          max_volume = :max_volume
      WHERE id = :id
      ');

      return $query->execute([
        ':code'=> $loc->code,
        ':description'=> $loc->description,
        ':type'=> $loc->type,
        ':status'=> $loc->status,
        ':max_units' => $loc->maxUnits,
        ':max_weight' => $loc->maxWeight,
        ':max_volume' => $loc->maxVolume,
        ':id' => $loc->id,
        ]);
    }

    public function delete(int $id): bool{
      $db = Database::getConnection();
      $query = $db->prepare('DELETE FROM locations WHERE id = :id');
      return $query->execute([  
        ':id'=> $id
      ]);
    }

  }
?>