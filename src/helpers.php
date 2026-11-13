<?php
// Petites fonctions utilisées partout (contrôleurs et vues)

// Échappe une valeur avant de l'afficher dans le HTML (protection XSS)
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// Construit une URL interne : url('orders/show', ['id' => 3]) -> index.php?route=orders/show&id=3
function url(string $route, array $params = []): string
{
    return 'index.php?' . http_build_query(['route' => $route] + $params);
}

function redirect(string $route, array $params = []): never
{
    header('Location: ' . url($route, $params));
    exit;
}

// Formate un montant : 1234.5 -> "1 234,50 €"
function price(mixed $value): string
{
    return number_format((float) $value, 2, ',', ' ') . ' €';
}

// Jeton CSRF : prouve que le formulaire envoyé vient bien du dashboard
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . csrf_token() . '">';
}

function check_csrf(): void
{
    if (!hash_equals(csrf_token(), $_POST['csrf'] ?? '')) {
        http_response_code(403);
        exit('Requête invalide, rechargez la page.');
    }
}

// Message affiché une seule fois sur la page suivante
function flash(string $message, string $type = 'success'): void
{
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function take_flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

// Libellés des statuts de commande (mêmes valeurs techniques que l'API)
const ORDER_STATUS = [
    'pending'   => 'En attente',
    'preparing' => 'En préparation',
    'shipped'   => 'Expédiée',
    'delivered' => 'Livrée',
];

const PAYMENT_LABELS = [
    'card'     => 'Carte bancaire',
    'cash'     => 'Espèces',
    'check'    => 'Chèque',
    'paypal'   => 'PayPal',
    'in_store' => 'Paiement au retrait',
];