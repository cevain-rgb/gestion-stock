<div class="max-w-2xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="<?= url('structure/banques') ?>" class="btn-secondary btn-sm"><i class="fa-solid fa-arrow-left"></i></a>
        <h1 class="text-xl font-bold text-slate-800">Modifier la banque</h1>
    </div>
    <div class="grid grid-cols-1 gap-4">
        <div class="card card-body">
            <form method="POST" action="<?= url('structure/banques/'.$banque['id_banque'].'/edit') ?>">
                <?= csrfField() ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div><label class="form-label">Nom <span class="text-rose-500">*</span></label>
                        <input type="text" name="nom" class="form-input" value="<?= e($banque['nom']) ?>" required></div>
                    <div><label class="form-label">N° compte</label>
                        <input type="text" name="numero_compte" class="form-input" value="<?= e($banque['numero_compte']??'') ?>"></div>
                    <div><label class="form-label">Ville</label>
                        <input type="text" name="ville" class="form-input" value="<?= e($banque['ville']??'') ?>"></div>
                    <div><label class="form-label">Pays</label>
                        <input type="text" name="pays" class="form-input" value="<?= e($banque['pays']??'Cameroun') ?>"></div>
                </div>
                <div class="flex justify-end gap-3">
                    <a href="<?= url('structure/banques') ?>" class="btn-secondary">Annuler</a>
                    <button type="submit" class="btn-primary"><i class="fa-solid fa-floppy-disk"></i> Enregistrer</button>
                </div>
            </form>
        </div>
        <!-- Versements -->
        <div class="card overflow-hidden">
            <div class="card-header"><h2 class="font-semibold text-slate-700">Versements</h2></div>
            <div class="p-4 border-b border-slate-100">
                <form method="POST" action="<?= url('structure/banques/'.$banque['id_banque'].'/versement') ?>" class="flex flex-wrap gap-3 items-end">
                    <?= csrfField() ?>
                    <div class="w-36"><label class="form-label text-xs">Montant (FCFA)</label>
                        <input type="number" name="montant" step="1" min="1" class="form-input" required></div>
                    <div class="w-36"><label class="form-label text-xs">Date</label>
                        <input type="date" name="date_versement" class="form-input" value="<?= date('Y-m-d') ?>"></div>
                    <div class="flex-1 min-w-[140px]"><label class="form-label text-xs">Référence</label>
                        <input type="text" name="reference" class="form-input" placeholder="N° virement..."></div>
                    <button type="submit" class="btn-primary py-2"><i class="fa-solid fa-plus"></i> Ajouter</button>
                </form>
            </div>
            <table class="data-table">
                <thead><tr><th>Date</th><th class="text-right">Montant</th><th>Référence</th></tr></thead>
                <tbody>
                <?php if (empty($versements)): ?><tr><td colspan="3" class="text-center py-6 text-slate-400 text-sm">Aucun versement.</td></tr><?php endif; ?>
                <?php foreach ($versements as $v): ?>
                <tr>
                    <td class="text-sm"><?= dateFr($v['date_versement']) ?></td>
                    <td class="text-right font-semibold text-emerald-700"><?= money($v['montant']) ?></td>
                    <td class="text-sm text-slate-500"><?= e($v['reference']?:'—') ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
