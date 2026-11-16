<?php
// Réglages de la base de test : config/config.test.php s'il existe, sinon le modèle
// Les variables d'environnement TEST_DB_* et CAFTHE_SCHEMA_FILE remplacent les valeurs du fichier
$root = dirname(__DIR__);
$file = is_file("$root/config/config.test.php") ? "$root/config/config.test.php" : "$root/config/config.test.example.php";
$config = require $file;

foreach (['host', 'port', 'name', 'user', 'pass'] as $key) {
    $value = getenv('TEST_DB_' . strtoupper($key));
    if ($value !== false) {
        $config['db'][$key] = $value;
    }
}
if (getenv('CAFTHE_SCHEMA_FILE') !== false) {
    $config['schema_file'] = getenv('CAFTHE_SCHEMA_FILE');
}

// Sécurité : les tests vident et modifient la base, jamais celle de l'application
if (!str_ends_with($config['db']['name'], '_test')) {
    throw new RuntimeException("La base de test doit avoir un nom finissant par _test ({$config['db']['name']})");
}

return $config;
