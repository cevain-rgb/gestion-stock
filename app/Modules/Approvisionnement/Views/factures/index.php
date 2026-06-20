<?php $totalPages = max(1,(int)ceil($total/20)); ?>
<div class="flex items-center justify-between mb-6">
    <div><h1 class="text-xl font-bold text-slate-800">Factures fournisseurs</h1><p class="text-sm text-slate-500"><?= $total ?> facture(s)</p></div>
    <?php if(!empty($_SESSION['droits']['approvisionnement.creer'])): ?>
    <a href="<?= url('approvisionnement/factures/creer') ?>" class="btn-primary"><i class="fa-solid fa-plus"></i> Nouvelle facture</a>
    <?php endif; ?>
</div>
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
<?php
$kpis=[['Total',$stats['total']??0,'fa-file-invoice','bg-slate-50 text-slate-600'],
       ['Impayées',$stats['impayees']??0,'fa-circle-exclamation','bg-rose-50 text-rose-600'],
       ['Partielles',$stats['partielles']??0,'fa-clock','bg-amber-50 text-amber-600'],
       ['Total dû',money($stats['total_du']??0),'fa-money-bill-wave','bg-violet-50 text-violet-600']];
foreach($kpis as [$lbl,$val,$ico,$cls]): ?>
<div class="card p-4"><div class="w-8 h-8 rounded-lg <?= $cls ?> flex items-center justify-center mb-2"><i class="fa-solid <?= $ico ?> text-sm"></i></div>
<div class="text-lg font-bold text-slate-800"><?= is_string($val)?$val:(int)$val ?></div><div class="text-xs text-slate-500"><?= $lbl ?></div></div>
<?php endforeach; ?>
</div>
<div class="card card-body mb-4">
    <form method="GET" action="<?= url('approvisionnement/factures') ?>" class="flex flex-wrap items-end gap-3">
        <div class="flex-1 min-w-[160px]"><label class="form-label text-xs">Recherche</label>
        <input type="text" name="q" value="<?= e($filtres['recherche']??'') ?>" placeholder="N° facture, fournisseur..." class="form-input py-2 text-sm"></div>
        <div class="w-40"><label class="form-label text-xs">Statut</label>
        <select name="statut" class="form-select py-2 text-sm"><option value="">Tous</option>
        <option value="impayee" <?= $filtres['statut']==='impayee'?'selected':'' ?>>Impayée</option>
        <option value="partielle" <?= $filtres['statut']==='partielle'?'selected':'' ?>>Partielle</option>
        <option value="soldee" <?= $filtres['statut']==='soldee'?'selected':'' ?>>Soldée</option>
        </select></div>
        <button type="submit" class="btn-primary py-2"><i class="fa-solid fa-filter"></i> Filtrer</button>
        <a href="<?= url('approvisionnement/factures') ?>" class="btn-secondary py-2"><i class="fa-solid fa-xmark"></i></a>
    </form>
</div>
<div class="card overflow-hidden">
    <table class="data-table"><thead><tr><th>N° Facture</th><th>Fournisseur</th><th>Date</th><th class="text-right">Montant TTC</th><th class="text-right">Reste</th><th class="text-center">Statut</th><th class="text-center">Actions</th></tr></thead>
    <tbody>
    <?php if(empty($lignes)): ?><tr><td colspan="7" class="text-center py-10 text-slate-400">Aucune facture.</td></tr><?php endif; ?>
    <?php foreach($lignes as $f): ?>
    <tr>
        <td class="font-mono text-xs text-violet-600"><?= e($f['numero']) ?></td>
        <td class="font-medium text-slate-700"><?= e($f['fournisseur']) ?></td>
        <td class="text-sm text-slate-500"><?= dateFr($f['date_document']) ?></td>
        <td class="text-right font-semibold"><?= money($f['montant_ttc']) ?></td>
        <td class="text-right font-semibold <?= (float)$f['reste_a_payer']>0?'text-rose-600':'text-emerald-600' ?>"><?= money($f['reste_a_payer']) ?></td>
        <td class="text-center"><?= badgePaiement($f['statut_paiement']) ?></td>
        <td><div class="flex items-center justify-center gap-1">
            <a href="<?= url('approvisionnement/factures/'.$f['oid_doc']) ?>" class="btn-secondary btn-sm"><i class="fa-solid fa-eye"></i></a>
        </div></td>
    </tr>
    <?php endforeach; ?>
    </tbody></table>
</div>
<?= paginationLinks($page??1, $totalPages, url('approvisionnement/factures').'?'.http_build_query(array_filter($filtres??[]))) ?>
