<?php

require_once __DIR__ ."/../Database/Database.php";
require_once __DIR__ ."/../Models/ProductLocation.php";

class ProductLocationService {

    public function getLocationsForProduct(int $productId): array
    {
        $db = Database::getConnection();
        $query = $db->prepare("
            SELECT * FROM product_locations
            WHERE product_id = :pid
            ORDER BY location_id ASC
        ");
        $query->execute([':pid' => $productId]);

        $list = [];
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $pl = new ProductLocation();
            $pl->id = $row['id'];
            $pl->productId = $row['product_id'];
            $pl->locationId = $row['location_id'];
            $pl->quantity = $row['quantity'];
            $pl->updatedAt = $row['updated_at'];
            $list[] = $pl;
        }

        return $list;
    }

    public function getTotalQuantity(int $productId): int
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT SUM(quantity) AS total
            FROM product_locations
            WHERE product_id = :pid
        ");
        $stmt->execute([':pid' => $productId]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['total'] ?? 0);
    }

    public function addToLocation(Product $product, Location $loc, int $qty): bool{

    var_dump($product->useLocations);


      if (!$product->useLocations) {
        echo "Ten produkt nie korzysta z lokalizacji.\n";
        return false;
      }

      if (!$this->canAddToLocation($product, $loc, $qty)) {
        return false;
      }
    
        $db = Database::getConnection();

        // Czy istnieje wpis?
        $query = $db->prepare("
            SELECT id, quantity FROM product_locations
            WHERE product_id = :pid AND location_id = :lid
        ");
        $query->execute([':pid' => $product->id, ':lid' => $loc->id]);

        $row = $query->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            // Aktualizacja istniejącej lokalizacji
            $newQty = $row['quantity'] + $qty;

            $update = $db->prepare("
                UPDATE product_locations
                SET quantity = :q, updated_at = CURRENT_TIMESTAMP
                WHERE id = :id
            ");

            return $update->execute([
                ':q' => $newQty,
                ':id' => $row['id']
            ]);
        }

        // Tworzenie nowego wpisu
        $insert = $db->prepare("
            INSERT INTO product_locations (product_id, location_id, quantity)
            VALUES (:pid, :lid, :q)
        ");

        return $insert->execute([
            ':pid' => $product->id,
            ':lid' => $loc->id,
            ':q' => $qty
        ]);
    }

    public function removeFromLocation(Product $product, Location $loc, int $qty): bool
    {
        $db = Database::getConnection();

        $query = $db->prepare("
            SELECT id, quantity FROM product_locations
            WHERE product_id = :pid AND location_id = :lid
        ");
        $query->execute([':pid' => $product->id, ':lid' => $loc->id]);

        $row = $query->fetch(PDO::FETCH_ASSOC);
        if (!$row || $row['quantity'] < $qty) {
            return false;
        }

        if ($row['quantity'] < $qty) {
            return false; // brak wystarczającej ilości
        }

        $newQty = $row['quantity'] - $qty;

        if ($newQty === 0) {
            // Usuń wpis jeśli ilość spadła do 0
            $delete = $db->prepare("DELETE FROM product_locations WHERE id = :id");
            return $delete->execute([':id' => $row['id']]);
        }

        // Aktualizacja
        $update = $db->prepare("
            UPDATE product_locations
            SET quantity = :q, updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ");

        return $update->execute([
            ':q' => $newQty,
            ':id' => $row['id']
        ]);
    }

    public function move(Product $product, Location $fromLoc, Location $toLoc, int $qty): bool{

        $db = Database::getConnection();
        $db->beginTransaction();

        try {

        $stmt = $db->prepare("
            SELECT quantity 
            FROM product_locations 
            WHERE product_id = :p AND location_id = :l
        ");
        $stmt->execute([':p' => $product->id, ':l' => $fromLoc->id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row || $row['quantity'] < $qty) {
            $db->rollBack();
            return false;
        }

        if (!$this->canAddToLocation($product, $toLoc, $qty)) {
            $db->rollBack();
            return false;
        }

        $newQtyFrom = $row['quantity'] - $qty;

        if ($newQtyFrom === 0) {
            $stmt = $db->prepare("
                DELETE FROM product_locations 
                WHERE product_id = :p AND location_id = :l
            ");
            $stmt->execute([':p' => $product->id, ':l' => $fromLoc->id]);
        } else {
            $stmt = $db->prepare("
                UPDATE product_locations 
                SET quantity = :q 
                WHERE product_id = :p AND location_id = :l
            ");
            $stmt->execute([
                ':q' => $newQtyFrom,
                ':p' => $product->id,
                ':l' => $fromLoc->id
            ]);
        }

        $stmt = $db->prepare("
            SELECT id, quantity 
            FROM product_locations 
            WHERE product_id = :p AND location_id = :l
        ");
        $stmt->execute([':p' => $product->id, ':l' => $toLoc->id]);
        $rowTo = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($rowTo) {
            $stmt = $db->prepare("
                UPDATE product_locations 
                SET quantity = :q 
                WHERE id = :id
            ");
            $stmt->execute([
                ':q' => $rowTo['quantity'] + $qty,
                ':id' => $rowTo['id']
            ]);
        } else {
            $stmt = $db->prepare("
                INSERT INTO product_locations (product_id, location_id, quantity)
                VALUES (:p, :l, :q)
            ");
            $stmt->execute([
                ':p' => $product->id,
                ':l' => $toLoc->id,
                ':q' => $qty
            ]);
        }

        $db->commit();
        return true;

    } catch (Exception $e) {
        $db->rollBack();
        return false;
    }
    }

  public function getLocationUsage(int $locationId): array{
    $db = Database::getConnection();

    $stmt = $db->prepare("
      SELECT pl.quantity, p.weight, p.volume
      FROM product_locations pl
      JOIN products p ON p.id = pl.product_id
      WHERE pl.location_id = :loc
    ");
    
    $stmt->execute([':loc' => $locationId]);

    $totalUnits = 0;
    $totalWeight = 0.0;
    $totalVolume = 0.0;

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $w = $row['weight'] ?? 0.0;
      $v = $row['volume'] ?? 0.0;
      $qty = (int)$row['quantity'];
      $totalUnits += $qty;
      $totalWeight += $qty * (float)$w;
      $totalVolume += $qty * (float)$v;
    }

    return [
      'units' => $totalUnits,
      'weight' => $totalWeight,
      'volume' => $totalVolume
    ];
  }

  public function getFreeCapacity(Location $loc, array $usage): array{
    return [
        'units' => $loc->maxUnits ? $loc->maxUnits - $usage['units'] : null,
        'weight' => $loc->maxWeight ? $loc->maxWeight - $usage['weight'] : null,
        'volume' => $loc->maxVolume ? $loc->maxVolume - $usage['volume'] : null
    ];
  }

  public function canAddToLocation(Product $product, Location $loc, int $qty): bool{
    $usage = $this->getLocationUsage($loc->id);

    // sztuki
    if ($loc->maxUnits !== null) {
        if ($usage['units'] + $qty > $loc->maxUnits) {
            return false;
        }
    }

    // waga
    if ($loc->maxWeight !== null) {
        if ($usage['weight'] + ($product->weight * $qty) > $loc->maxWeight) {
            return false;
        }
    }

    // objętość
    if ($loc->maxVolume !== null) {
        if ($usage['volume'] + ($product->volume * $qty) > $loc->maxVolume) {
            return false;
        }
    }

    return true;
  }



}
?>