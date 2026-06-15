<?php

class MenuRenderer {

    public static function mainMenu(): void {
      echo "\n=== MENU GŁÓWNE ===\n";
      echo "1. Magazyn\n";
      echo "2. Rezerwacje\n";
      echo "3. Zamówienia klientów\n";
      echo "4. Lokalizacje magazynowe\n";
      echo "0. Wyjście\n";
    }

    public static function warehouseMenu(): void {
      echo "\n=== MAGAZYN ===\n";
      echo "1. Dodaj produkt\n";
      echo "2. Wyświetl produkty\n";
      echo "3. Edytuj produkt\n";
      echo "4. Usuń produkt\n";
      echo "5. Raport wartości magazynu\n";
      echo "0. Powrót\n";
    }

    public static function reservationMenu(): void {
      echo "\n=== REZERWACJE ===\n";
      echo "1. Dodaj rezerwację\n";
      echo "2. Wyświetl rezerwacje\n";
      echo "3. Anuluj rezerwację\n";
      echo "0. Powrót\n";
    }

    public static function orderMenu(): void {
      echo "\n=== ZAMÓWIENIA ===\n";
      echo "1. Utwórz zamówienie\n";
      echo "2. Dodaj pozycję do zamówienia\n";
      echo "3. Wyświetl zamówienia\n";
      echo "4. Wyświetl szczegóły zamówienia\n";
      echo "5. Edytuj szczegóły zamówienia\n";
      echo "6. Usuń pozycję zamówienia\n";
      echo "7. Finalizuj zamówienie\n";
      echo "8. Anuluj zamówienie\n";
      echo "0. Powrót\n";
    }

    public static function locationsMenu(): void {
      echo "\n=== LOKALIZACJE MAGAZYNOWE ===\n";
      echo "1. Lista lokalizacji\n";
      echo "2. Dodaj lokalizację\n";
      echo "3. Edytuj lokalizację\n";
      echo "4. Usuń lokalizację\n";
      echo "5. Dodaj produkt do lokalizacji\n";
      echo "6. Przesuń produkt między lokacjami\n";
      echo "7. Pokaż rozmieszczenie produktu\n";
      echo "8. Pokaż zawartość lokacji\n";
      echo "0. Powrót\n";
    }
}
