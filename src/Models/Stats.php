<?php
namespace App\Models;

use App\Core\Model;

// Indicateurs du tableau de bord (CDC 5.6) : les calculs sont faits par MySQL
class Stats extends Model
{
    // Chiffre d'affaires du jour, de la semaine, du mois et de l'année
    public function revenue(): array
    {
        return $this->db->query(
            "SELECT
                COALESCE(SUM(CASE WHEN DATE(date_commande) = CURDATE() THEN total END), 0) AS jour,
                COALESCE(SUM(CASE WHEN YEARWEEK(date_commande, 1) = YEARWEEK(CURDATE(), 1) THEN total END), 0) AS semaine,
                COALESCE(SUM(CASE WHEN YEAR(date_commande) = YEAR(CURDATE())
                                   AND MONTH(date_commande) = MONTH(CURDATE()) THEN total END), 0) AS mois,
                COALESCE(SUM(CASE WHEN YEAR(date_commande) = YEAR(CURDATE()) THEN total END), 0) AS annee
             FROM commande"
        )->fetch();
    }

    // Nombre de ventes et panier moyen
    public function salesSummary(): array
    {
        return $this->db->query(
            'SELECT COUNT(*) AS nombre_ventes, COALESCE(ROUND(AVG(total), 2), 0) AS panier_moyen FROM commande'
        )->fetch();
    }

    // Top 10 des produits les plus vendus
    public function topProducts(): array
    {
        return $this->db->query(
            'SELECT p.nom_produit, p.categorie, SUM(l.quantite) AS quantite_vendue,
                    SUM(l.quantite * l.prix_unitaire_achete) AS chiffre_affaires
             FROM ligne_commande l
             JOIN produit p ON p.code_produit = l.code_produit
             GROUP BY p.code_produit, p.nom_produit, p.categorie
             ORDER BY quantite_vendue DESC
             LIMIT 10'
        )->fetchAll();
    }

    // Répartition des ventes par catégorie
    public function salesByCategory(): array
    {
        return $this->db->query(
            'SELECT p.categorie, SUM(l.quantite) AS quantite_vendue,
                    SUM(l.quantite * l.prix_unitaire_achete) AS chiffre_affaires
             FROM ligne_commande l
             JOIN produit p ON p.code_produit = l.code_produit
             GROUP BY p.categorie
             ORDER BY chiffre_affaires DESC'
        )->fetchAll();
    }

    // Nouveaux clients par mois sur les 12 derniers mois
    public function newClientsByMonth(): array
    {
        return $this->db->query(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') AS mois, COUNT(*) AS nouveaux_clients
             FROM client
             WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
             GROUP BY DATE_FORMAT(created_at, '%Y-%m')
             ORDER BY mois"
        )->fetchAll();
    }
}