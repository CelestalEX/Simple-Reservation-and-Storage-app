<?php

require_once __DIR__ . '/Database.php';

class DatabaseInitializer {

    public static function initialize(): void {
        $db = Database::getConnection();

        $schema = file_get_contents(__DIR__ . '/schema.sql');

        if ($schema === false) {
            throw new Exception("Nie można odczytać pliku schema.sql");
        }

        $db->exec($schema);
    }
}
