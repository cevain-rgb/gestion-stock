<div class="max-w-md mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="<?= url('structure/categories') ?>" class="btn-secondary btn-sm"><i class="fa-solid fa-arrow-left"></i></a>
        <h1 class="text-xl font-bold text-slate-800"><?= $categorie ? 'Modifier la catégorie' : 'Nouvelle catégorie' ?></h1>
    </div>
    <div class="card card-body">
        <form method="POST" action="<?= $categorie ? url('structure/categories/'.$categorie['id_categorie'].'/edit') : url('structure/categories/creer') ?>">
            <?= csrfField() ?>
            <div class="mb-4">
                <label class="form-label">Libellé <span class="text-rose-500">*</span></label>
                <input type="text" name="libelle" required class="form-input <?= !empty($errors['libelle'])?'border-rose-400':'' ?>"
                    value="<?= e($_POST['libelle'] ?? $categorie['libelle'] ?? '') ?>">
                <?php if (!empty($errors['libelle'])): ?><p class="form-error"><?= e($errors['libelle']) ?></p><?php endif; ?>
            </div>
            <div class="mb-6">
                <label class="form-label">Remise (%)</label>
                <input type="number" name="remise_pct" step="0.01" min="0" max="100" class="form-input"
                    value="<?= e($_POST['remise_pct'] ?? $categorie['remise_pct'] ?? '0') ?>">
                <?php if (!empty($errors['remise_pct'])): ?><p class="form-error"><?= e($errors['remise_pct']) ?></p><?php endif; ?>
            </div>
            <div class="flex justify-end gap-3">
                <a href="<?= url('structure/categories') ?>" class="btn-secondary">Annuler</a>
                <button type="submit" class="btn-primary">
                    <i class="fa-solid fa-<?= $categorie?'floppy-disk':'plus' ?>"></i>
                    <?= $categorie ? 'Enregistrer' : 'Créer' ?>
                </button>
            </div>
        </form>
    </div>
</div>
