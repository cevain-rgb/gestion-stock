<?php $data = $client ?? []; ?>
<div class="max-w-2xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="<?= url('structure/clients') ?>" class="btn-secondary btn-sm"><i class="fa-solid fa-arrow-left"></i></a>
        <h1 class="text-xl font-bold text-slate-800"><?= $client ? 'Modifier le client' : 'Nouveau client' ?></h1>
    </div>
    <div class="card card-body">
        <form method="POST" action="<?= $client ? url('structure/clients/'.$client['id_client'].'/edit') : url('structure/clients/creer') ?>">
            <?= csrfField() ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="form-label">Nom <span class="text-rose-500">*</span></label>
                    <input type="text" name="nom" class="form-input <?= !empty($errors['nom'])?'border-rose-400':'' ?>"
                        value="<?= e($_POST['nom'] ?? $data['nom'] ?? '') ?>" required>
                    <?php if(!empty($errors['nom'])): ?><p class="form-error"><?= e($errors['nom']) ?></p><?php endif; ?>
                </div>
                <div>
                    <label class="form-label">Catégorie <span class="text-rose-500">*</span></label>
                    <select name="id_categorie" class="form-select <?= !empty($errors['id_categorie'])?'border-rose-400':'' ?>" required>
                        <option value="">— Choisir —</option>
                        <?php foreach($categories as $cat): ?>
                        <option value="<?= $cat['id_categorie'] ?>" <?= (string)($_POST['id_categorie']??$data['id_categorie']??'')===(string)$cat['id_categorie']?'selected':'' ?>>
                            <?= e($cat['libelle']) ?> <?= (float)$cat['remise_pct']>0?'(−'.$cat['remise_pct'].'%)':'' ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if(!empty($errors['id_categorie'])): ?><p class="form-error"><?= e($errors['id_categorie']) ?></p><?php endif; ?>
                </div>
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
                <a href="<?= url('structure/clients') ?>" class="btn-secondary">Annuler</a>
                <button type="submit" class="btn-primary"><i class="fa-solid fa-<?= $client?'floppy-disk':'plus' ?>"></i> <?= $client?'Enregistrer':'Créer' ?></button>
            </div>
        </form>
    </div>
</div>
