<?php use App\Core\Auth; ?>
<h1>Mon profil</h1>

<div class="grid-2">
    <section class="card">
        <h2>Informations</h2>
        <p><?= e($employee['prenom_employe'] . ' ' . $employee['nom_employe']) ?></p>
        <p><?= e($employee['email_employe']) ?> · <?= e($employee['tel_employe']) ?></p>
        <p>Rôle : <?= $employee['role'] === Auth::ADMIN ? 'Administrateur' : 'Vendeur' ?></p>
        <p><small>Pour modifier ces informations, contactez l'administrateur.</small></p>
    </section>

    <form method="post" action="<?= url('profile') ?>" class="card form">
        <h2>Changer de mot de passe</h2>
        <?= csrf_field() ?>
        <label>Mot de passe actuel
            <input type="password" name="old_password" required>
            <small class="error"><?= e($errors['old_password'] ?? '') ?></small>
        </label>
        <label>Nouveau mot de passe
            <input type="password" name="new_password" autocomplete="new-password" required>
            <small class="error"><?= e($errors['new_password'] ?? '') ?></small>
        </label>
        <label>Confirmation
            <input type="password" name="confirm_password" autocomplete="new-password" required>
            <small class="error"><?= e($errors['confirm_password'] ?? '') ?></small>
        </label>
        <button>Modifier</button>
    </form>
</div>