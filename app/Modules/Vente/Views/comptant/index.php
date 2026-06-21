<?php $totalPages = max(1,(int)ceil($total/20)); ?>
<div class="flex items-center justify-between mb-6">
    <div><h1 class="text-xl font-bold text-slate-800">Vente comptant</h1><p class="text-sm text-slate-500"><?= $total ?> vente(s)</p></div>
    <?php if(!empty($_SESSION['droits']['vente.creer'])): ?>
    <a href="<?= url('vente/comptant/creer') ?>" class="btn-primary"><i class="fa-solid fa-cash-register"></i> Nouvelle vente</a>
    <?php endif; ?>
</div>
<div class="card card-body mb-4">
    <form method="GET" action="<?= url('vente/comptant') ?>" class="flex flex-wrap items-end gap-3">
        <div class="flex-1 min-w-[160px]"><label class="form-label text-xs">Recherche</label>
        <input type="text" name="q" value="<?= e($filtres['recherche']??'') ?>" placeholder="N° vente, client..." class="form-input py-2 text-sm"></div>
        <div class="w-36"><label class="form-label text-xs">Du</label><input type="date" name="date_debut" value="<?= e($filtres['date_debut']??'') ?>" class="form-input py-2 text-sm"></div>
        <div class="w-36"><label class="form-label text-xs">Au</label><input type="date" name="date_fin" value="<?= e($filtres['date_fin']??'') ?>" class="form-input py-2 text-sm"></div>
        <button type="submit" class="btn-primary py-2"><i class="fa-solid fa-filter"></i> Filtrer</button>
        <a href="<?= url('vente/comptant') ?>" class="btn-secondary py-2"><i class="fa-solid fa-xmark"></i></a>
    </form>
</div>
<div class="card overflow-hidden">
    <table class="data-table"><thead><tr><th>N° Vente</th><th>Client</th><th>Date</th><th class="text-right">Montant</th><th class="text-center">Actions</th></tr></thead>
    <tbody>
    <?php if(empty($lignes)): ?><tr><td colspan="5" class="text-center py-10 text-slate-400">Aucune vente comptant.</td></tr><?php endif; ?>
    <?php foreach($lignes as $c): ?>
    <tr>
        <td class="font-mono text-xs text-emerald-600"><?= e($c['numero']) ?></td>
        <td class="font-medium text-slate-700"><?= e($c['client']) ?></td>
        <td class="text-sm text-slate-500"><?= dateFr($c['date_document']) ?></td>
        <td class="text-right font-semibold"><?= money($c['montant_total']) ?></td>
        <td><div class="flex items-center justify-center gap-1">
            <a href="<?= url('vente/commandes/'.$c['oid_doc']) ?>" class="btn-secondary btn-sm"><i class="fa-solid fa-eye"></i></a>
        </div></td>
    </tr>
    <?php endforeach; ?>
    </tbody></table>
</div>
<?= paginationLinks($page??1, $totalPages, url('vente/comptant')) ?>
