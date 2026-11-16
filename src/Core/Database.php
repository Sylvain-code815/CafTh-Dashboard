<?php
namespace App\Core;

use PDO;

// Connexion unique à MySQL, partagée par tous les modèles
class Database
{
    public const OPTIONS = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // erreur SQL = exception
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // lignes = tableaux associatifs
        PDO::ATTR_EMULATE_PREPARES   => false,                  // vraies requêtes préparées
    ];

    private static array $config;
    private static ?PDO $pdo = null;

    public static function init(array $config): void
    {
        self::$config = $config;
    }

    public static function dsn(array $c): string
    {
        return "mysql:host={$c['host']};port={$c['port']};dbname={$c['name']};charset=utf8mb4";
    }

    public static function get(): PDO
    {
        if (self::$pdo === null) {
            $c = self::$config;
            self::$pdo = new PDO(self::dsn($c), $c['user'], $c['pass'], self::OPTIONS);
        }
        return self::$pdo;
    }

    // Connexion à la base de test, dans une transaction annulée après chaque test
    public static function setConnection(PDO $pdo): void
    {
        self::$pdo = $pdo;
    }
}
