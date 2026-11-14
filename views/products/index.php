<?php use App\Models\Product; ?>
<div class="page-head">
    <h1>Produits</h1>
    <a class="button" href="<?= url('products/create') ?>">+ Ajouter un produit</a>
</div>

<form method="get" class="filters">
    <input type="hidden" name="route" value="products">
    <input type="search" name="q" value="<?= e($search) ?>" placeholder="Nom ou origine">
    <select name="categorie">
        <option value="">Toutes les catégories</option>
        <?php foreach (array_keys(Product::VAT_BY_CATEGORY) as $cat): ?>
            <option <?= $cat === $category ? 'selected' : '' ?>><?= e($cat) ?></option>
        <?php endforeach; ?>
    </select>
    <label class="check"><input type="checkbox" name="inactifs" <?= $withInactive ? 'checked' : '' ?>> Afficher les désactivés</label>
    <button>Filtrer</button>
</form>

<table>
    <tr><th>Réf.</th><th>Nom</th><th>Catégorie</th><th>Type</th><th>Prix HT</th><th>Prix TTC</th><th>Stock</th><th></th></tr>
    <?php foreach ($products as $p): ?>
        <tr class="<?= $p['active'] ? '' : 'inactive' ?>">
            <td><?= e($p['code_produit']) ?></td>
            <td><?= e($p['nom_produit']) ?><?= $p['produit_promotion'] ? ' <span class="badge">-' . e($p['taux_remise']) . ' %</span>' : '' ?></td>
            <td><?= e($p['categorie']) ?></td>
            <td><?= e($p['type_vente']) ?></td>
            <td><?= price($p['prix_HT']) ?></td>
            <td><?= price($p['prix_ttc']) ?></td>
            <td><?= e($p['stock']) ?></td>
            <td class="actions">
                <a href="<?= url('products/edit', ['id' => $p['code_produit']]) ?>">Modifier</a>
                <form method="post" action="<?= url('products/toggle', ['id' => $p['code_produit']]) ?>" class="inline">
                    <?= csrf_field() ?>
                    <button class="link"><?= $p['active'] ? 'Désactiver' : 'Réactiver' ?></button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
</table>