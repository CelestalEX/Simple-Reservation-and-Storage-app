# System Magazynowo-Zamówieniowy (PHP CLI)

Aplikacja konsolowa służąca do zarządzania produktami, rezerwacjami oraz zamówieniami.

## Funkcjonalność

- Dodawanie, Edycja, Wyświetlanie lub usuwanie (CRUD) produktów
- Tworzenie rezerwacji
- Tworzenie i zarządzanie zamówieniami

app/  \
 ├── Controller/   → Logika wejścia użytkownika (CLI)  \  
 ├── Services/     → Logika biznesowa i operacje na danych  \
 ├── Models/       → Struktury danych  \
 ├── Helpers/      → Funkcje pomocnicze  \
 ├── Database/     → Inicjalizacja i połączenie z SQLite  \
 └── Views/        → warstwa prezentacji  \


## Uruchamianie

Uruchom aplikację poprzez:
``` php index.php ```