<?php
// Démarrage des tests : mêmes fonctions et même autoload que l'application (public/index.php)
define('ROOT', dirname(__DIR__));

require ROOT . '/src/helpers.php';
require ROOT . '/src/autoload.php';
require __DIR__ . '/Support/NestedTransactionPdo.php';
require __DIR__ . '/Support/IntegrationTestCase.php';
