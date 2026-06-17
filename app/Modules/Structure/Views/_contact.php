<?php // partial: _contact.php — champs contact t_contact
$prefix = $prefix ?? '';
$data   = $data ?? [];
?>
<div class="border-t border-slate-100 pt-4 mt-4">
    <p class="text-sm font-semibold text-slate-600 mb-3">
        <i class="fa-solid fa-address-card mr-2 text-violet-400"></i>Coordonnées
    </p>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="form-label">Téléphone</label>
            <input type="tel" name="telephone" class="form-input"
                value="<?= e($_POST['telephone'] ?? $data['telephone'] ?? '') ?>" placeholder="+237 6xx xxx xxx">
        </div>
        <div>
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-input"
                value="<?= e($_POST['email'] ?? $data['email'] ?? '') ?>">
        </div>
        <div class="sm:col-span-2">
            <label class="form-label">Rue / Adresse</label>
            <input type="text" name="rue" class="form-input"
                value="<?= e($_POST['rue'] ?? $data['rue'] ?? '') ?>">
        </div>
        <div>
            <label class="form-label">Ville</label>
            <input type="text" name="ville" class="form-input"
                value="<?= e($_POST['ville'] ?? $data['ville'] ?? '') ?>">
        </div>
        <div>
            <label class="form-label">Code postal</label>
            <input type="text" name="code_postal" class="form-input"
                value="<?= e($_POST['code_postal'] ?? $data['code_postal'] ?? '') ?>">
        </div>
        <div>
            <label class="form-label">Pays</label>
            <input type="text" name="pays" class="form-input"
                value="<?= e($_POST['pays'] ?? $data['pays'] ?? 'Cameroun') ?>">
        </div>
    </div>
</div>
