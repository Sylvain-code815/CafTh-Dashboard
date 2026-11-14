<?php
namespace Tests\Unit;

use App\Models\Product;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

// Règles de calcul des produits, sans base de données
class ProductRulesTest extends TestCase
{
    public function testEffectivePriceWithoutPromotionIsTheTtcPrice(): void
    {
        $product = ['prix_ttc' => '12.90', 'produit_promotion' => 0, 'taux_remise' => 20];
        $this->assertSame(12.90, Product::effectivePrice($product));
    }

    public function testEffectivePriceAppliesTheDiscountWhenInPromotion(): void
    {
        $product = ['prix_ttc' => '12.90', 'produit_promotion' => 1, 'taux_remise' => 20];
        $this->assertSame(10.32, Product::effectivePrice($product));
    }

    public function testEffectivePriceIgnoresAPromotionWithoutDiscountRate(): void
    {
        $product = ['prix_ttc' => '8.50', 'produit_promotion' => 1, 'taux_remise' => 0];
        $this->assertSame(8.50, Product::effectivePrice($product));
    }

    public function testEffectivePriceIsRoundedToTheCent(): void
    {
        // 9.99 x 0.85 = 8.4915
        $product = ['prix_ttc' => '9.99', 'produit_promotion' => 1, 'taux_remise' => 15];
        $this->assertSame(8.49, Product::effectivePrice($product));
    }

    #[DataProvider('saleTypes')]
    public function testIsSoldByWeight(string $type, bool $byWeight): void
    {
        $this->assertSame($byWeight, Product::isSoldByWeight(['type_vente' => $type]));
    }

    public static function saleTypes(): array
    {
        return [
            'vrac'    => ['Vrac', true],
            'grain'   => ['Grain', true],
            'boite'   => ['Boite', false],
            'sachet'  => ['Sachet', false],
            'dosette' => ['Dosette', false],
            'unite'   => ['Unité', false],
        ];
    }

    public function testVatRatesFollowTheSpecification(): void
    {
        // CDC 5.3.2 : 5,5 % pour le thé et le café, 20 % pour les accessoires
        $this->assertSame(['Thé' => 5.5, 'Café' => 5.5, 'Accessoire' => 20], Product::VAT_BY_CATEGORY);
    }

    #[DataProvider('vatPrices')]
    public function testPriceWithVat(float $priceHt, string $category, float $expected): void
    {
        $this->assertSame($expected, Product::priceWithVat($priceHt, $category));
    }

    public static function vatPrices(): array
    {
        return [
            'thé 5,5 %'         => [10.0, 'Thé', 10.55],
            'café arrondi'      => [7.99, 'Café', 8.43],
            'accessoire 20 %'   => [10.0, 'Accessoire', 12.0],
            'accessoire arrondi'=> [4.15, 'Accessoire', 4.98],
        ];
    }

    #[DataProvider('skus')]
    public function testSkuFormat(string $sku, bool $valid): void
    {
        $this->assertSame($valid, Product::isValidSku($sku));
    }

    public static function skus(): array
    {
        return [
            'café'              => ['CAF-0001', true],
            'thé'               => ['THE-0003', true],
            'accessoire'        => ['ACC-0005', true],
            'minuscules'        => ['the-0003', false],
            'trois chiffres'    => ['THE-003', false],
            'cinq chiffres'     => ['THE-00003', false],
            'quatre lettres'    => ['THEE-0003', false],
            'sans tiret'        => ['THE0003', false],
            'espace final'      => ['THE-0003 ', false],
            'retour à la ligne' => ["THE-0003\n", false],
            'vide'              => ['', false],
        ];
    }

    public function testEachCategoryHasASkuPrefix(): void
    {
        $this->assertSame(array_keys(Product::VAT_BY_CATEGORY), array_keys(Product::SKU_PREFIX_BY_CATEGORY));
    }
}
