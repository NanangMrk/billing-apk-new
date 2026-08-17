<?php
// app/Services/Database.php - Database Wrapper

require_once __DIR__ . '/../../config/database.php';

class Database {
    public static function connect(): PDO {
        return getDbConnection();
    }
}
