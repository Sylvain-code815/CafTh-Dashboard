<div class="page-head">
    <h1>Clients</h1>
    <a class="button" href="<?= url('clients/create') ?>">+ Nouveau client</a>
</div>

<form method="get" class="filters">
    <input type="hidden" name="route" value="clients">
    <input type="search" name="q" value="<?= e($search) ?>" placeholder="Nom, email ou téléphone">
    <button>Rechercher</button>
</form>

<table>
    <tr><th>Nom</th><th>Email</th><th>Téléphone</th><th>Compte en ligne</th><th></th></tr>
    <?php foreach ($clients as $c): ?>
        <tr>
            <td><?= e($c['prenom_client'] . ' ' . $c['nom_client']) ?></td>
            <td><?= e($c['email']) ?></td>
            <td><?= e($c['telephone']) ?></td>
            <td><?= $c['compte_actif'] ? 'Activé' : 'Pas encore activé' ?></td>
            <td><a href="<?= url('clients/show', ['id' => $c['code_client']]) ?>">Fiche</a></td>
        </tr>
    <?php endforeach; ?>
</table>