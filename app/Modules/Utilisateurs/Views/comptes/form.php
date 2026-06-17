<div class="max-w-2xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="<?= url('utilisateurs/comptes') ?>" class="btn-secondary btn-sm"><i class="fa-solid fa-arrow-left"></i></a>
        <h1>
            <?= $utilisateur ? 'Modifier l\'utilisateur' : 'Nouvel utilisateur' ?>
        </h1>
    </div>

    <div class="card card-body">
        <form method="POST" action="<?= $utilisateur
            ? url('utilisateurs/comptes/'.$utilisateur['id_utilisateur'].'/edit')
            : url('utilisateurs/comptes/creer') ?>">
            <?= csrfField() ?>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="form-label">Nom <span class="text-rose-500">*</span></label>
                    <input type="text" name="nom" class="form-input <?= !empty($errors['nom']) ? 'border-rose-400' : '' ?>"
                        value="<?= e($_POST['nom'] ?? $utilisateur['nom'] ?? '') ?>" required>
                    <?php if (!empty($errors['nom'])): ?><p class="form-error"><?= e($errors['nom']) ?></p><?php endif; ?>
                </div>
                <div>
                    <label class="form-label">Prénom</label>
                    <input type="text" name="prenom" class="form-input"
                        value="<?= e($_POST['prenom'] ?? $utilisateur['prenom'] ?? '') ?>">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="form-label">Login <span class="text-rose-500">*</span></label>
                    <input type="text" name="login" class="form-input <?= !empty($errors['login']) ? 'border-rose-400' : '' ?>"
                        value="<?= e($_POST['login'] ?? $utilisateur['login'] ?? '') ?>" required pattern="[a-zA-Z0-9._-]{3,50}">
                    <p class="text-xs text-slate-400 mt-1">3-50 caractères (lettres, chiffres, ., -, _)</p>
                    <?php if (!empty($errors['login'])): ?><p class="form-error"><?= e($errors['login']) ?></p><?php endif; ?>
                </div>
                <div>
                    <label class="form-label">Groupe <span class="text-rose-500">*</span></label>
                    <select name="id_groupe" class="form-select <?= !empty($errors['id_groupe']) ? 'border-rose-400' : '' ?>" required>
                        <option value="">- Choisir -</option>
                        <?php foreach ($groupes as $g): ?>
                        <option value="<?= $g['id_groupe'] ?>"
                            <?= (string)($_POST['id_groupe'] ?? $utilisateur['id_groupe'] ?? '') === (string)$g['id_groupe'] ? 'selected' : '' ?>>
                            <?= e($g['libelle']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (!empty($errors['id_groupe'])): ?><p class="form-error"><?= e($errors['id_groupe']) ?></p><?php endif; ?>
                </div>
            </div>

            <?php if ($utilisateur): ?>
            <div class="mb-4">
                <label class="form-label flex items-center gap-2">
                    <input type="checkbox" name="actif" class="w-4 h-4 rounded accent-violet-600"
                        <?= !empty($_POST['actif'] ?? $utilisateur['actif'] ?? true) ? 'checked' : '' ?>>
                    Compte actif
                </label>
            </div>
            <?php endif; ?>

            <?php if (!$utilisateur): ?>
            <hr class="my-4 border-slate-100">
            <p class="text-sm font-medium text-slate-600 mb-3">Mot de passe initial</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="form-label">Mot de passe <span class="text-rose-500">*</span></label>
                    <input type="password" name="password" class="form-input <?= !empty($errors['password']) ? 'border-rose-400' : '' ?>"
                        minlength="6" required>
                    <?php if (!empty($errors['password'])): ?><p class="form-error"><?= e($errors['password']) ?></p><?php endif; ?>
                </div>
                <div>
                    <label class="form-label">Confirmation <span class="text-rose-500">*</span></label>
                    <input type="password" name="password_confirm" class="form-input <?= !empty($errors['password_confirm']) ? 'border-rose-400' : '' ?>" required>
                    <?php if (!empty($errors['password_confirm'])): ?><p class="form-error"><?= e($errors['password_confirm']) ?></p><?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($errors['global'])): ?>
            <div class="mb-4 p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-sm">
                <?= e($errors['global']) ?>
            </div>
            <?php endif; ?>

            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="<?= url('utilisateurs/comptes') ?>" class="btn-secondary">Annuler</a>
                <button type="submit" class="btn-primary">
                    <i class="fa-solid fa-<?= $utilisateur ? 'floppy-disk' : 'user-plus' ?>"></i>
                    <?= $utilisateur ? 'Enregistrer' : 'Créer l\'utilisateur' ?>
                </button>
            </div>
        </form>
    </div>
</div>
