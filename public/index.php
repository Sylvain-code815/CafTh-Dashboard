<?php
// Point d'entrée unique (front controller) : toutes les pages passent par ce fichier

use App\Core\Auth;
use App\Core\Database;

define('ROOT', dirname(__DIR__));

require ROOT . '/src/helpers.php';

// Chargement automatique des classes : App\Models\Product -> src/Models/Product.php
spl_autoload_register(function (string $class) {
    $prefix = 'App\\';
    if (str_starts_with($class, $prefix)) {
        $file = ROOT . '/src/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        if (is_file($file)) {
            require $file;
        }
    }
});

$config = require ROOT . '/config/config.php';
Database::init($config['db']);

// Cookie de session protégé (inaccessible au JavaScript, non envoyé depuis un autre site)
session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Strict',
    'secure'   => !empty($_SERVER['HTTPS']),
]);
session_start();
Auth::checkTimeout($config['session_timeout']);

// Dashboard non indexé par les moteurs de recherche (CDC 6.2)
header('X-Robots-Tag: noindex, nofollow');

// Table de routage : route => [contrôleur, méthode, accès]
// accès : guest = sans connexion, staff = vendeur ou admin, admin = admin seulement
$routes = [
    'login'            => ['AuthController', 'login', 'guest'],
    'logout'           => ['AuthController', 'logout', 'staff'],
    'dashboard'        => ['DashboardController', 'index', 'staff'],
    'products'         => ['ProductController', 'index', 'staff'],
    'products/create'  => ['ProductController', 'create', 'staff'],
    'products/edit'    => ['ProductController', 'edit', 'staff'],
    'products/toggle'  => ['ProductController', 'toggle', 'staff'],
    'orders'           => ['OrderController', 'index', 'staff'],
    'orders/show'      => ['OrderController', 'show', 'staff'],
    'orders/status'    => ['OrderController', 'nextStatus', 'staff'],
    'clients'          => ['ClientController', 'index', 'staff'],
    'clients/create'   => ['ClientController', 'create', 'staff'],
    'clients/show'     => ['ClientController', 'show', 'staff'],
    'sales/create'     => ['SaleController', 'create', 'staff'],
    'employees'        => ['EmployeeController', 'index', 'admin'],
    'employees/create' => ['EmployeeController', 'create', 'admin'],
    'employees/edit'   => ['EmployeeController', 'edit', 'admin'],
    'employees/delete' => ['EmployeeController', 'delete', 'admin'],
    'profile'          => ['ProfileController', 'index', 'staff'],
];

$route = $_GET['route'] ?? (Auth::user() ? 'dashboard' : 'login');

if (!isset($routes[$route])) {
    http_response_code(404);
    exit('Page introuvable');
}

[$controllerName, $method, $access] = $routes[$route];

if ($access !== 'guest' && !Auth::user()) {
    redirect('login');
}
if ($access === 'admin' && !Auth::isAdmin()) {
    http_response_code(403);
    exit("Accès réservé à l'administrateur");
}

$class = 'App\\Controllers\\' . $controllerName;
(new $class())->$method();