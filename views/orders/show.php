<?php
use App\Models\Order;
use App\Models\Product;

// Détail de la TVA : les prix enregistrés sont TTC, on en déduit le HT ligne par ligne
$totalTtc = 0;
$totalHt = 0;
foreach ($order['lignes'] as $l) {
    $lineTtc = $l['quantite'] * $l['prix_unitaire_achete'];
    $totalTtc += $lineTtc;
    $totalHt += $lineTtc / (1 + $l['tva'] / 100);
}
$shipping = $order['total'] - $totalTtc; // frais de livraison éventuels (commande web)

$index = array_search($order['statut_commande'], Order::STATUS_FLOW, true);
$next = Order::STATUS_FLOW[$index + 1] ?? null;
?>
<div class="page-head">
    <h1>Commande n°<?= e($order['num_commande']) ?></h1>
    <a href="<?= url('orders') ?>">← Retour aux commandes</a>
</div>

<div class="grid-2">
    <section class="card">
        <h2>Informations</h2>
        <p>Date : <?= e(date('d/m/Y H:i', strtotime($order['date_commande']))) ?></p>
        <p>Canal : <?= $order['mode_commande'] === 'web' ? 'En ligne' : 'Magasin' ?></p>
        <p>Paiement : <?= e(PAYMENT_LABELS[$order['mode_paiement']] ?? $order['mode_paiement']) ?>
            <?= $order['date_paiement'] ? '(payée)' : '(non payée)' ?></p>
        <p>Statut : <span class="status <?= e($order['statut_commande']) ?>"><?= e(ORDER_STATUS[$order['statut_commande']]) ?></span></p>

        <?php if ($next): ?>
            <form method="post" action="<?= url('orders/status', ['id' => $order['num_commande']]) ?>">
                <?= csrf_field() ?>
                <button>Passer à « <?= e(ORDER_STATUS[$next]) ?> »</button>
            </form>
        <?php endif; ?>
    </section>

    <section class="card">
        <h2>Client</h2>
        <p><a href="<?= url('clients/show', ['id' => $order['code_client']]) ?>"><?= e($order['prenom_client'] . ' ' . $order['nom_client']) ?></a></p>
        <p><?= e($order['email']) ?> · <?= e($order['telephone']) ?></p>
        <?php if ($order['rue']): ?>
            <h2>Livraison</h2>
            <p><?= e($order['rue']) ?>, <?= e($order['cp']) ?> <?= e($order['ville']) ?>, <?= e($order['pays']) ?></p>
            <p><?= e($order['choix_transporteur']) ?> · prévue le <?= e(date('d/m/Y', strtotime($order['date_livraison']))) ?></p>
        <?php elseif ($order['mode_commande'] === 'web'): ?>
            <p>Retrait en magasin</p>
        <?php endif; ?>
    </section>
</div>

<section class="card">
    <h2>Produits</h2>
    <table>
        <tr><th>Produit</th><th>Quantité</th><th>Prix unitaire TTC</th><th>TVA</th><th>Total TTC</th></tr>
        <?php foreach ($order['lignes'] as $l): ?>
            <tr>
                <td><?= e($l['nom_produit']) ?></td>
                <td><?= e($l['quantite']) ?><?= Product::isSoldByWeight($l) ? ' × 100 g' : '' ?></td>
                <td><?= price($l['prix_unitaire_achete']) ?></td>
                <td><?= e($l['tva']) ?> %</td>
                <td><?= price($l['quantite'] * $l['prix_unitaire_achete']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <p class="totals">
        Total HT : <?= price($totalHt) ?><br>
        TVA : <?= price($totalTtc - $totalHt) ?><br>
        <?php if ($shipping > 0.001): ?>Livraison : <?= price($shipping) ?><br><?php endif; ?>
        <strong>Total TTC : <?= price($order['total']) ?></strong>
    </p>
</section>