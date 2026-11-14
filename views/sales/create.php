<?php use App\Models\Product; ?>
<h1>Vente en magasin</h1>

<?php if ($error): ?>
    <p class="flash error"><?= e($error) ?></p>
<?php endif; ?>

<form method="post" action="<?= url('sales/create') ?>" class="card form">
    <?= csrf_field() ?>

    <h2>1. Client</h2>
    <label>Client existant
        <select name="client" required>
            <option value="">— Choisir —</option>
            <?php foreach ($clients as $c): ?>
                <option value="<?= e($c['code_client']) ?>" <?= (int) $c['code_client'] === $clientId ? 'selected' : '' ?>>
                    <?= e($c['nom_client'] . ' ' . $c['prenom_client'] . ' (' . $c['email'] . ')') ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>
    <p><a href="<?= url('clients/create', ['from_sale' => 1]) ?>">+ Création rapide d'un client</a></p>

    <h2>2. Panier</h2>
    <p>Produits au poids (vrac, grain) : la quantité est un nombre de tranches de 100 g (3 = 300 g).</p>
    <table>
        <tr><th>Produit</th><th>Quantité</th></tr>
        <?php for ($i = 0; $i < $lines; $i++): ?>
            <tr>
                <td>
                    <select name="produit[]">
                        <option value="">—</option>
                        <?php foreach ($products as $p): ?>
                            <option value="<?= e($p['code_produit']) ?>">
                                <?= e($p['nom_produit']) ?> · <?= price(Product::effectivePrice($p)) ?><?= Product::isSoldByWeight($p) ? ' / 100 g' : '' ?>
                                · stock <?= e($p['stock']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td><input type="number" name="quantite[]" min="1" value="1"></td>
            </tr
        <?php endfor; ?>
    </table>

    <h2>3. Paiement</h2>
    <label>Moyen de paiement
                        <?php foreach ($products as $p): ?>
                            <option value="<?= e($p['code_produit']) ?>">
                                <?= e($p['nom_produit']) ?> · <?= price(Product::effectivePrice($p)) ?><?= Product::isSoldByWeight($p) ? ' / 100 g' : '' ?>
                                · stock <?= e($p['stock']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td><input type="number" name="quantite[]" min="1" value="1"></td>
            </tr>
        <?php endfor; ?>
    </table>

    <h2>3. Paiement</h2>
    <label>Moyen de paiement
        <select name="paiement" required>
            <option value="card">Carte bancaire</option>
            <option value="cash">Espèces</option>
            <option value="check">Chèque</option>
        </select>
    </label>

    <p>Le total HT, la TVA et le total TTC sont calculés à l'enregistrement, avec les prix de la base.</p>
    <button>Enregistrer la vente</button>
</form>
