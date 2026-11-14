<?php
// Configuration des tests d'intégration : copier en config.test.php pour l'adapter (jamais commité)
// Base dédiée, vidée et recréée par tests/create_test_db.php : son nom doit finir par _test.
return [
    'db' => [
        'host' => '127.0.0.1',
        'port' => 3307,
        'name' => 'cafthe_test',
        'user' => 'root', // le script de création a besoin du droit CREATE DATABASE
        'pass' => '',
    ],
    // Schéma complet + jeu de données de l'API
    'schema_file' => dirname(__DIR__, 2) . '/Cafthe-API/database/cafthe.sql',
];
