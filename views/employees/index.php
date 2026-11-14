<?php use App\Core\Auth; ?>
<div class="page-head">
    <h1>Comptes vendeurs</h1>
    <a class="button" href="<?= url('employees/create') ?>">+ Nouveau compte</a>
</div>

<table>
    <tr><th>Nom</th><th>Email</th><th>Téléphone</th><th>Rôle</th><th></th></tr>
    <?php foreach ($employees as $emp): ?>
        <tr>
            <td><?= e($emp['prenom_employe'] . ' ' . $emp['nom_employe']) ?></td>
            <td><?= e($emp['email_employe']) ?></td>
            <td><?= e($emp['tel_employe']) ?></td>
            <td><?= $emp['role'] === Auth::ADMIN ? 'Administrateur' : 'Vendeur' ?></td>
            <td class="actions">
                <a href="<?= url('employees/edit', ['id' => $emp['num_employe']]) ?>">Modifier</a>
                <?php if ((int) $emp['num_employe'] !== Auth::id()): ?>
                    <form method="post" action="<?= url('employees/delete', ['id' => $emp['num_employe']]) ?>" class="inline"
                          onsubmit="return confirm('Supprimer ce compte ?')">
                        <?= csrf_field() ?>
                        <button class="link danger">Supprimer</button>
                    </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
</table>