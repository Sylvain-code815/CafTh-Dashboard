<section class="card narrow">
    <h1>Mot de passe oublié</h1>
    <?php if ($sent): ?>
        <p class="flash success">Si un compte existe avec cet email, un lien de réinitialisation vient d'y être envoyé.
            Il est valable 1 heure.</p>
    <?php else: ?>
        <?php if ($error): ?>
            <p class="flash error"><?= e($error) ?></p>
        <?php endif; ?>
        <p>Saisissez l'email de votre compte : vous recevrez un lien pour choisir un nouveau mot de passe.</p>
        <form method="post" action="<?= url('forgot-password') ?>" class="form">
            <?= csrf_field() ?>
            <label>Email
                <input type="email" name="email" value="<?= e($email) ?>" required autofocus>
            </label>
            <button>Envoyer le lien</button>
        </form>
    <?php endif; ?>
    <p><a href="<?= url('login') ?>">← Retour à la connexion</a></p>
</section>
