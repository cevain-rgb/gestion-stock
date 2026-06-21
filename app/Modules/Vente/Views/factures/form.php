<div class="max-w-xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="<?= url('vente/factures') ?>" class="btn-secondary btn-sm"><i class="fa-solid fa-arrow-left"></i></a>
        <h1 class="text-xl font-bold text-slate-800">Nouvelle facture client</h1>
    </div>
    <?php if(empty($commandes)): ?>
    <div class="card card-body text-center py-8 text-slate-400">
        <i class="fa-solid fa-circle-info text-2xl mb-2 block opacity-40"></i>
        Aucune commande entièrement livrée n'est en attente de facturation.
    </div>
    <?php else: ?>
    <div class="card card-body">
        <form method="POST" action="<?= url('vente/factures/creer') ?>">
            <?= csrfField() ?>
            <div class="mb-4">
                <label class="form-label">Commande <span class="text-rose-500">*</span></label>
                <select name="id_commande_c" id="selectCmd" class="form-select <?= !empty($errors['id_commande_c'])?'border-rose-400':'' ?>" required onchange="majMontant()">
                    <option value="">— Choisir une commande livrée —</option>
                    <?php foreach($commandes as $c): ?>
                    <option value="<?= $c['oid_doc'] ?>" data-montant="<?= $c['montant_total'] ?>"><?= e($c['numero'].' — '.$c['client'].' ('.number_format((float)$c['montant_total'],0,',',' ').' FCFA)') ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if(!empty($errors['id_commande_c'])): ?><p class="form-error"><?= e($errors['id_commande_c']) ?></p><?php endif; ?>
            </div>
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="form-label">Montant HT (FCFA) <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.01" min="0.01" name="montant_ht" id="montantHt" class="form-input <?= !empty($errors['montant_ht'])?'border-rose-400':'' ?>" required oninput="majTtc()">
                    <?php if(!empty($errors['montant_ht'])): ?><p class="form-error"><?= e($errors['montant_ht']) ?></p><?php endif; ?>
                </div>
                <div>
                    <label class="form-label">Taux TVA (%)</label>
                    <input type="number" step="0.01" min="0" name="taux_tva" id="tauxTva" value="0" class="form-input" oninput="majTtc()">
                </div>
            </div>
            <div class="mb-6 p-3 rounded-lg bg-violet-50 border border-violet-200 flex justify-between items-center">
                <span class="text-sm font-medium text-violet-700">Montant TTC estimé</span>
                <span class="text-lg font-bold text-violet-800" id="montantTtc">0 FCFA</span>
            </div>
            <?php if(!empty($errors['global'])): ?><div class="mb-4 p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-sm"><?= e($errors['global']) ?></div><?php endif; ?>
            <div class="flex justify-end gap-3">
                <a href="<?= url('vente/factures') ?>" class="btn-secondary">Annuler</a>
                <button type="submit" class="btn-primary"><i class="fa-solid fa-file-invoice-dollar"></i> Créer la facture</button>
            </div>
        </form>
    </div>
    <?php endif; ?>
</div>
<script>
function majMontant() {
    const opt = document.getElementById('selectCmd').selectedOptions[0];
    if (opt && opt.dataset.montant) { document.getElementById('montantHt').value = opt.dataset.montant; majTtc(); }
}
function majTtc() {
    const ht  = parseFloat(document.getElementById('montantHt').value || 0);
    const tva = parseFloat(document.getElementById('tauxTva').value || 0);
    document.getElementById('montantTtc').textContent = (ht * (1 + tva/100)).toLocaleString('fr-FR') + ' FCFA';
}
</script>
