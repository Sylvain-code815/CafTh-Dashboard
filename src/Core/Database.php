<?php
namespace App\Core;

use PDO;

// Connexion unique à MySQL, partagée par tous les modèles
class Database
{
    private static array $config;
    private static ?PDO $pdo = null;

    public static function init(array $config): void
    {
        self::$config = $config;
    }

    public static function get(): PDO
    {
        if (self::$pdo === null) {
            $c = self::$config;
            $dsn = "mysql:host={$c['host']};port={$c['port']};dbname={$c['name']};charset=utf8mb4";
            self::$pdo = new PDO($dsn, $c['user'], $c['pass'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // erreur SQL = exception
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // lignes = tableaux associatifs
                PDO::ATTR_EMULATE_PREPARES   => false,                  // vraies requêtes préparées
            ]);
        }
        return self::$pdo;
    }
}