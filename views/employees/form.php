<?php use App\Core\Auth; ?>
<h1><?= e($title) ?></h1>

<form method="post" class="card form">
    <?= csrf_field() ?>
    <label>Nom
        <input name="nom_employe" value="<?= e($data['nom_employe']) ?>" maxlength="60" required>
        <small class="error"><?= e($errors['nom_employe'] ?? '') ?></small>
    </label>
    <label>Prénom
        <input name="prenom_employe" value="<?= e($data['prenom_employe']) ?>" maxlength="20" required>
    </label>
    <label>Email
        <input type="email" name="email_employe" value="<?= e($data['email_employe']) ?>" maxlength="250" required>
        <small class="error"><?= e($errors['email_employe'] ?? '') ?></small>
    </label>
    <label>Téléphone
        <input type="tel" name="tel_employe" value="<?= e($data['tel_employe']) ?>" maxlength="15">
    </label>
    <label>Rôle
        <select name="role">
            <option value="<?= Auth::SELLER ?>" <?= $data['role'] === Auth::SELLER ? 'selected' : '' ?>>Vendeur</option>
            <option value="<?= Auth::ADMIN ?>" <?= $data['role'] === Auth::ADMIN ? 'selected' : '' ?>>Administrateur</option>
        </select>
        <small class="error"><?= e($errors['role'] ?? '') ?></small>
    </label>
    <label>Mot de passe <?= $employee ? '(laisser vide pour ne pas le changer)' : '' ?>
        <input type="password" name="password" autocomplete="new-password" <?= $employee ? '' : 'required' ?>>
        <small class="error"><?= e($errors['password'] ?? '') ?></small>
    </label>
    <div class="actions">
        <button>Enregistrer</button>
        <a href="<?= url('employees') ?>">Annuler</a>
    </div>
</form>