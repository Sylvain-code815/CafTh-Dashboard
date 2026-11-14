<?php use App\Models\Product; ?>
<h1><?= e($title) ?></h1>

<form method="post" class="card form">
    <?= csrf_field() ?>

    <label>Nom
        <input name="nom_produit" value="<?= e($data['nom_produit']) ?>" maxlength="50" required>
        <small class="error"><?= e($errors['nom_produit'] ?? '') ?></small>
    </label>

    <label>Description
        <textarea name="description" maxlength="255" required><?= e($data['description']) ?></textarea>
        <small class="error"><?= e($errors['description'] ?? '') ?></small>
    </label>

    <label>Catégorie (fixe la TVA : 5,5 % thé/café, 20 % accessoire)
        <select name="categorie">
            <?php foreach (array_keys(Product::VAT_BY_CATEGORY) as $cat): ?>
                <option <?= $cat === $data['categorie'] ? 'selected' : '' ?>><?= e($cat) ?></option>
            <?php endforeach; ?>
        </select>
        <small class="error"><?= e($errors['categorie'] ?? '') ?></small>
    </label>

    <label>Type de vente
        <select name="type_vente">
            <optgroup label="À l'unité">
                <?php foreach (Product::UNIT_TYPES as $type): ?>
                    <option <?= $type === $data['type_vente'] ? 'selected' : '' ?>><?= e($type) ?></option>
                <?php endforeach; ?>
            </optgroup>
            <optgroup label="Au poids (prix pour 100 g)">
                <?php foreach (Product::WEIGHT_TYPES as $type): ?>
                    <option <?= $type === $data['type_vente'] ? 'selected' : '' ?>><?= e($type) ?></option>
                <?php endforeach; ?>
            </optgroup>
        </select>
        <small class="error"><?= e($errors['type_vente'] ?? '') ?></small>
    </label>

    <label>Prix HT (le TTC est calculé automatiquement)
        <input name="prix_HT" value="<?= e($data['prix_HT']) ?>" inputmode="decimal" required>
        <small class="error"><?= e($errors['prix_HT'] ?? '') ?></small>
    </label>

    <label>Stock
        <input type="number" name="stock" min="0" value="<?= e($data['stock']) ?>" required>
        <small class="error"><?= e($errors['stock'] ?? '') ?></small>
    </label>

    <label>Origine
        <input name="origine" value="<?= e($data['origine']) ?>" maxlength="100">
    </label>

    <label>Image (nom du fichier dans public/images de l'API)
        <input name="image" value="<?= e($data['image']) ?>" maxlength="150" placeholder="produit-the-boite.webp">
    </label>

    <fieldset>
        <label class="check"><input type="checkbox" name="produit_phare" <?= $data['produit_phare'] ? 'checked' : '' ?>> Produit phare</label>
        <label class="check"><input type="checkbox" name="nouveaute" <?= $data['nouveaute'] ? 'checked' : '' ?>> Nouveauté</label>
        <label class="check"><input type="checkbox" name="produit_promotion" <?= $data['produit_promotion'] ? 'checked' : '' ?>> En promotion</label>
    </fieldset>

    <label>Remise (%)
        <input type="number" name="taux_remise" min="0" max="90" value="<?= e($data['taux_remise']) ?>">
        <small class="error"><?= e($errors['taux_remise'] ?? '') ?></small>
    </label>

    <div class="actions">
        <button>Enregistrer</button>
        <a href="<?= url('products') ?>">Annuler</a>
    </div>
</form>