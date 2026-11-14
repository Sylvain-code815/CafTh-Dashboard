<?php use App\Core\Auth; $user = Auth::user(); $flash = take_flash(); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($title ?? 'Dashboard') ?> - CafThé</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php if ($user): ?>
    <header class="topbar">
        <strong class="brand">CafThé · Dashboard</strong>
        <nav>
            <a href="<?= url('dashboard') ?>">Tableau de bord</a>
            <a href="<?= url('sales/create') ?>">Vente magasin</a>
            <a href="<?= url('orders') ?>">Commandes</a>
            <a href="<?= url('products') ?>">Produits</a>
            <a href="<?= url('clients') ?>">Clients</a>
            <?php if (Auth::isAdmin()): ?>
                <a href="<?= url('employees') ?>">Vendeurs</a>
            <?php endif; ?>
            <a href="<?= url('profile') ?>"><?= e($user['prenom']) ?></a>
            <form method="post" action="<?= url('logout') ?>" class="inline">
                <?= csrf_field() ?>
                <button class="link">Déconnexion</button>
            </form>
        </nav>
    </header>
<?php endif; ?>

<main class="container">
    <?php if ($flash): ?>
        <p class="flash <?= e($flash['type']) ?>"><?= e($flash['message']) ?></p>
    <?php endif; ?>
    <?= $content ?>
</main>
</body>
</html>