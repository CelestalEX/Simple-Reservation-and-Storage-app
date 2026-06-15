<?php

require_once __DIR__."/../view/MenuRenderer.php";
require_once __DIR__."/../view/TableRenderer.php";
require_once __DIR__."/../helpers/InputHelper.php";
require_once __DIR__."/../Services/LocationService.php";
require_once __DIR__."/../Services/ProductLocationService.php";
require_once __DIR__."/../Services/ProductService.php";
require_once __DIR__."/../helpers/IdSelectorHelper.php";

class LocationController
{
    private LocationService $locationService;
    private ProductLocationService $productLocationService;
    private ProductService $productService;

    public function __construct()
    {
        $this->locationService = new LocationService();
        $this->productLocationService = new ProductLocationService();
        $this->productService = new ProductService();
    }

  public function listLocations(): void{
    $locations = $this->locationService->getAll();

    echo "\n=== LISTA LOKALIZACJI ===\n";

    if (empty($locations)) {
        echo "Brak lokalizacji.\n";
        return;
    }

    $headers = [
        "Kod", "Typ", "Status",
        "Zajęte szt", "Max szt", "Wolne szt",
        "Zajęte kg", "Max kg", "Wolne kg",
        "Zajęte m3", "Max m3", "Wolne m3"
    ];

    $rows = [];

    foreach ($locations as $loc) {
        $usage = $this->productLocationService->getLocationUsage($loc->id);
        $free = $this->productLocationService->getFreeCapacity($loc, $usage);

        $rows[] = [
            $loc->code,
            $loc->type,
            $loc->status,

            $usage['units'],
            $loc->maxUnits ?? "-",
            $free['units'] ?? "-",

            number_format($usage['weight'], 2),
            $loc->maxWeight ?? "-",
            $free['weight'] ?? "-",

            number_format($usage['volume'], 3),
            $loc->maxVolume ?? "-",
            $free['volume'] ?? "-"
        ];
    }

    TableRenderer::render($headers, $rows);
  }

  public function showLocationContents(): void{
    echo "\n=== ZAWARTOŚĆ LOKALIZACJI ===\n";
    
    IdSelectorHelper::showLocations();
    $locId = InputHelper::readInt("ID lokalizacji (Enter by anulować akcję): ", true);

    if ($locId === null) {
      echo "Powrót do menu\n";
      return;
    }

    $loc = $this->locationService->getById($locId);

    if (!$loc) {
      echo "Lokalizacja nie istnieje.\n";
      return;
    }

    $stmt = Database::getConnection()->prepare("
      SELECT p.name, pl.quantity, p.weight, p.volume
      FROM product_locations pl
      JOIN products p ON p.id = pl.product_id
      WHERE pl.location_id = :loc
    ");
    $stmt->execute([':loc' => $locId]);

    $headers = ["Produkt", "Ilość", "Waga/szt", "Objętość/szt"];
    $rows = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $rows[] = [
        $row['name'],
        $row['quantity'],
        $row['weight'],
        $row['volume']
      ];
    }

    if (empty($rows)) {
      echo "Lokalizacja jest pusta.\n";
      return;
    }

    TableRenderer::render($headers, $rows);
  }


    public function createLocation(): void{
      echo "\n=== DODAWANIE LOKALIZACJI ===\n";

      $code = InputHelper::readString("Kod lokalizacji (np. A1-R2-P3)(Enter by anulować akcję): ", true);
      
      if ($code === null) {
        echo "Powrót do menu\n";
        return;
      }

      $desc = InputHelper::readString("Opis (opcjonalnie): ", true);

      $types = ['pick','putaway','bulk','returns','damaged','expired'];
      echo "Dostępne typy: " . implode(", ", $types) . "\n";
      $type = InputHelper::readString("Typ lokalizacji: ");

      if (!in_array($type, $types)) {
        echo "Niepoprawny typ lokalizacji.\n";
        return;
      } 

      $statuses = ['active','blocked','inventory','disabled'];
      echo "Dostępne statusy: " . implode(", ", $statuses) . "\n";
      $status = InputHelper::readString("Status lokalizacji: ");

      if (!in_array($status, $statuses)) {
          echo "Niepoprawny status.\n";
          return;
      }

      $maxUnits = InputHelper::readInt("Maksymalna liczba sztuk (ENTER = brak limitu): ", true);
      $maxUnits = $maxUnits === null ? null : $maxUnits;
      $maxWeight = InputHelper::readFloat("Maksymalna waga (kg) (ENTER = brak limitu): ", true);
      $maxWeight = $maxWeight === null ? null : $maxWeight;
      $maxVolume = InputHelper::readFloat("Maksymalna objętość (m3) (ENTER = brak limitu): ", true);
      $maxVolume = $maxVolume === null ? null : $maxVolume;


      $loc = new Location();
      $loc->code = $code;
      $loc->description = $desc;
      $loc->type = $type;
      $loc->status = $status;
      $loc->maxUnits = $maxUnits;
      $loc->maxWeight = $maxWeight;
      $loc->maxVolume = $maxVolume;

      if ($this->locationService->create($loc)) {
        echo "Lokalizacja dodana.\n";
      } else {
        echo "Błąd podczas dodawania lokalizacji.\n";
      }
    }

    public function editLocation(): void{
      echo "\n=== EDYCJA LOKJALIZACJI ===\n";

      IdSelectorHelper::showLocations();
      $id = InputHelper::readInt("Podaj ID lokalizacji (Enter by anulować akcję): ", true);

      if ($id === null) {
        echo "Powrót do menu\n";
        return;
      }

      $loc = $this->locationService->getById($id);

      if (!$loc) {
        echo "Lokalizacja nie istnieje.\n";
        return;
      }

      echo "\nAktualny kod: {$loc->code}\n";
      $codeInput = InputHelper::readString("Nowy kod (Enter = bez zmian): ", true);
      if ($codeInput === "" || $codeInput === null) {
        $code = $loc->code;
      } else {
        $code = $codeInput;
      }

      echo "\nAktualny opis: ".($loc->description ?: "-")."\n";
      $descInput = InputHelper::readString("Nowy opis (Enter = bez zmian, '-' = usuń opis): ", true);
      if ($descInput === "" || $descInput === null) {
        $desc = $loc->description;
      } elseif ($descInput === "-") {
        $desc = null;
      } else {
        $desc = $descInput;
      }

      $types = ['pick','putaway','bulk','returns','damaged','expired'];
      echo "\nDostępne typy: " . implode(", ", $types) . "\n";
      echo "Aktualny typ: {$loc->type}\n";
      $typeInput = InputHelper::readString("Nowy typ (ENTER = bez zmian): ", true);

      if ($typeInput === null || $typeInput === "") {
        $type = $loc->type;
      } else {
        if (!in_array($typeInput, ['pick','putaway','bulk','returns','damaged','expired'])) {
          echo "Niepoprawny typ.\n";
          return;
        }
        $type = $typeInput;
      }

      $statuses = ['active','blocked','inventory','disabled'];
      echo "\nDostępne statusy: " . implode(", ", $statuses) . "\n";
      echo "Aktualny status: {$loc->status}\n";
      $statusInput = InputHelper::readString("Nowy status (ENTER = bez zmian): ", true);

      if ($statusInput === null || $statusInput === "") {
        $status = $loc->status;
      } else {
        if (!in_array($statusInput, $statuses)) {
          echo "Niepoprawny status.\n";
          return;
        }
        $status = $statusInput;
      }

      echo "\nAktualna pojemność (sztuki): " . ($loc->maxUnits ?? "-") . "\n";
      $unitsInput = InputHelper::readString("Nowa pojemność (ENTER = bez zmian, '-' = usuń): ", true);

      if ($unitsInput === null || $unitsInput === "") {
        $maxUnits = $loc->maxUnits;
      } elseif ($unitsInput === "-") {
        $maxUnits = null;
      } else {
        $maxUnits = (int)$unitsInput;
      }

      echo "\nAktualna pojemność (objętość): " . ($loc->maxVolume ?? "-") . "\n";
      $volumeInput = InputHelper::readString("Nowa pojemność (ENTER = bez zmian, '-' = usuń): ", true);

      if ($volumeInput === null || $volumeInput === "") {
        $maxVolume = $loc->maxVolume;
      } elseif ($volumeInput === "-") {
        $maxVolume = null;
      } else {
        $maxVolume = (int)$volumeInput;
      }

      echo "\nAktualna pojemność (waga): " . ($loc->maxWeight ?? "-") . "\n";
      $weightInput = InputHelper::readString("Nowa pojemność (ENTER = bez zmian, '-' = usuń): ", true);

      if ($weightInput === null || $weightInput === "") {
        $maxWeight = $loc->maxWeight;
      } elseif ($weightInput === "-") {
        $maxWeight = null;
      } else {
        $maxWeight = (int)$weightInput;
      }

      $loc->code = $code;
      $loc->description = $desc;
      $loc->type = $type;
      $loc->status = $status;
      $loc->maxUnits = $maxUnits;
      $loc->maxWeight = $maxWeight;
      $loc->maxVolume = $maxVolume;

      if ($this->locationService->update($loc)) {
        echo "Lokalizacja zaktualizowana.\n";
      } else {
        echo "Błąd podczas aktualizacji.\n";
      }
    }

    public function deleteLocation(): void{
      echo "\n=== USUWANIE LOKALIZACJI ===\n";

      IdSelectorHelper::showLocations();

      $id = InputHelper::readInt("Podaj ID lokalizacji (Enter by anulować akcję): ", true);

      if ($id === null) {
        echo "Powrót do menu\n";
        return;
      }

      $loc = $this->locationService->getById($id);

      if (!$loc) {
        echo "Lokalizacja nie istnieje.\n";
        return;
      }

      echo "Usuwasz lokalizację: {$loc->code}\n";
      $confirm = strtolower(InputHelper::readString("Potwierdzić (t/N): ", true) ?: "n");
      if ($confirm !== "t") {
        echo "Anulowano.\n";
        return;
      }

      if ($this->locationService->delete($id)) {
        echo "Lokalizacja usunięta.\n";
      } else {
        echo "Błąd podczas usuwania.\n";
      }

    }

    public function addProductToLocation(): void {
      echo "\n=== DODAWANIE PRODUKTU DO LOKALIZACJI ===\n";

      IdSelectorHelper::showProducts();
      $productId = InputHelper::readInt("ID produktu (Enter by anulować akcję): ", true);

      if ($productId === null) {
        echo "Powrót do menu\n";
        return;
      }

      $product = $this->productService->getProductById($productId);

      if (!$product) {
        echo "Produkt nie istnieje.\n";
        return;
      }

      IdSelectorHelper::showLocations();
      $locationId = InputHelper::readInt("ID lokalizacji: ");
      $loc = $this->locationService->getById($locationId);

      if (!$loc) {
        echo "Lokalizacja nie istnieje.\n";
        return;
      }

      $qty = InputHelper::readInt("Ilość do dodania: ");

      if ($this->productLocationService->addToLocation($product, $loc, $qty)) {
        echo "Dodano $qty szt. produktu do lokalizacji {$loc->code}.\n";
      } else {
        echo "Błąd podczas dodawania.\n";
      }
    }


    public function showProductLocations(): void{
      echo "\n=== ROZMIESZCZENIE PRODUKTU ===\n";

      IdSelectorHelper::showProducts();
      $productId = InputHelper::readInt("ID produktu (Enter by anulować akcję): ", true);

      if ($productId === null) {
        echo "Powrót do menu\n";
        return;
      }

      $product = $this->productService->getProductById($productId);

      if (!$product) {
        echo "Produkt nie istnieje.\n";
        return;
      }

      $locations = $this->productLocationService->getLocationsForProduct($productId);

      if (empty($locations)) {
        echo "Produkt nie znajduje się w żadnej lokalizacji.\n";
        return;
      }

      $headers = ["Lokalizacja", "Typ", "Status", "Ilość"];
      $rows = [];

      foreach ($locations as $pl) {
        $loc = $this->locationService->getById($pl->locationId);
        $rows[] = [
          $loc->code,
          $loc->type,
          $loc->status,
          $pl->quantity
        ];
      }

      TableRenderer::render($headers, $rows);
    }

    public function moveProduct(): void{
        echo "\n=== PRZESUNIĘCIE PRODUKTU ===\n";

        IdSelectorHelper::showProducts();
        $productId = InputHelper::readInt("Podaj ID produktu (Enter by anulować akcję): ", true);

        if ($productId === null) {
          echo "Powrót do menu\n";
          return;
        }

        $product = $this->productService->getProductById($productId);

        if (!$product) {
            echo "Produkt nie istnieje.\n";
            return;
        }

        IdSelectorHelper::showLocations();
        $fromId = InputHelper::readInt("ID lokalizacji źródłowej: ");
        $toId = InputHelper::readInt("ID lokalizacji docelowej: ");
        $qty = InputHelper::readInt("Ilość do przesunięcia: ");

        $from = $this->locationService->getById($fromId);
        $to = $this->locationService->getById($toId);

        $confirm = strtolower(
            InputHelper::readString("Potwierdzić przesunięcie $qty szt.? (t/N): ", true)
        ) ?: "n";

        if ($confirm !== "t") {
            echo "Anulowano.\n";
            return;
        }

        if ($this->productLocationService->move($product, $from, $to, $qty)) {
            echo "Przesunięcie wykonane.\n";
        } else {
            echo "Błąd: brak ilości lub niepoprawne lokalizacje.\n";
        }
    }
  }

?>