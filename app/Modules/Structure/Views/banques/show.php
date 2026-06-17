<div class="max-w-3xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="<?= url('structure/banques') ?>" class="btn-secondary btn-sm"><i class="fa-solid fa-arrow-left"></i></a>
        <h1 class="text-xl font-bold text-slate-800"><?= e($banque['nom']) ?></h1>
        <?php if (!empty($_SESSION['droits']['structure.modifier'])): ?>
        <a href="<?= url('structure/banques/'.$banque['id_banque'].'/edit') ?>" class="btn-primary btn-sm ml-auto">
            <i class="fa-solid fa-pen"></i> Modifier
        </a>
        <?php endif; ?>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
        <div class="card card-body">
            <h2 class="font-semibold text-slate-700 mb-3">Informations</h2>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500">N° de compte</dt><dd class="font-mono"><?= e($banque['numero_compte']?:'—') ?></dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Ville</dt><dd><?= e($banque['ville']?:'—') ?></dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Pays</dt><dd><?= e($banque['pays']?:'—') ?></dd></div>
                <div class="flex justify-between border-t pt-2"><dt class="text-slate-500">Total versé</dt>
                    <dd class="font-bold text-violet-700"><?= money(array_sum(array_column($versements,'montant'))) ?></dd>
                </div>
            </dl>
        </div>
        <?php if (!empty($_SESSION['droits']['structure.creer'])): ?>
        <div class="card card-body">
            <h2 class="font-semibold text-slate-700 mb-3">Enregistrer un versement</h2>
            <form method="POST" action="<?= url('structure/banques/'.$banque['id_banque'].'/versement') ?>">
                <?= csrfField() ?>
                <div class="mb-3">
                    <label class="form-label text-xs">Montant <span class="text-rose-500">*</span></label>
                    <input type="number" name="montant" step="0.01" min="0.01" required class="form-input">
                </div>
                <div class="mb-3">
                    <label class="form-label text-xs">Date</label>
                    <input type="date" name="date_versement" class="form-input" value="<?= date('Y-m-d') ?>">
                </div>
                <div class="mb-4">
                    <label class="form-label text-xs">Référence</label>
                    <input type="text" name="reference" class="form-input" placeholder="N° de virement…">
                </div>
                <button type="submit" class="btn-primary w-full btn-sm">
                    <i class="fa-solid fa-plus"></i> Enregistrer
                </button>
            </form>
        </div>
        <?php endif; ?>
    </div>
    <div class="card overflow-hidden">
        <div class="card-header"><h2 class="font-semibold text-slate-700">Historique des versements</h2></div>
        <?php if (empty($versements)): ?>
            <div class="py-8 text-center text-slate-400 text-sm">Aucun versement enregistré.</div>
        <?php else: ?>
        <table class="data-table">
            <thead><tr><th>Date</th><th>Montant</th><th>Référence</th></tr></thead>
            <tbody>
            <?php foreach ($versements as $v): ?>
            <tr>
                <td class="text-sm"><?= dateFr($v['date_versement']) ?></td>
                <td class="font-semibold text-violet-700"><?= money($v['montant']) ?></td>
                <td class="text-slate-500 text-sm"><?= e($v['reference']?:'—') ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>
