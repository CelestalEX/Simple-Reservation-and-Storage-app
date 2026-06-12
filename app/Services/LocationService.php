<?php

require_once __DIR__. "/../Database/Database.php";
require_once __DIR__."/../Models/Location.php";

  class LocationService {

    public function getAll(): array
    {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT * FROM locations ORDER BY code ASC");

        $locations = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $loc = new Location();
            $loc->id = $row['id'];
            $loc->code = $row['code'];
            $loc->description = $row['description'];
            $loc->createdAt = $row['created_at'];
            $locations[] = $loc;
        }

        return $locations;
    }

    public function getById(int $id): ?Location
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM locations WHERE id = :id");
        $stmt->execute([':id' => $id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }

        $loc = new Location();
        $loc->id = $row['id'];
        $loc->code = $row['code'];
        $loc->description = $row['description'];
        $loc->createdAt = $row['created_at'];

        return $loc;
    }

    public function create(string $code, ?string $description = null): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO locations (code, description)
            VALUES (:code, :description)
        ");

        return $stmt->execute([
            ':code' => $code,
            ':description' => $description
        ]);
    }
  }
?>