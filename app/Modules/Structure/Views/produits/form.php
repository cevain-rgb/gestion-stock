<div class="max-w-2xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="<?= url('structure/produits') ?>" class="btn-secondary btn-sm"><i class="fa-solid fa-arrow-left"></i></a>
        <h1 class="text-xl font-bold text-slate-800"><?= $produit ? 'Modifier le produit' : 'Nouveau produit' ?></h1>
    </div>
    <div class="card card-body">
        <form method="POST" action="<?= $produit ? url('structure/produits/'.$produit['id_produit'].'/edit') : url('structure/produits/creer') ?>">
            <?= csrfField() ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="form-label">Code <span class="text-rose-500">*</span></label>
                    <input type="text" name="code" class="form-input <?= !empty($errors['code'])?'border-rose-400':'' ?>"
                        value="<?= e($_POST['code'] ?? $produit['code'] ?? '') ?>" required>
                    <?php if(!empty($errors['code'])): ?><p class="form-error"><?= e($errors['code']) ?></p><?php endif; ?>
                </div>
                <div>
                    <label class="form-label">Famille <span class="text-rose-500">*</span></label>
                    <select name="id_famille" class="form-select <?= !empty($errors['id_famille'])?'border-rose-400':'' ?>" required>
                        <option value="">— Choisir —</option>
                        <?php foreach($familles as $f): ?>
                        <option value="<?= $f['id_famille'] ?>" <?= (string)($_POST['id_famille']??$produit['id_famille']??'')===(string)$f['id_famille']?'selected':'' ?>><?= e($f['libelle']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if(!empty($errors['id_famille'])): ?><p class="form-error"><?= e($errors['id_famille']) ?></p><?php endif; ?>
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label">Désignation <span class="text-rose-500">*</span></label>
                <input type="text" name="designation" class="form-input <?= !empty($errors['designation'])?'border-rose-400':'' ?>"
                    value="<?= e($_POST['designation'] ?? $produit['designation'] ?? '') ?>" required>
                <?php if(!empty($errors['designation'])): ?><p class="form-error"><?= e($errors['designation']) ?></p><?php endif; ?>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="form-label">Unité</label>
                    <input type="text" name="unite" class="form-input" value="<?= e($_POST['unite'] ?? $produit['unite'] ?? 'unité') ?>">
                </div>
                <div>
                    <label class="form-label">Prix d'achat (FCFA)</label>
                    <input type="number" step="0.01" min="0" name="prix_achat" class="form-input"
                        value="<?= e($_POST['prix_achat'] ?? $produit['prix_achat'] ?? '0') ?>">
                </div>
                <div>
                    <label class="form-label">Prix de vente (FCFA)</label>
                    <input type="number" step="0.01" min="0" name="prix_vente" class="form-input"
                        value="<?= e($_POST['prix_vente'] ?? $produit['prix_vente'] ?? '0') ?>">
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <?php if(!$produit): ?>
                <div>
                    <label class="form-label">Stock initial</label>
                    <input type="number" step="0.0001" min="0" name="stock_actuel" class="form-input"
                        value="<?= e($_POST['stock_actuel'] ?? '0') ?>">
                </div>
                <?php endif; ?>
                <div>
                    <label class="form-label">Seuil d'alerte</label>
                    <input type="number" step="0.0001" min="0" name="stock_alerte" class="form-input"
                        value="<?= e($_POST['stock_alerte'] ?? $produit['stock_alerte'] ?? '0') ?>">
                </div>
                <div>
                    <label class="form-label">Produit père (optionnel)</label>
                    <select name="id_produit_pere" class="form-select">
                        <option value="">— Aucun (produit racine) —</option>
                        <?php foreach($parents as $par): ?>
                        <?php if(isset($produit['id_produit']) && $par['id_produit'] === $produit['id_produit']) continue; ?>
                        <option value="<?= $par['id_produit'] ?>" <?= (string)($_POST['id_produit_pere']??$produit['id_produit_pere']??'')===(string)$par['id_produit']?'selected':'' ?>>
                            <?= e($par['code'].' — '.$par['designation']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="mb-6 p-3 rounded-lg bg-slate-50 border border-slate-200">
                <label class="flex items-center gap-2 cursor-pointer mb-2">
                    <input type="checkbox" id="fracCheck" name="is_fractionnaire" class="w-4 h-4 accent-violet-600"
                        <?= !empty($_POST['is_fractionnaire']??$produit['is_fractionnaire']??false)?'checked':'' ?> onchange="document.getElementById('fracDiv').classList.toggle('hidden',!this.checked)">
                    <span class="text-sm font-medium text-slate-700">Produit fractionnaire</span>
                </label>
                <div id="fracDiv" class="<?= empty($_POST['is_fractionnaire']??$produit['is_fractionnaire']??false)?'hidden':'' ?>">
                    <label class="form-label text-xs">Facteur de fraction</label>
                    <input type="number" step="0.0001" min="0.0001" name="facteur_fraction" class="form-input w-40"
                        value="<?= e($_POST['facteur_fraction'] ?? $produit['facteur_fraction'] ?? '1') ?>">
                </div>
            </div>
            <?php if(!empty($errors['global'])): ?><div class="mb-4 p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-sm"><?= e($errors['global']) ?></div><?php endif; ?>
            <div class="flex justify-end gap-3">
                <a href="<?= url('structure/produits') ?>" class="btn-secondary">Annuler</a>
                <button type="submit" class="btn-primary"><i class="fa-solid fa-<?= $produit?'floppy-disk':'plus' ?>"></i> <?= $produit?'Enregistrer':'Créer' ?></button>
            </div>
        </form>
    </div>
</div>
