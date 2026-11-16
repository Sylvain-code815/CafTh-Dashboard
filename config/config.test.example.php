<?php
// Configuration des tests d'intégration : le nom de la base doit finir par _test
return [
    'db' => [
        'host' => '127.0.0.1',
        'port' => 3307,
        'name' => 'cafthe_test',
        'user' => 'root',
        'pass' => '',
    ],
    // Schéma et jeu de données de l'API
    'schema_file' => dirname(__DIR__, 2) . '/Cafthe-API/database/cafthe.sql',
];
