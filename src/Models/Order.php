<?php
namespace App\Models;

use App\Core\Model;
use RuntimeException;
use Throwable;

class Order extends Model
{
    // Doublon API
    public const STATUS_FLOW = ['pending', 'preparing', 'shipped', 'delivered'];
    public const STORE_PAYMENTS = ['card', 'cash', 'check'];

    // Liste avec filtres : statut, canal (web / magasin), recherche client
    public function search(string $status = '', string $mode = '', string $client = ''): array
    {
        $sql = 'SELECT c.num_commande, c.date_commande, c.statut_commande, c.mode_commande, c.mode_paiement,
                       c.total, cl.nom_client, cl.prenom_client
                FROM commande c
                JOIN client cl ON cl.code_client = c.code_client
                WHERE 1 = 1';
        $params = [];

        if ($status !== '') {
            $sql .= ' AND c.statut_commande = ?';
            $params[] = $status;
        if ($mode !== '') {
            $sql .= ' AND c.mode_commande = ?';
            $params[] = $mode;
        }
        if ($client !== '') {
            $sql .= ' AND (cl.nom_client LIKE ? OR cl.prenom_client LIKE ? OR cl.email LIKE ?)';
            array_push($params, "%$client%", "%$client%", "%$client%");
        }

        $stmt = $this->db->prepare($sql . ' ORDER BY c.date_commande DESC');
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
             JOIN client cl ON cl.code_client = c.code_client
             LEFT JOIN adresse a ON a.id_adresse = c.id_adresse_livraison
             LEFT JOIN livraison lv ON lv.num_commande = c.num_commande
             WHERE c.num_commande = ?'
        );
        $stmt->execute([$id]);
        $order = $stmt->fetch();
        if (!$order) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT l.quantite, l.prix_unitaire_achete, p.nom_produit, p.type_vente, p.tva
             FROM ligne_commande l
             JOIN produit p ON p.code_produit = l.code_produit
             WHERE l.num_commande = ?'
        );
        $stmt->execute([$id]);
        $order['lignes'] = $stmt->fetchAll();

        return $order;
    }

    // Fait avancer la commande d'une étape : pending -> preparing -> shipped -> delivered
    public function advanceStatus(int $id): bool
    {
        $stmt = $this->db->prepare('SELECT statut_commande FROM commande WHERE num_commande = ?');
        $stmt->execute([$id]);
        $current = $stmt->fetchColumn();

        $index = array_search($current, self::STATUS_FLOW, true);
        if ($index === false || $index === count(self::STATUS_FLOW) - 1) {
            return false; // commande inconnue ou déjà livrée
        }

        $stmt = $this->db->prepare('UPDATE commande SET statut_commande = ? WHERE num_commande = ?');
        $stmt->execute([self::STATUS_FLOW[$index + 1], $id]);
        return true;
    }

    // Vente en magasin : tout est enregistré, ou rien.
    // $lines = [code_produit => quantité] (au poids : quantité = nombre de tranches de 100 g)
    public function createStoreSale(int $clientId, array $lines, string $paymentMode): int
    {
        $this->db->beginTransaction();
        try {
            // Verrouille les produits vendus : deux ventes simultanées ne peuvent pas
            // vendre le même dernier article
            $placeholders = implode(',', array_fill(0, count($lines), '?'));
            $stmt = $this->db->prepare(
                "SELECT * FROM produit WHERE code_produit IN ($placeholders) AND active = 1 FOR UPDATE"
            );
            $stmt->execute(array_keys($lines));
            $products = [];
            foreach ($stmt->fetchAll() as $p) {
                $products[$p['code_produit']] = $p;
            }

            // Total calculé avec les prix de la base (en centimes pour éviter les erreurs d'arrondi)
            $totalCents = 0;
            $prices = [];
            foreach ($lines as $productId => $quantity) {
                $product = $products[$productId] ?? null;
                if (!$product) {
                    throw new RuntimeException("Produit $productId introuvable ou désactivé");
                }
                if ($product['stock'] < $quantity) {
                    throw new RuntimeException("Stock insuffisant pour « {$product['nom_produit']} »");
                }
                $prices[$productId] = Product::effectivePrice($product);
                $totalCents += (int) round($prices[$productId] * 100) * $quantity;
            }

            // Vente en magasin : payée et remise tout de suite
            $stmt = $this->db->prepare(
                "INSERT INTO commande (code_client, statut_commande, mode_commande, date_commande,
                                       mode_paiement, total, date_paiement)
                 VALUES (?, 'delivered', 'store', NOW(), ?, ?, NOW())"
            );
            $stmt->execute([$clientId, $paymentMode, $totalCents / 100]);
            $orderId = (int) $this->db->lastInsertId();

            $insertLine = $this->db->prepare(
                'INSERT INTO ligne_commande (num_commande, code_produit, quantite, prix_unitaire_achete)
                 VALUES (?, ?, ?, ?)'
            );
            $updateStock = $this->db->prepare(
                'UPDATE produit SET stock = stock - ? WHERE code_produit = ?'
            );
            foreach ($lines as $productId => $quantity) {
                $insertLine->execute([$orderId, $productId, $quantity, $prices[$productId]]);
                $updateStock->execute([$quantity, $productId]); // stock mis à jour instantanément
            }

            $this->db->commit();
            return $orderId;
        } catch (Throwable $e) {
            $this->db->rollBack(); // annule tout ce qui a été fait depuis beginTransaction()
            throw $e;
        }
    }
}