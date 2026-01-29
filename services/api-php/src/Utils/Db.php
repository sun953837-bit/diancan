<?php

namespace App\Utils;

use PDO;
use PDOException;

class Db
{
    private static ?PDO $pdo = null;

    /**
     * 获取 PDO 连接（单例）
     */
    public static function conn(): PDO
    {
        if (self::$pdo === null) {
            $host = $_ENV['MYSQL_HOST'] ?? 'mysql';
            $port = $_ENV['MYSQL_PORT'] ?? '3306';
            $db = $_ENV['MYSQL_DATABASE'] ?? 'diancan';
            $user = $_ENV['MYSQL_USER'] ?? 'diancan';
            $pass = $_ENV['MYSQL_PASSWORD'] ?? 'diancan';
            $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
            try {
                self::$pdo = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                ]);
            } catch (PDOException $e) {
                die('DB Connection failed: ' . $e->getMessage());
            }
        }
        return self::$pdo;
    }
}
