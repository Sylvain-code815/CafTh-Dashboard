<?php
// Chargement automatique des classes : App\Models\Product -> src/Models/Product.php
// Utilisé par public/index.php et par les tests (l'application n'a pas besoin de Composer)
spl_autoload_register(function (string $class) {
    $prefix = 'App\\';
    if (str_starts_with($class, $prefix)) {
        $file = __DIR__ . '/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        if (is_file($file)) {
            require $file;
        }
    }
});
