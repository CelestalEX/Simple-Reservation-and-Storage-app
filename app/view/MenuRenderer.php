<?php

class MenuRenderer {

    public static function mainMenu(): void {
      echo "\n=== MENU GŁÓWNE ===\n";
      echo "1. Magazyn\n";
      echo "2. Rezerwacje\n";
      echo "3. Zamówienia klientów\n";
      echo "4. Wyjście\n";
    }

    public static function warehouseMenu(): void {
      echo "\n=== MAGAZYN ===\n";
      echo "1. Dodaj produkt\n";
      echo "2. Wyświetl produkty\n";
      echo "3. Edytuj produkt\n";
      echo "4. Usuń produkt\n";
      echo "5. Raport wartości magazynu\n";
      echo "6. Powrót\n";
    }

    public static function reservationMenu(): void {
      echo "\n=== REZERWACJE ===\n";
      echo "1. Dodaj rezerwację\n";
      echo "2. Wyświetl rezerwacje\n";
      echo "3. Anuluj rezerwację\n";
      echo "4. Powrót\n";
    }

    public static function orderMenu(): void {
      echo "\n=== ZAMÓWIENIA ===\n";
      echo "1. Utwórz zamówienie\n";
      echo "2. Dodaj pozycję do zamówienia\n";
      echo "3. Wyświetl zamówienia\n";
      echo "4. Wyświetl szczegóły zamówienia\n";
      echo "5. Finalizuj zamówienie\n";
      echo "6. Anuluj zamówienie\n";
      echo "7. Powrót\n";
    }
}
