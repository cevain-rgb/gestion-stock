<div class="max-w-lg mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="<?= url('utilisateurs/groupes') ?>" class="btn-secondary btn-sm"><i class="fa-solid fa-arrow-left"></i></a>
        <h1 class="text-xl font-bold text-slate-800"><?= $groupe ? 'Modifier le groupe' : 'Nouveau groupe' ?></h1>
    </div>
    <div class="card card-body">
        <form method="POST" action="<?= $groupe ? url('utilisateurs/groupes/'.$groupe['id_groupe'].'/edit') : url('utilisateurs/groupes/creer') ?>">
            <?= csrfField() ?>
            <div class="mb-4">
                <label class="form-label">Libellé <span class="text-rose-500">*</span></label>
                <input type="text" name="libelle" class="form-input <?= !empty($errors['libelle']) ? 'border-rose-400' : '' ?>"
                    value="<?= e($_POST['libelle'] ?? $groupe['libelle'] ?? '') ?>" required maxlength="100">
                <?php if (!empty($errors['libelle'])): ?><p class="form-error"><?= e($errors['libelle']) ?></p><?php endif; ?>
            </div>
            <div class="mb-6">
                <label class="form-label">Description</label>
                <textarea name="description" rows="3" class="form-input" placeholder="Description optionnelle..."><?= e($_POST['description'] ?? $groupe['description'] ?? '') ?></textarea>
            </div>
            <div class="flex items-center justify-end gap-3">
                <a href="<?= url('utilisateurs/groupes') ?>" class="btn-secondary">Annuler</a>
                <button type="submit" class="btn-primary">
                    <i class="fa-solid fa-<?= $groupe ? 'floppy-disk' : 'plus' ?>"></i>
                    <?= $groupe ? 'Enregistrer' : 'Créer le groupe' ?>
                </button>
            </div>
        </form>
    </div>
</div>
