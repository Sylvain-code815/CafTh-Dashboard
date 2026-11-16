<?php
namespace App\Core;

// Accès aux réglages de config/config.php depuis n'importe quelle classe
class Config
{
    private static array $values = [];

    public static function init(array $values): void
    {
        self::$values = $values;
    }

    // $default est renvoyé si la clé manque
    public static function get(string $key, mixed $default = null): mixed
    {
        return self::$values[$key] ?? $default;
    }
}
