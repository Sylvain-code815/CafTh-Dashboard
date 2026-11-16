<?php
return [
    'db' => [
        'host' => '127.0.0.1',
        'port' => 3307,
        'name' => 'cafthe',
        'user' => 'cafthe_api',
        'pass' => 'CHANGE_ME',
    ],
    // Déconnexion automatique après 30 minutes d'inactivité
    'session_timeout' => 1800,
    // Adresse publique du dashboard
    'app_url' => 'https://dashboard-cafthe.slaurent.dev-campus.fr',
    // Envoi des emails : 'mail' ou 'log'
    'mail' => [
        'from'      => 'no-reply@slaurent.dev-campus.fr',
        'transport' => 'mail',
    ],
];