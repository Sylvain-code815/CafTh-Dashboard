<?php
// Copier ce fichier en config.php et le remplir (config.php n'est jamais commité)
return [
    'db' => [
        'host' => '127.0.0.1',
        'port' => 3307,
        'name' => 'cafthe',
        'user' => 'cafthe_api',
        'pass' => 'qNQuJNDg0lX-WrjgEwOs-pkw',
    ],
    // Déconnexion automatique après 30 minutes d'inactivité (CDC 5.1)
    'session_timeout' => 1800,
];