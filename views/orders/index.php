<h1>Commandes</h1>

<form method="get" class="filters">
    <input type="hidden" name="route" value="orders">
    <select name="mode">
        <option value="web" <?= $mode === 'web' ? 'selected' : '' ?>>En ligne</option>
        <option value="store" <?= $mode === 'store' ? 'selected' : '' ?>>Magasin</option>
        <option value="" <?= $mode === '' ? 'selected' : '' ?>>Toutes</option>
    </select>
    <select name="statut">
        <option value="">Tous les statuts</option>
        <?php foreach (ORDER_STATUS as $value => $label): ?>
            <option value="<?= e($value) ?>" <?= $value === $status ? 'selected' : '' ?>><?= e($label) ?></option>
        <?php endforeach; ?>
    </select>
    <input type="search" name="client" value="<?= e($client) ?>" placeholder="Nom ou email du client">
    <button>Filtrer</button>
</form>

<table>
    <tr><th>N°</th><th>Date</th><th>Client</th><th>Canal</th><th>Paiement</th><th>Total</th><th>Statut</th><th></th></tr>
    <?php foreach ($orders as $o): ?>
        <tr>
            <td><?= e($o['num_commande']) ?></td>
            <td><?= e(date('d/m/Y H:i', strtotime($o['date_commande']))) ?></td>
            <td><?= e($o['prenom_client'] . ' ' . $o['nom_client']) ?></td>
            <td><?= $o['mode_commande'] === 'web' ? 'En ligne' : 'Magasin' ?></td>
            <td><?= e(PAYMENT_LABELS[$o['mode_paiement']] ?? $o['mode_paiement']) ?></td>
            <td><?= price($o['total']) ?></td>
            <td><span class="status <?= e($o['statut_commande']) ?>"><?= e(ORDER_STATUS[$o['statut_commande']] ?? $o['statut_commande']) ?></span></td>
            <td><a href="<?= url('orders/show', ['id' => $o['num_commande']]) ?>">Détail</a></td>
        </tr>
    <?php endforeach; ?>
</table>