<?php
// Crée (ou recrée) la base de test à partir de database/cafthe.sql de l'API.
// Le nom de base « cafthe » du fichier est remplacé par celui de la configuration de test.

$config = require __DIR__ . '/config.php';
$db = $config['db'];
$schemaFile = $argv[1] ?? $config['schema_file'];

if (!is_file($schemaFile)) {
    fwrite(STDERR, "Fichier introuvable : $schemaFile\n");
    exit(1);
}

// DROP DATABASE / CREATE DATABASE / USE cafthe -> base de test
$sql = preg_replace(
    '/^(DROP DATABASE IF EXISTS|CREATE DATABASE|USE)\s+cafthe\b/mi',
    '$1 `' . $db['name'] . '`',
    file_get_contents($schemaFile),
    -1,
    $count
);
if ($count !== 3) {
    fwrite(STDERR, "Le fichier ne contient pas les lignes DROP / CREATE / USE attendues\n");
    exit(1);
}

$pdo = new PDO(
    "mysql:host={$db['host']};port={$db['port']};charset=utf8mb4",
    $db['user'],
    $db['pass'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

// Une requête à la fois : chaque erreur est signalée avec la requête en cause
$statements = preg_split('/;\s*$/m', $sql);
foreach ($statements as $statement) {
    // Lignes de commentaire retirées pour savoir s'il reste une vraie requête
    $code = trim(preg_replace('/^\s*--.*$/m', '', $statement));
    if ($code !== '') {
        $pdo->exec($code);
    }
}

$products = $pdo->query("SELECT COUNT(*) FROM `{$db['name']}`.produit")->fetchColumn();
echo "Base {$db['name']} créée ($products produits).\n";
