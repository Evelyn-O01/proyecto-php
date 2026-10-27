<?php

namespace App\Infrastructure\Database;

use PDO;
use PDOException;
use RuntimeException;

class PostgresConnection
{
    private static ?PDO $instance = null;

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $config = require __DIR__ . '/../../../config/database.php';
            
            $dsn = sprintf(
                "pgsql:host=%s;port=%s;dbname=%s",
                $config['host'],
                $config['port'],
                $config['database']
            );

            try {
                self::$instance = new PDO(
                    $dsn,
                    $config['username'],
                    $config['password'],
                    $config['options']
                );
            } catch (PDOException $e) {
                throw new RuntimeException("Error al conectar a PostgreSQL: " . $e->getMessage(), (int)$e->getCode(), $e);
            }
        }

        return self::$instance;
    }

    public static function reset(): void
    {
        self::$instance = null;
    }
}
