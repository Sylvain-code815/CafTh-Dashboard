<?php
// Copier ce fichier en config.php et le remplir (config.php n'est jamais commité)
return [
    'db' => [
        'host' => '127.0.0.1',
        'port' => 3307,
        'name' => 'cafthe',
        'user' => 'cafthe_api',
        'pass' => 'CHANGE_ME',
    ],
    // Déconnexion automatique après 30 minutes d'inactivité (CDC 5.1)
    'session_timeout' => 1800,
    // Adresse publique du dashboard : sert à construire le lien envoyé par email (mot de passe oublié)
    'app_url' => 'https://dashboard-cafthe.slaurent.dev-campus.fr',
    // Envoi des emails : 'mail' = fonction mail() de PHP (serveur Plesk),
    // 'log' = écrit les emails dans storage/mails.log sans les envoyer (développement)
    'mail' => [
        'from'      => 'no-reply@slaurent.dev-campus.fr',
        'transport' => 'mail',
    ],
];