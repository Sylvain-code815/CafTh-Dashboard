<?php
namespace Tests\Integration;

use App\Models\Product;
use PDOException;
use Tests\Support\IntegrationTestCase;

class ProductTest extends IntegrationTestCase
{
    private function data(array $overrides = []): array
    {
        return $overrides + [
            'sku' => 'THE-0900', 'nom_produit' => 'Thé de test', 'description' => 'Produit créé par les tests',
            'categorie' => 'Thé', 'type_vente' => 'Vrac', 'prix_HT' => '10', 'stock' => 12,
            'image' => 'produit-the-boite.webp', 'origine' => 'Japon',
            'produit_phare' => 0, 'nouveaute' => 1, 'produit_promotion' => 0, 'taux_remise' => 0,
        ];
    }

    public function testCreateComputesVatAndTtcFromTheCategory(): void
    {
        $model = new Product();
        $id = $model->create($this->data(['sku' => 'ACC-0900', 'categorie' => 'Accessoire', 'type_vente' => 'Unité']));

        $product = $model->find($id);
        $this->assertSame('ACC-0900', $product['sku']);
        $this->assertSame('20.00', $product['tva']);
        $this->assertSame('12.00', $product['prix_ttc']);
        $this->assertSame(1, (int) $product['active']);
    }

    public function testCreateWithADuplicateSkuIsRefused(): void
    {
        $this->expectException(PDOException::class);
        $this->expectExceptionCode('23000'); // contrainte UNIQUE sur produit.sku
        (new Product())->create($this->data(['sku' => 'THE-0003']));
    }

    public function testSkuExistsIgnoresTheProductBeingEdited(): void
    {
        $model = new Product();
        $this->assertTrue($model->skuExists('THE-0003'));
        $this->assertFalse($model->skuExists('THE-0003', 3)); // le produit 3 garde sa propre référence
        $this->assertFalse($model->skuExists('THE-9999'));
    }

    public function testUpdateCanChangeTheSku(): void
    {
        $model = new Product();
        $product = $model->find(3);
        $model->update(3, ['sku' => 'THE-0903'] + $product);

        $this->assertSame('THE-0903', $model->find(3)['sku']);
    }

    public function testSuggestSkuTakesTheNextNumberOfTheCategory(): void
    {
        $model = new Product();
        $model->create($this->data(['sku' => 'THE-0950']));

        $this->assertSame('THE-0951', $model->suggestSku('Thé'));
        $this->assertSame('CAF-0024', $model->suggestSku('Café')); // CAF-0023 est le dernier du jeu de test
        $this->assertSame('ACC-0031', $model->suggestSku('Accessoire'));
    }

    public function testSearchFindsAProductBySku(): void
    {
        $results = (new Product())->search('ACC-0024');
        $this->assertCount(1, $results);
        $this->assertSame(24, (int) $results[0]['code_produit']);
    }

    public function testToggleActiveKeepsTheRow(): void
    {
        $model = new Product();

        $model->toggleActive(13);
        $this->assertSame(0, (int) $model->find(13)['active']); // désactivé, toujours en base
        $this->assertSame([], $model->search('THE-0013')); // absent de la liste par défaut
        $this->assertCount(1, $model->search('THE-0013', '', true)); // visible avec « Afficher les désactivés »

        $model->toggleActive(13);
        $this->assertSame(1, (int) $model->find(13)['active']);
    }
}
