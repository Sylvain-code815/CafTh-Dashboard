<?php
namespace App\Core;

// Classe mère des contrôleurs : affiche une vue dans le gabarit commun
abstract class Controller
{
    protected function render(string $view, array $data = []): void
    {
        extract($data); // ['products' => ...] devient la variable $products dans la vue
        ob_start();
        require ROOT . "/views/$view.php";
        $content = ob_get_clean();
        require ROOT . '/views/layout.php';
    }

    // Identifiant numérique passé dans l'URL (?id=3), 0 s'il est absent ou invalide
    protected function idParam(): int
    {
        return (int) ($_GET['id'] ?? 0);
    }

    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }
}