<?php $totalPages = max(1,(int)ceil($total/20)); ?>
<div class="flex items-center justify-between mb-6">
    <div><h1 class="text-xl font-bold text-slate-800">Réceptions</h1><p class="text-sm text-slate-500"><?= $total ?> bon(s) de réception</p></div>
</div>
<div class="card card-body mb-4">
    <form method="GET" action="<?= url('approvisionnement/receptions') ?>" class="flex flex-wrap items-end gap-3">
        <div class="flex-1 min-w-[180px]"><label class="form-label text-xs">Recherche</label>
        <input type="text" name="q" value="<?= e($filtres['recherche']??'') ?>" placeholder="N° réception, commande, fournisseur..." class="form-input py-2 text-sm"></div>
        <div class="w-36"><label class="form-label text-xs">Du</label><input type="date" name="date_debut" value="<?= e($filtres['date_debut']??'') ?>" class="form-input py-2 text-sm"></div>
        <div class="w-36"><label class="form-label text-xs">Au</label><input type="date" name="date_fin" value="<?= e($filtres['date_fin']??'') ?>" class="form-input py-2 text-sm"></div>
        <button type="submit" class="btn-primary py-2"><i class="fa-solid fa-filter"></i> Filtrer</button>
        <a href="<?= url('approvisionnement/receptions') ?>" class="btn-secondary py-2"><i class="fa-solid fa-xmark"></i></a>
    </form>
</div>
<div class="card overflow-hidden">
    <table class="data-table"><thead><tr><th>N° Réception</th><th>Commande liée</th><th>Fournisseur</th><th>Date</th><th class="text-right">Montant reçu</th><th class="text-center">Actions</th></tr></thead>
    <tbody>
    <?php if(empty($lignes)): ?><tr><td colspan="6" class="text-center py-10 text-slate-400">Aucune réception.</td></tr><?php endif; ?>
    <?php foreach($lignes as $r): ?>
    <tr>
        <td class="font-mono text-xs text-violet-600"><?= e($r['numero']) ?></td>
        <td class="font-mono text-xs text-slate-500"><?= e($r['numero_commande']) ?></td>
        <td class="font-medium text-slate-700"><?= e($r['fournisseur']) ?></td>
        <td class="text-sm text-slate-500"><?= dateFr($r['date_document']) ?></td>
        <td class="text-right font-semibold text-emerald-700"><?= money($r['montant_recu']) ?></td>
        <td><div class="flex items-center justify-center gap-1">
            <a href="<?= url('approvisionnement/receptions/'.$r['oid_doc']) ?>" class="btn-secondary btn-sm"><i class="fa-solid fa-eye"></i></a>
        </div></td>
    </tr>
    <?php endforeach; ?>
    </tbody></table>
</div>
<?= paginationLinks($page??1, $totalPages, url('approvisionnement/receptions')) ?>
