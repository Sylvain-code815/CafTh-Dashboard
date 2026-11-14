<?php
namespace Tests\Integration;

use App\Models\Order;
use App\Models\Stats;
use Tests\Support\IntegrationTestCase;

class StatsTest extends IntegrationTestCase
{
    public function testRevenueHasTheFourPeriods(): void
    {
        $this->assertSame(['jour', 'semaine', 'mois', 'annee'], array_keys((new Stats())->revenue()));
    }

    public function testSalesSummaryHasCountAndAverageBasket(): void
    {
        $summary = (new Stats())->salesSummary();
        $this->assertSame(['nombre_ventes', 'panier_moyen'], array_keys($summary));
        $this->assertSame((int) $this->value('SELECT COUNT(*) FROM commande'), (int) $summary['nombre_ventes']);
    }

    public function testATodaySaleIsCountedInTheDailyRevenue(): void
    {
        $stats = new Stats();
        $before = (float) $stats->revenue()['jour'];

        (new Order())->createStoreSale(1, [1 => 1], 'card'); // 15,90 €

        $this->assertEqualsWithDelta($before + 15.90, (float) $stats->revenue()['jour'], 0.001);
    }

    public function testTopProductsAndCategoriesHaveTheExpectedColumns(): void
    {
        $stats = new Stats();

        $top = $stats->topProducts();
        $this->assertNotEmpty($top);
        $this->assertLessThanOrEqual(10, count($top));
        $this->assertSame(['nom_produit', 'categorie', 'quantite_vendue', 'chiffre_affaires'], array_keys($top[0]));

        $categories = $stats->salesByCategory();
        $this->assertNotEmpty($categories);
        $this->assertSame(['categorie', 'quantite_vendue', 'chiffre_affaires'], array_keys($categories[0]));
    }

    public function testNewClientsByMonth(): void
    {
        $this->db->exec(
            "INSERT INTO client (nom_client, prenom_client, email, telephone, mdp)
             VALUES ('Test', 'Stats', 'stats.test@example.com', '0600000000', NULL)"
        );

        $months = array_column((new Stats())->newClientsByMonth(), 'nouveaux_clients', 'mois');
        $currentMonth = $this->value("SELECT DATE_FORMAT(NOW(), '%Y-%m')"); // horloge de MySQL
        $this->assertGreaterThanOrEqual(1, (int) ($months[$currentMonth] ?? 0));
    }
}
