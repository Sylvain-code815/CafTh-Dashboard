<h1>Tableau de bord</h1>

<div class="kpis">
    <div class="kpi"><span>CA du jour</span><strong><?= price($revenue['jour']) ?></strong></div>
    <div class="kpi"><span>CA de la semaine</span><strong><?= price($revenue['semaine']) ?></strong></div>
    <div class="kpi"><span>CA du mois</span><strong><?= price($revenue['mois']) ?></strong></div>
    <div class="kpi"><span>CA de l'année</span><strong><?= price($revenue['annee']) ?></strong></div>
    <div class="kpi"><span>Nombre de ventes</span><strong><?= e($summary['nombre_ventes']) ?></strong></div>
    <div class="kpi"><span>Panier moyen</span><strong><?= price($summary['panier_moyen']) ?></strong></div>
</div>

<div class="grid-2">
    <section class="card">
        <h2>Top 10 des produits</h2>
        <table>
            <tr><th>Produit</th><th>Catégorie</th><th>Quantité</th><th>CA</th></tr>
            <?php foreach ($top as $p): ?>
                <tr>
                    <td><?= e($p['nom_produit']) ?></td>
                    <td><?= e($p['categorie']) ?></td>
                    <td><?= e($p['quantite_vendue']) ?></td>
                    <td><?= price($p['chiffre_affaires']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </section>

    <section class="card">
        <h2>Ventes par catégorie</h2>
        <table>
            <tr><th>Catégorie</th><th>Quantité</th><th>CA</th></tr>
            <?php foreach ($categories as $c): ?>
                <tr>
                    <td><?= e($c['categorie']) ?></td>
                    <td><?= e($c['quantite_vendue']) ?></td>
                    <td><?= price($c['chiffre_affaires']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>

        <h2>Nouveaux clients (12 mois)</h2>
        <table>
            <tr><th>Mois</th><th>Nouveaux clients</th></tr>
            <?php foreach ($newClients as $m): ?>
                <tr><td><?= e($m['mois']) ?></td><td><?= e($m['nouveaux_clients']) ?></td></tr>
            <?php endforeach; ?>
        </table>
    </section>
</div>