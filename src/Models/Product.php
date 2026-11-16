<?php
namespace App\Models;

use App\Core\Model;

class Product extends Model
{
    // Catégorie => taux de TVA (5,5 % thé/café, 20 % accessoires)
    public const VAT_BY_CATEGORY = ['Thé' => 5.5, 'Café' => 5.5, 'Accessoire' => 20];

    // Types de vente à l'unité ; les autres (Vrac, Grain) sont vendus au poids, prix pour 100 g
    public const UNIT_TYPES = ['Boite', 'Sachet', 'Dosette', 'Unité'];
    public const WEIGHT_TYPES = ['Vrac', 'Grain'];

    // Référence SKU : préfixe de catégorie + numéro sur 4 chiffres, ex. THE-0003
    public const SKU_PATTERN = '/^[A-Z]{3}-\d{4}$/D'; // D : refuse aussi un retour à la ligne final
    public const SKU_PREFIX_BY_CATEGORY = ['Thé' => 'THE', 'Café' => 'CAF', 'Accessoire' => 'ACC'];

    // Liste avec recherche et filtres
    public function search(string $text = '', string $category = '', bool $withInactive = false): array
    {
        $sql = 'SELECT * FROM produit WHERE 1 = 1';
        $params = [];

        if ($text !== '') {
            $sql .= ' AND (nom_produit LIKE ? OR origine LIKE ? OR sku LIKE ?)';
            array_push($params, "%$text%", "%$text%", "%$text%");
        }
        if ($category !== '') {
            $sql .= ' AND categorie = ?';
            $params[] = $category;
        }
        if (!$withInactive) {
            $sql .= ' AND active = 1';
        }

        $stmt = $this->db->prepare($sql . ' ORDER BY categorie, nom_produit');
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM produit WHERE code_produit = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    // Produits actifs et en stock, pour la vente en magasin
    public function forSale(): array
    {
        return $this->db->query(
            'SELECT * FROM produit WHERE active = 1 AND stock > 0 ORDER BY categorie, nom_produit'
        )->fetchAll();
    }

    public function create(array $d): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO produit (sku, nom_produit, description, categorie, type_vente, tva, prix_HT, prix_ttc,
                                  stock, image, origine, produit_phare, nouveaute, produit_promotion, taux_remise)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute($this->values($d));
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $d): void
    {
        $stmt = $this->db->prepare(
            'UPDATE produit SET sku = ?, nom_produit = ?, description = ?, categorie = ?, type_vente = ?, tva = ?,
                    prix_HT = ?, prix_ttc = ?, stock = ?, image = ?, origine = ?, produit_phare = ?,
                    nouveaute = ?, produit_promotion = ?, taux_remise = ?
             WHERE code_produit = ?'
        );
        $stmt->execute([...$this->values($d), $id]);
    }

    // Suppression = désactivation, pour garder l'historique des commandes
    public function toggleActive(int $id): void
    {
        $stmt = $this->db->prepare('UPDATE produit SET active = 1 - active WHERE code_produit = ?');
        $stmt->execute([$id]);
    }

    // Une référence SKU ne peut servir qu'à un seul produit ($exceptId : le produit en cours de modification)
    public function skuExists(string $sku, int $exceptId = 0): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM produit WHERE sku = ? AND code_produit <> ?');
        $stmt->execute([$sku, $exceptId]);
        return (bool) $stmt->fetchColumn();
    }

    // Référence proposée à la création : préfixe de la catégorie + numéro suivant le plus grand utilisé
    public function suggestSku(string $category): string
    {
        $prefix = self::SKU_PREFIX_BY_CATEGORY[$category] ?? 'ACC';
        $stmt = $this->db->prepare('SELECT MAX(CAST(SUBSTRING(sku, 5) AS UNSIGNED)) FROM produit WHERE sku LIKE ?');
        $stmt->execute([$prefix . '-%']);
        return sprintf('%s-%04d', $prefix, (int) $stmt->fetchColumn() + 1);
    }

    // La TVA et le prix TTC sont calculés ici, jamais saisis
    private function values(array $d): array
    {
        $vat = self::VAT_BY_CATEGORY[$d['categorie']];
        $ttc = self::priceWithVat((float) $d['prix_HT'], $d['categorie']);

        return [
            $d['sku'], $d['nom_produit'], $d['description'], $d['categorie'], $d['type_vente'], $vat,
            $d['prix_HT'], $ttc, $d['stock'], $d['image'], $d['origine'],
            $d['produit_phare'], $d['nouveaute'], $d['produit_promotion'], $d['taux_remise'],
        ];
    }

    // Prix réellement payé : remise déduite si le produit est en promotion
    public static function effectivePrice(array $p): float
    {
        $price = (float) $p['prix_ttc'];
        if ((int) $p['produit_promotion'] === 1 && (int) $p['taux_remise'] > 0) {
            $price *= 1 - $p['taux_remise'] / 100;
        }
        return round($price, 2);
    }

    // Prix TTC arrondi au centime, d'après le taux de TVA de la catégorie
    public static function priceWithVat(float $priceHt, string $category): float
    {
        return round($priceHt * (1 + self::VAT_BY_CATEGORY[$category] / 100), 2);
    }

    public static function isValidSku(string $sku): bool
    {
        return preg_match(self::SKU_PATTERN, $sku) === 1;
    }

    public static function isSoldByWeight(array $p): bool
    {
        return !in_array($p['type_vente'], self::UNIT_TYPES, true);
    }
}
