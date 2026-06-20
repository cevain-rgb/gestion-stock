<div class="max-w-3xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="<?= url('approvisionnement/dons') ?>" class="btn-secondary btn-sm"><i class="fa-solid fa-arrow-left"></i></a>
        <div class="flex-1">
            <h1 class="text-xl font-bold text-slate-800 font-mono"><?= e($don['numero']) ?></h1>
            <p class="text-sm text-slate-500">Donateur : <?= e($don['fournisseur']?:'Anonyme') ?></p>
        </div>
    </div>
    <div class="card card-body mb-4">
        <dl class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
            <div><dt class="text-xs text-slate-500 mb-1">Date</dt><dd><?= dateFr($don['date_document']) ?></dd></div>
            <div><dt class="text-xs text-slate-500 mb-1">Créé par</dt><dd class="font-mono"><?= e($don['cree_par']) ?></dd></div>
            <div><dt class="text-xs text-slate-500 mb-1">Lignes</dt><dd><?= count($don['lignes']) ?> produit(s)</dd></div>
        </dl>
        <?php if(!empty($don['observations'])): ?>
        <div class="mt-3 pt-3 border-t border-slate-100 text-sm text-slate-600"><?= e($don['observations']) ?></div>
        <?php endif; ?>
    </div>
    <div class="card overflow-hidden">
        <div class="card-header"><h2 class="font-semibold text-slate-700">Produits reçus en don</h2></div>
        <table class="data-table"><thead><tr><th>Code</th><th>Désignation</th><th class="text-right">Quantité</th><th class="text-right">Valeur unit.</th><th class="text-right">Total</th></tr></thead>
        <tbody>
        <?php foreach($don['lignes'] as $l): ?>
        <tr>
            <td class="font-mono text-xs text-violet-600"><?= e($l['code']) ?></td>
            <td><?= e($l['designation']) ?></td>
            <td class="text-right font-semibold text-emerald-700"><?= number_format((float)$l['quantite'],2,',',' ') ?> <?= e($l['unite']) ?></td>
            <td class="text-right"><?= money($l['valeur_unitaire']) ?></td>
            <td class="text-right font-semibold"><?= money($l['montant_ligne']) ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody></table>
    </div>
</div>
