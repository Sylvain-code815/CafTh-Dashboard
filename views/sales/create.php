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
                            <option value="<?= e($p['code_produit']) ?>"
                                    data-price="<?= e(Product::effectivePrice($p)) ?>"
                                    data-vat="<?= e($p['tva']) ?>">
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

    <h2>4. Total</h2>
    <p class="totals" id="sale-total">
        Total HT : <span data-total="ht">0,00 €</span><br>
        TVA : <span data-total="vat">0,00 €</span><br>
        <strong>Total TTC : <span data-total="ttc">0,00 €</span></strong>
    </p>
    <p class="hint">Estimation à vérifier avec le client : le montant enregistré est recalculé avec les prix de la base.</p>
    <button>Enregistrer la vente</button>
</form>

<script>
// Total affiché en direct, avant l'enregistrement de la vente
(function () {
    var form = document.querySelector('form.form');
    var euro = new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' });

    function refresh() {
        var ttc = 0, ht = 0;
        form.querySelectorAll('select[name="produit[]"]').forEach(function (select) {
            var option = select.selectedOptions[0];
            var quantity = parseInt(select.closest('tr').querySelector('input[name="quantite[]"]').value, 10);
            if (!option || !option.dataset.price || !(quantity > 0)) return;
            var line = parseFloat(option.dataset.price) * quantity;
            ttc += line;
            ht += line / (1 + parseFloat(option.dataset.vat) / 100);
        });
        form.querySelector('[data-total="ht"]').textContent = euro.format(ht);
        form.querySelector('[data-total="vat"]').textContent = euro.format(ttc - ht);
        form.querySelector('[data-total="ttc"]').textContent = euro.format(ttc);
    }

    form.addEventListener('input', refresh);
    form.addEventListener('change', refresh);
    refresh();
})();
</script>
