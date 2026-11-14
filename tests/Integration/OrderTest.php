<?php
namespace Tests\Integration;

use App\Models\Order;
use RuntimeException;
use Tests\Support\IntegrationTestCase;

class OrderTest extends IntegrationTestCase
{
    private function createWebOrder(string $status): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO commande (code_client, statut_commande, mode_commande, mode_paiement, total)
             VALUES (1, ?, 'web', 'card', 20)"
        );
        $stmt->execute([$status]);
        return (int) $this->db->lastInsertId();
    }

    private function orderStatus(int $orderId): string
    {
        return $this->value('SELECT statut_commande FROM commande WHERE num_commande = ?', [$orderId]);
    }

    public function testAdvanceStatusFollowsTheFlowAndStopsAtDelivered(): void
    {
        $order = new Order();
        $id = $this->createWebOrder('pending');

        foreach (['preparing', 'shipped', 'delivered'] as $expected) {
            $this->assertTrue($order->advanceStatus($id));
            $this->assertSame($expected, $this->orderStatus($id));
        }

        $this->assertFalse($order->advanceStatus($id)); // déjà livrée
        $this->assertSame('delivered', $this->orderStatus($id));
    }

    public function testAdvanceStatusRefusesAnUnknownOrder(): void
    {
        $this->assertFalse((new Order())->advanceStatus(999999));
    }

    public function testStoreSaleSavesTheOrderAndDecrementsTheStock(): void
    {
        // Produit 1 : 15,90 € ; produit 2 : 12,50 € en promotion à -50 % (6,25 €)
        $orderId = (new Order())->createStoreSale(1, [1 => 2, 2 => 3], 'cash');

        $this->assertSame(98, (int) $this->value('SELECT stock FROM produit WHERE code_produit = 1'));
        $this->assertSame(47, (int) $this->value('SELECT stock FROM produit WHERE code_produit = 2'));

        $stmt = $this->db->prepare('SELECT * FROM commande WHERE num_commande = ?');
        $stmt->execute([$orderId]);
        $sale = $stmt->fetch();
        $this->assertSame('store', $sale['mode_commande']);
        $this->assertSame('delivered', $sale['statut_commande']);
        $this->assertSame('cash', $sale['mode_paiement']);
        $this->assertSame('50.55', $sale['total']); // 2 x 15,90 + 3 x 6,25
        $this->assertNotNull($sale['date_paiement']);

        $this->assertSame('6.25', $this->value(
            'SELECT prix_unitaire_achete FROM ligne_commande WHERE num_commande = ? AND code_produit = 2',
            [$orderId]
        ));
    }

    public function testStoreSaleWithInsufficientStockWritesNothing(): void
    {
        $orders = (int) $this->value('SELECT COUNT(*) FROM commande');
        $lines = (int) $this->value('SELECT COUNT(*) FROM ligne_commande');

        // Produit 1 en stock, produit 24 (stock 10) demandé 11 fois : toute la vente est refusée
        try {
            (new Order())->createStoreSale(1, [1 => 1, 24 => 11], 'card');
            $this->fail('Une exception était attendue');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('Stock insuffisant', $e->getMessage());
        }

        $this->assertSame($orders, (int) $this->value('SELECT COUNT(*) FROM commande'));
        $this->assertSame($lines, (int) $this->value('SELECT COUNT(*) FROM ligne_commande'));
        $this->assertSame(100, (int) $this->value('SELECT stock FROM produit WHERE code_produit = 1'));
        $this->assertSame(10, (int) $this->value('SELECT stock FROM produit WHERE code_produit = 24'));
    }

    public function testStoreSaleRefusesADisabledProduct(): void
    {
        $this->db->exec('UPDATE produit SET active = 0 WHERE code_produit = 13');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('introuvable ou désactivé');
        (new Order())->createStoreSale(1, [13 => 1], 'card');
    }
}
