<section class="card narrow">
    <h1>Connexion</h1>
    <?php if ($error): ?>
        <p class="flash error"><?= e($error) ?></p>
    <?php endif; ?>
    <form method="post" action="<?= url('login') ?>">
        <?= csrf_field() ?>
        <label>Email
            <input type="email" name="email" value="<?= e($email) ?>" required autofocus>
        </label>
        <label>Mot de passe
            <input type="password" name="password" required>
        </label>
        <button>Se connecter</button>
    </form>
</section>