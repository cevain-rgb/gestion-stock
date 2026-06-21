<?php $totalPages = max(1,(int)ceil($total/20)); ?>
<div class="flex items-center justify-between mb-6">
    <div><h1 class="text-xl font-bold text-slate-800">Livraisons</h1><p class="text-sm text-slate-500"><?= $total ?> bon(s) de livraison</p></div>
</div>
<div class="card card-body mb-4">
    <form method="GET" action="<?= url('vente/livraisons') ?>" class="flex flex-wrap items-end gap-3">
        <div class="flex-1 min-w-[180px]"><label class="form-label text-xs">Recherche</label>
        <input type="text" name="q" value="<?= e($filtres['recherche']??'') ?>" placeholder="N° livraison, commande, client..." class="form-input py-2 text-sm"></div>
        <div class="w-36"><label class="form-label text-xs">Du</label><input type="date" name="date_debut" value="<?= e($filtres['date_debut']??'') ?>" class="form-input py-2 text-sm"></div>
        <div class="w-36"><label class="form-label text-xs">Au</label><input type="date" name="date_fin" value="<?= e($filtres['date_fin']??'') ?>" class="form-input py-2 text-sm"></div>
        <button type="submit" class="btn-primary py-2"><i class="fa-solid fa-filter"></i> Filtrer</button>
        <a href="<?= url('vente/livraisons') ?>" class="btn-secondary py-2"><i class="fa-solid fa-xmark"></i></a>
    </form>
</div>
<div class="card overflow-hidden">
    <table class="data-table"><thead><tr><th>N° Livraison</th><th>Commande liée</th><th>Client</th><th>Date</th><th class="text-right">Montant livré</th><th class="text-center">Actions</th></tr></thead>
    <tbody>
    <?php if(empty($lignes)): ?><tr><td colspan="6" class="text-center py-10 text-slate-400">Aucune livraison.</td></tr><?php endif; ?>
    <?php foreach($lignes as $l): ?>
    <tr>
        <td class="font-mono text-xs text-violet-600"><?= e($l['numero']) ?></td>
        <td class="font-mono text-xs text-slate-500"><?= e($l['numero_commande']) ?></td>
        <td class="font-medium text-slate-700"><?= e($l['client']) ?></td>
        <td class="text-sm text-slate-500"><?= dateFr($l['date_document']) ?></td>
        <td class="text-right font-semibold text-emerald-700"><?= money($l['montant_livre']) ?></td>
        <td><div class="flex items-center justify-center gap-1">
            <a href="<?= url('vente/livraisons/'.$l['oid_doc']) ?>" class="btn-secondary btn-sm"><i class="fa-solid fa-eye"></i></a>
        </div></td>
    </tr>
    <?php endforeach; ?>
    </tbody></table>
</div>
<?= paginationLinks($page??1, $totalPages, url('vente/livraisons')) ?>
