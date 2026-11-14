<h1>Nouveau client</h1>

<form method="post" action="<?= url('clients/create', $fromSale ? ['from_sale' => 1] : []) ?>" class="card form">
    <?= csrf_field() ?>
    <p>Le client est créé sans mot de passe : il le choisira à sa première connexion sur le site.</p>

    <label>Nom
        <input name="nom_client" value="<?= e($data['nom_client']) ?>" maxlength="60" required>
        <small class="error"><?= e($errors['nom_client'] ?? '') ?></small>
    </label>
    <label>Prénom
        <input name="prenom_client" value="<?= e($data['prenom_client']) ?>" maxlength="20" required>
        <small class="error"><?= e($errors['prenom_client'] ?? '') ?></small>
    </label>
    <label>Email
        <input type="email" name="email" value="<?= e($data['email']) ?>" maxlength="250" required>
        <small class="error"><?= e($errors['email'] ?? '') ?></small>
    </label>
    <label>Téléphone
        <input type="tel" name="telephone" value="<?= e($data['telephone']) ?>" maxlength="15" required>
        <small class="error"><?= e($errors['telephone'] ?? '') ?></small>
    </label>

    <div class="actions">
        <button>Créer le client</button>
        <a href="<?= url($fromSale ? 'sales/create' : 'clients') ?>">Annuler</a>
    </div>
</form>