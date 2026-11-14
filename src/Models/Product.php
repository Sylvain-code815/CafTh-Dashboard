<?php
namespace App\Models;

use App\Core\Model;

class Product extends Model
{
    // Catégorie => taux de TVA (CDC 5.3.2 : 5,5 % thé/café, 20 % accessoires)
    public const VAT_BY_CATEGORY = ['Thé' => 5.5, 'Café' => 5.5, 'Accessoire' => 20];

    // Types de vente à l'unité ; les autres (Vrac, Grain) sont vendus au poids, prix pour 100 g
    public const UNIT_TYPES = ['Boite', 'Sachet', 'Dosette', 'Unité'];
    public const WEIGHT_TYPES = ['Vrac', 'Grain'];

    // Liste avec recherche et filtres (CDC 5.3.1 « Lecture »)
    public function search(string $text = '', string $category = '', bool $withInactive = false): array
    {
        $sql = 'SELECT * FROM produit WHERE 1 = 1';
        $params = [];

        if ($text !== '') {
            $sql .= ' AND (nom_produit LIKE ? OR origine LIKE ?)';
            $params[] = "%$text%";
            $params[] = "%$text%";
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

    public function create(array $d): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO produit (nom_produit, description, categorie, type_vente, tva, prix_HT, prix_ttc,
                                  stock, image, origine, produit_phare, nouveaute, produit_promotion, taux_remise)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute($this->values($d));
    }

    public function update(int $id, array $d): void
    {
        $stmt = $this->db->prepare(
            'UPDATE produit SET nom_produit = ?, description = ?, categorie = ?, type_vente = ?, tva = ?,
                    prix_HT = ?, prix_ttc = ?, stock = ?, image = ?, origine = ?, produit_phare = ?,
                    nouveaute = ?, produit_promotion = ?, taux_remise = ?
             WHERE code_produit = ?'
        );
        $stmt->execute([...$this->values($d), $id]);
    }

    // Suppression = désactivation, pour garder l'historique des commandes (CDC 5.3.1)
    public function toggleActive(int $id): void
    {
        $stmt = $this->db->prepare('UPDATE produit SET active = 1 - active WHERE code_produit = ?');
        $stmt->execute([$id]);
    }

    // La TVA et le prix TTC sont calculés ici, jamais saisis (CDC 5.3.2 « calcul auto TTC »)
    private function values(array $d): array
    {
        $vat = self::VAT_BY_CATEGORY[$d['categorie']];
        $ttc = round($d['prix_HT'] * (1 + $vat / 100), 2);

        return [
            $d['nom_produit'], $d['description'], $d['categorie'], $d['type_vente'], $vat,
            $d['prix_HT'], $ttc, $d['stock'], $d['image'], $d['origine'],
            $d['produit_phare'], $d['nouveaute'], $d['produit_promotion'], $d['taux_remise'],
        ];
    }

    // Prix réellement payé : remise déduite si le produit est en promotion (même règle que l'API)
    public static function effectivePrice(array $p): float
    {
        $price = (float) $p['prix_ttc'];
        if ((int) $p['produit_promotion'] === 1 && (int) $p['taux_remise'] > 0) {
            $price *= 1 - $p['taux_remise'] / 100;
        }
        return round($price, 2);
    }

    public static function isSoldByWeight(array $p): bool
    {
        return !in_array($p['type_vente'], self::UNIT_TYPES, true);
    }
}
