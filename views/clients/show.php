<div class="page-head">
    <h1><?= e($client['prenom_client'] . ' ' . $client['nom_client']) ?></h1>
    <a class="button" href="<?= url('sales/create', ['client' => $client['code_client']]) ?>">Nouvelle vente</a>
</div>

<div class="grid-2">
    <section class="card">
        <h2>Coordonnées</h2>
        <p><?= e($client['email']) ?></p>
        <p><?= e($client['telephone']) ?></p>
        <p>Client depuis le <?= e(date('d/m/Y', strtotime($client['created_at']))) ?></p>
        <p>Compte en ligne : <?= $client['compte_actif'] ? 'activé' : 'pas encore activé' ?></p>
    </section>

    <section class="card">
        <h2>Statistiques</h2>
        <p>Commandes : <?= e($stats['nombre_commandes']) ?></p>
        <p>Total dépensé : <?= price($stats['total_depense']) ?></p>
        <p>Panier moyen : <?= price($stats['panier_moyen']) ?></p>
        <p>Fréquence : <?= $stats['frequence_jours'] !== null ? 'une commande tous les ' . e($stats['frequence_jours']) . ' jours' : '—' ?></p>
        <p>Produits favoris :
            <?= $stats['favoris'] ? e(implode(', ', array_column($stats['favoris'], 'nom_produit'))) : '—' ?></p>
    </section>
</div>

<section class="card">
    <h2>Historique des achats (magasin et en ligne)</h2>
    <table>
        <tr><th>N°</th><th>Date</th><th>Canal</th><th>Statut</th><th>Total</th><th></th></tr>
        <?php foreach ($orders as $o): ?>
            <tr>
                <td><?= e($o['num_commande']) ?></td>
                <td><?= e(date('d/m/Y', strtotime($o['date_commande']))) ?></td>
                <td><?= $o['mode_commande'] === 'web' ? 'En ligne' : 'Magasin' ?></td>
                <td><?= e(ORDER_STATUS[$o['statut_commande']] ?? $o['statut_commande']) ?></td>
                <td><?= price($o['total']) ?></td>
                <td><a href="<?= url('orders/show', ['id' => $o['num_commande']]) ?>">Détail</a></td>
            </tr>
        <?php endforeach; ?>
    </table>
</section>