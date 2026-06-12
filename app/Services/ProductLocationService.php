<?php

class ProductLocationService {

    public function getLocationsForProduct(int $productId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT * FROM product_locations
            WHERE product_id = :pid
            ORDER BY location_id ASC
        ");
        $stmt->execute([':pid' => $productId]);

        $list = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
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

    public function addToLocation(int $productId, int $locationId, int $qty): bool
    {
        $db = Database::getConnection();

        // Czy istnieje wpis?
        $stmt = $db->prepare("
            SELECT id, quantity FROM product_locations
            WHERE product_id = :pid AND location_id = :lid
        ");
        $stmt->execute([':pid' => $productId, ':lid' => $locationId]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

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
            ':pid' => $productId,
            ':lid' => $locationId,
            ':q' => $qty
        ]);
    }

    public function removeFromLocation(int $productId, int $locationId, int $qty): bool
    {
        $db = Database::getConnection();

        $stmt = $db->prepare("
            SELECT id, quantity FROM product_locations
            WHERE product_id = :pid AND location_id = :lid
        ");
        $stmt->execute([':pid' => $productId, ':lid' => $locationId]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
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

    public function move(int $productId, int $fromLoc, int $toLoc, int $qty): bool
    {
        $db = Database::getConnection();
        $db->beginTransaction();

        try {
            if (!$this->removeFromLocation($productId, $fromLoc, $qty)) {
                $db->rollBack();
                return false;
            }

            if (!$this->addToLocation($productId, $toLoc, $qty)) {
                $db->rollBack();
                return false;
            }

            $db->commit();
            return true;

        } catch (Exception $e) {
            $db->rollBack();
            return false;
        }
    }
}
?>