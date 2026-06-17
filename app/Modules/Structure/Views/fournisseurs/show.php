<div class="max-w-3xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="<?= url('structure/fournisseurs') ?>" class="btn-secondary btn-sm"><i class="fa-solid fa-arrow-left"></i></a>
        <h1 class="text-xl font-bold text-slate-800"><?= e($fournisseur['nom']) ?></h1>
        <?php if (!empty($_SESSION['droits']['structure.modifier'])): ?>
        <a href="<?= url('structure/fournisseurs/'.$fournisseur['id_fournisseur'].'/edit') ?>" class="btn-primary btn-sm ml-auto">
            <i class="fa-solid fa-pen"></i> Modifier
        </a>
        <?php endif; ?>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
        <div class="card card-body">
            <h2 class="font-semibold text-slate-700 mb-3">Coordonnées</h2>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500">Téléphone</dt><dd><?= e($fournisseur['telephone']?:'—') ?></dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Email</dt><dd><?= e($fournisseur['email']?:'—') ?></dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Ville</dt><dd><?= e($fournisseur['ville']?:'—') ?></dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Pays</dt><dd><?= e($fournisseur['pays']?:'—') ?></dd></div>
            </dl>
        </div>
        <div class="card card-body">
            <h2 class="font-semibold text-slate-700 mb-3">Informations</h2>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500">Depuis le</dt><dd><?= dateFr($fournisseur['created_at'] ?? null) ?></dd></div>
            </dl>
        </div>
    </div>
    <div class="card overflow-hidden">
        <div class="card-header"><h2 class="font-semibold text-slate-700">Dernières commandes</h2></div>
        <?php if (empty($commandes)): ?>
            <div class="py-8 text-center text-slate-400 text-sm">Aucune commande.</div>
        <?php else: ?>
        <table class="data-table">
            <thead><tr><th>Numéro</th><th>Date</th><th>Statut</th><th class="text-right">Montant</th></tr></thead>
            <tbody>
            <?php foreach ($commandes as $c): ?>
            <tr>
                <td class="font-mono text-xs text-violet-600"><?= e($c['numero']) ?></td>
                <td class="text-sm"><?= dateFr($c['date_document']) ?></td>
                <td><?= isset($c['statut']) ? badgeStatutCF($c['statut']) : '' ?></td>
                <td class="text-right font-semibold"><?= money($c['montant_total']) ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>
