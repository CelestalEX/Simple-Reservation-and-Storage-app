<?php

class Database {
    private static ?PDO $connection = null;

    public static function getConnection(): PDO {
        if (self::$connection === null) {
            self::$connection = new PDO('sqlite:' . __DIR__ . '/app.db');
            self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }

        self::$connection->exec('PRAGMA foreign_keys = ON');

        return self::$connection;
    }
}
