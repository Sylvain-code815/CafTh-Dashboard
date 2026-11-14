<?php use App\Models\Employee; ?>
<section class="card narrow">
    <h1>Nouveau mot de passe</h1>
    <?php if (!$valid): ?>
        <p class="flash error">Ce lien n'est plus valable : il a expiré ou a déjà été utilisé.</p>
        <p><a href="<?= url('forgot-password') ?>">Demander un nouveau lien</a></p>
    <?php else: ?>
        <form method="post" action="<?= url('reset-password') ?>" class="form">
            <?= csrf_field() ?>
            <input type="hidden" name="token" value="<?= e($token) ?>">
            <label>Nouveau mot de passe
                <input type="password" name="password" autocomplete="new-password" required autofocus>
                <small><?= e(Employee::PASSWORD_RULE) ?></small>
                <small class="error"><?= e($errors['password'] ?? '') ?></small>
            </label>
            <label>Confirmation
                <input type="password" name="confirm_password" autocomplete="new-password" required>
                <small class="error"><?= e($errors['confirm_password'] ?? '') ?></small>
            </label>
            <button>Enregistrer le mot de passe</button>
        </form>
    <?php endif; ?>
</section>
