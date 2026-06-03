<?php

class MenuRenderer {

    public static function mainMenu(): void {
        echo "\n=== MENU GŁÓWNE ===\n";
        echo "1. Magazyn\n";
        echo "2. Rezerwacje\n";
        echo "3. Wyjście\n";
    }

    public static function warehouseMenu(): void {
        echo "\n=== MAGAZYN ===\n";
        echo "1. Dodaj produkt\n";
        echo "2. Wyświetl produkty\n";
        echo "3. Edytuj produkt\n";
        echo "4. Usuń produkt\n";
        echo "5. Powrót\n";
    }

    public static function reservationMenu(): void {
        echo "\n=== REZERWACJE ===\n";
        echo "1. Dodaj rezerwację\n";
        echo "2. Wyświetl rezerwacje\n";
        echo "3. Anuluj rezerwację\n";
        echo "4. Powrót\n";
    }
}
