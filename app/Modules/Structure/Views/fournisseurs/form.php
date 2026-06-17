<?php $data = $fournisseur ?? []; ?>
<div class="max-w-2xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="<?= url('structure/fournisseurs') ?>" class="btn-secondary btn-sm"><i class="fa-solid fa-arrow-left"></i></a>
        <h1 class="text-xl font-bold text-slate-800"><?= $fournisseur ? 'Modifier le fournisseur' : 'Nouveau fournisseur' ?></h1>
    </div>
    <div class="card card-body">
        <form method="POST" action="<?= $fournisseur ? url('structure/fournisseurs/'.$fournisseur['id_fournisseur'].'/edit') : url('structure/fournisseurs/creer') ?>">
            <?= csrfField() ?>
            <div class="mb-4">
                <label class="form-label">Nom <span class="text-rose-500">*</span></label>
                <input type="text" name="nom" class="form-input <?= !empty($errors['nom'])?'border-rose-400':'' ?>"
                    value="<?= e($_POST['nom'] ?? $data['nom'] ?? '') ?>" required>
                <?php if(!empty($errors['nom'])): ?><p class="form-error"><?= e($errors['nom']) ?></p><?php endif; ?>
            </div>
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">Coordonnées</p>
            
<!-- Contact -->
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
    <div>
        <label class="form-label">Téléphone</label>
        <input type="text" name="telephone" class="form-input" value="<?= e($_POST['telephone'] ?? $data['telephone'] ?? '') ?>">
    </div>
    <div>
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-input" value="<?= e($_POST['email'] ?? $data['email'] ?? '') ?>">
    </div>
</div>
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
    <div>
        <label class="form-label">Rue</label>
        <input type="text" name="rue" class="form-input" value="<?= e($_POST['rue'] ?? $data['rue'] ?? '') ?>">
    </div>
    <div>
        <label class="form-label">Ville</label>
        <input type="text" name="ville" class="form-input" value="<?= e($_POST['ville'] ?? $data['ville'] ?? '') ?>">
    </div>
</div>
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
    <div>
        <label class="form-label">Code postal</label>
        <input type="text" name="code_postal" class="form-input" value="<?= e($_POST['code_postal'] ?? $data['code_postal'] ?? '') ?>">
    </div>
    <div>
        <label class="form-label">Pays</label>
        <input type="text" name="pays" class="form-input" value="<?= e($_POST['pays'] ?? $data['pays'] ?? '') ?>">
    </div>
</div>

            <div class="flex justify-end gap-3 mt-2">
                <a href="<?= url('structure/fournisseurs') ?>" class="btn-secondary">Annuler</a>
                <button type="submit" class="btn-primary"><i class="fa-solid fa-<?= $fournisseur?'floppy-disk':'plus' ?>"></i> <?= $fournisseur?'Enregistrer':'Créer' ?></button>
            </div>
        </form>
    </div>
</div>
