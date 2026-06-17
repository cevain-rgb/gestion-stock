<div class="max-w-lg mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="<?= url('structure/familles') ?>" class="btn-secondary btn-sm"><i class="fa-solid fa-arrow-left"></i></a>
        <h1 class="text-xl font-bold text-slate-800"><?= $famille ? 'Modifier la famille' : 'Nouvelle famille' ?></h1>
    </div>
    <div class="card card-body">
        <form method="POST" action="<?= $famille ? url('structure/familles/'.$famille['id_famille'].'/edit') : url('structure/familles/creer') ?>">
            <?= csrfField() ?>
            <div class="mb-4">
                <label class="form-label">Libellé <span class="text-rose-500">*</span></label>
                <input type="text" name="libelle" class="form-input <?= !empty($errors['libelle'])?'border-rose-400':'' ?>"
                    value="<?= e($_POST['libelle'] ?? $famille['libelle'] ?? '') ?>" required maxlength="100">
                <?php if (!empty($errors['libelle'])): ?><p class="form-error"><?= e($errors['libelle']) ?></p><?php endif; ?>
            </div>
            <div class="mb-6">
                <label class="form-label">Description</label>
                <textarea name="description" rows="3" class="form-input"><?= e($_POST['description'] ?? $famille['description'] ?? '') ?></textarea>
            </div>
            <div class="flex justify-end gap-3">
                <a href="<?= url('structure/familles') ?>" class="btn-secondary">Annuler</a>
                <button type="submit" class="btn-primary"><i class="fa-solid fa-<?= $famille?'floppy-disk':'plus' ?>"></i> <?= $famille?'Enregistrer':'Créer' ?></button>
            </div>
        </form>
    </div>
</div>
