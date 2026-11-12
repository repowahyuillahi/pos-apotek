<?php
/**
 * Database wrapper — PDO singleton.
 * Kelas DB menyediakan method query, execute, insert, update, delete.
 */

require_once __DIR__ . '/../config/database.php';

class DB
{
    private static ?PDO $pdo = null;

    public static function connect(): PDO
    {
        if (self::$pdo === null) {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_PERSISTENT         => false,
            ];
            self::$pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        }
        return self::$pdo;
    }

    public static function query(string $sql, array $params = []): array
    {
        $stmt = self::connect()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function execute(string $sql, array $params = []): bool
    {
        $stmt = self::connect()->prepare($sql);
        return $stmt->execute($params);
    }

    public static function insert(string $table, array $data): int
    {
        $cols     = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        $sql = "INSERT INTO {$table} ({$cols}) VALUES ({$placeholders})";
        self::execute($sql, $data);
        return (int) self::connect()->lastInsertId();
    }

    public static function update(string $table, array $data, string $where, array $whereParams = []): bool
    {
        $set = [];
        foreach ($data as $col => $val) {
            $set[] = "{$col} = :{$col}";
        }
        $setStr = implode(', ', $set);
        $sql = "UPDATE {$table} SET {$setStr} WHERE {$where}";
        $params = array_merge($data, $whereParams);
        return self::execute($sql, $params);
    }

    public static function delete(string $table, string $where, array $params = []): bool
    {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        return self::execute($sql, $params);
    }

    public static function fetchOne(string $sql, array $params = []): ?array
    {
        $rows = self::query($sql, $params);
        return $rows[0] ?? null;
    }
}
