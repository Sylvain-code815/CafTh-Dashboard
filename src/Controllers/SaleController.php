<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use RuntimeException;

// Enregistrement d'une vente en magasin
class SaleController extends Controller
{
    private const LINES = 6;

    public function create(): void
    {
        $error = null;
        $clientId = (int) ($_POST['client'] ?? $_GET['client'] ?? 0);

        if ($this->isPost()) {
            check_csrf();
            $paymentMode = $_POST['paiement'] ?? '';

            // Regroupe les lignes : [code_produit => quantité totale]
            $lines = [];
            foreach ($_POST['produit'] ?? [] as $i => $productId) {
                $productId = (int) $productId;
                $quantity = (int) ($_POST['quantite'][$i] ?? 0);
                if ($productId > 0 && $quantity > 0) {
                    $lines[$productId] = ($lines[$productId] ?? 0) + $quantity;
                }
            }

            if (!(new Client())->find($clientId)) {
                $error = 'Choisissez un client';
            } elseif (!$lines) {
                $error = 'Ajoutez au moins un produit';
            } elseif (!in_array($paymentMode, Order::STORE_PAYMENTS, true)) {
                $error = 'Moyen de paiement invalide';
            } else {
                try {
                    $orderId = (new Order())->createStoreSale($clientId, $lines, $paymentMode);
                    flash('Vente enregistrée, stock mis à jour');
                    redirect('orders/show', ['id' => $orderId]);
                } catch (RuntimeException $e) {
                    $error = $e->getMessage(); // stock insuffisant, produit désactivé...
                }
            }
        }

        $this->render('sales/create', [
            'title'    => 'Vente en magasin',
            'clients'  => (new Client())->search(),
            'products' => (new Product())->forSale(),
            'clientId' => $clientId,
            'error'    => $error,
            'lines'    => self::LINES,
        ]);
    }
}