<?php
namespace App\Models;

use App\Core\Model;

class Client extends Model
{
    public function search(string $text = ''): array
    {
        $stmt = $this->db->prepare(
            'SELECT code_client, nom_client, prenom_client, email, telephone, created_at,
                    mdp IS NOT NULL AS compte_actif
             FROM client
             WHERE nom_client LIKE ? OR prenom_client LIKE ? OR email LIKE ? OR telephone LIKE ?
             ORDER BY nom_client, prenom_client'
        );
        $like = "%$text%";
        $stmt->execute([$like, $like, $like, $like]);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT code_client, nom_client, prenom_client, email, telephone, created_at,
                    mdp IS NOT NULL AS compte_actif
             FROM client WHERE code_client = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function emailExists(string $email): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM client WHERE email = ?');
        $stmt->execute([$email]);
        return (bool) $stmt->fetchColumn();
    }

    // Client créé en magasin sans mot de passe : il le choisira lors de sa première connexion sur le site
    public function create(array $d, int $employeeId): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO client (nom_client, prenom_client, email, telephone, mdp, num_employe_createur)
             VALUES (?, ?, ?, ?, NULL, ?)'
        );
        $stmt->execute([$d['nom_client'], $d['prenom_client'], $d['email'], $d['telephone'], $employeeId]);
        return (int) $this->db->lastInsertId();
    }

    // Historique unifié : achats en magasin et en ligne
    public function orders(int $id): array
    {
        $stmt = $this->db->prepare(
            'SELECT num_commande, date_commande, mode_commande, statut_commande, total
             FROM commande WHERE code_client = ? ORDER BY date_commande DESC'
        );
        $stmt->execute([$id]);
        return $stmt->fetchAll();
    }

    // Statistiques personnalisées : panier moyen, fréquence, produits favoris
    public function stats(int $id): array
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) AS nombre_commandes,
                    COALESCE(SUM(total), 0) AS total_depense,
                    COALESCE(ROUND(AVG(total), 2), 0) AS panier_moyen,
                    MIN(date_commande) AS premiere_commande,
                    MAX(date_commande) AS derniere_commande
             FROM commande WHERE code_client = ?'
        );
        $stmt->execute([$id]);
        $stats = $stmt->fetch();

        // Fréquence : nombre moyen de jours entre deux commandes
        $stats['frequence_jours'] = null;
        if ($stats['nombre_commandes'] > 1) {
            $days = (strtotime($stats['derniere_commande']) - strtotime($stats['premiere_commande'])) / 86400;
            $stats['frequence_jours'] = (int) round($days / ($stats['nombre_commandes'] - 1));
        }

        $stmt = $this->db->prepare(
            'SELECT p.nom_produit, SUM(l.quantite) AS quantite
             FROM ligne_commande l
             JOIN commande c ON c.num_commande = l.num_commande
             JOIN produit p ON p.code_produit = l.code_produit
             WHERE c.code_client = ?
             GROUP BY p.code_produit, p.nom_produit
             ORDER BY quantite DESC
             LIMIT 3'
        );
        $stmt->execute([$id]);
        $stats['favoris'] = $stmt->fetchAll();

        return $stats;
    }
}