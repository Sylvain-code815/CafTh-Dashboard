<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Product;

class ProductController extends Controller
{
    public function index(): void
    {
        $search = trim($_GET['q'] ?? '');
        $category = $_GET['categorie'] ?? '';
        $withInactive = isset($_GET['inactifs']);

        $this->render('products/index', [
            'title'        => 'Produits',
            'products'     => (new Product())->search($search, $category, $withInactive),
            'search'       => $search,
            'category'     => $category,
            'withInactive' => $withInactive,
        ]);
    }

    public function create(): void
    {
        $this->form(null);
    }

    public function edit(): void
    {
        $product = (new Product())->find($this->idParam());
        if (!$product) {
            flash('Produit introuvable', 'error');
            redirect('products');
        }
        $this->form($product);
    }

    // Formulaire commun à l'ajout et à la modification
    private function form(?array $product): void
    {
        $data = $product ?? [
            'nom_produit' => '', 'description' => '', 'categorie' => 'Thé', 'type_vente' => 'Boite',
            'prix_HT' => '', 'stock' => 0, 'image' => '', 'origine' => '',
            'produit_phare' => 0, 'nouveaute' => 0, 'produit_promotion' => 0, 'taux_remise' => 0,
        ];
        $errors = [];

        if ($this->isPost()) {
            check_csrf();
            [$data, $errors] = $this->validate($_POST);

            if (!$errors) {
                $model = new Product();
                if ($product) {
                    $model->update((int) $product['code_produit'], $data);
                    flash('Produit modifié');
                } else {
                    $model->create($data);
                    flash('Produit ajouté');
                }
                redirect('products');
            }
        }

        $this->render('products/form', [
            'title'   => $product ? 'Modifier un produit' : 'Ajouter un produit',
            'product' => $product,
            'data'    => $data,
            'errors'  => $errors,
        ]);
    }

    public function toggle(): void
    {
        if ($this->isPost()) {
            check_csrf();
            (new Product())->toggleActive($this->idParam());
            flash('Statut du produit mis à jour');
        }
        redirect('products');
    }

    // Vérifie chaque champ côté serveur (le HTML « required » se contourne facilement)
    private function validate(array $in): array
    {
        $d = [
            'nom_produit'       => trim($in['nom_produit'] ?? ''),
            'description'       => trim($in['description'] ?? ''),
            'categorie'         => $in['categorie'] ?? '',
            'type_vente'        => $in['type_vente'] ?? '',
            'prix_HT'           => str_replace(',', '.', $in['prix_HT'] ?? ''),
            'stock'             => $in['stock'] ?? '',
            'image'             => trim($in['image'] ?? ''),
            'origine'           => trim($in['origine'] ?? ''),
            'produit_phare'     => isset($in['produit_phare']) ? 1 : 0,
            'nouveaute'         => isset($in['nouveaute']) ? 1 : 0,
            'produit_promotion' => isset($in['produit_promotion']) ? 1 : 0,
            'taux_remise'       => $in['taux_remise'] ?? 0,
        ];
        $errors = [];

        if ($d['nom_produit'] === '' || mb_strlen($d['nom_produit']) > 50) {
            $errors['nom_produit'] = 'Nom obligatoire (50 caractères maximum)';
        }
        if ($d['description'] === '' || mb_strlen($d['description']) > 255) {
            $errors['description'] = 'Description obligatoire (255 caractères maximum)';
        }
        if (!array_key_exists($d['categorie'], Product::VAT_BY_CATEGORY)) {
            $errors['categorie'] = 'Catégorie invalide';
        }
        if (!in_array($d['type_vente'], [...Product::UNIT_TYPES, ...Product::WEIGHT_TYPES], true)) {
            $errors['type_vente'] = 'Type de vente invalide';
        }
        if (!is_numeric($d['prix_HT']) || $d['prix_HT'] < 0) {
            $errors['prix_HT'] = 'Prix HT invalide';
        }
        if (filter_var($d['stock'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]) === false) {
            $errors['stock'] = 'Le stock doit être un entier positif';
        }
        if (filter_var($d['taux_remise'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 90]]) === false) {
            $errors['taux_remise'] = 'Remise entre 0 et 90 %';
        }
        if ($d['image'] === '') {
            $d['image'] = 'produit-the-boite.webp';
        }

        return [$d, $errors];
    }
}