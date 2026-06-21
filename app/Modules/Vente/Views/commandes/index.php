<?php $totalPages = max(1,(int)ceil($total/20)); ?>
<div class="flex items-center justify-between mb-6">
    <div><h1 class="text-xl font-bold text-slate-800">Commandes clients</h1><p class="text-sm text-slate-500"><?= $total ?> commande(s)</p></div>
    <div class="flex items-center gap-2">
        <?php if (!empty($_SESSION['droits']['vente.imprimer'])): ?>
        <a href="<?= url('vente/rapports') ?>" class="btn-secondary"><i class="fa-solid fa-chart-bar"></i> Rapports</a>
        <?php endif; ?>
        <?php if(!empty($_SESSION['droits']['vente.creer'])): ?>
        <a href="<?= url('vente/commandes/creer') ?>" class="btn-primary"><i class="fa-solid fa-plus"></i> Nouvelle commande</a>
        <?php endif; ?>
    </div>
</div>
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
<?php
$kpis=[['Total',$stats['total']??0,'fa-file-lines','bg-slate-50 text-slate-600'],
       ['En attente',$stats['en_attente']??0,'fa-clock','bg-amber-50 text-amber-600'],
       ['Validées',$stats['validees']??0,'fa-check','bg-blue-50 text-blue-600'],
       ['Livrées',$stats['livrees']??0,'fa-truck','bg-emerald-50 text-emerald-600']];
foreach($kpis as [$lbl,$val,$ico,$cls]): ?>
<div class="card p-4"><div class="w-8 h-8 rounded-lg <?= $cls ?> flex items-center justify-center mb-2"><i class="fa-solid <?= $ico ?> text-sm"></i></div>
<div class="text-lg font-bold text-slate-800"><?= (int)$val ?></div><div class="text-xs text-slate-500"><?= $lbl ?></div></div>
<?php endforeach; ?>
</div>
<div class="card card-body mb-4">
    <form method="GET" action="<?= url('vente/commandes') ?>" class="flex flex-wrap items-end gap-3">
        <div class="flex-1 min-w-[160px]"><label class="form-label text-xs">Recherche</label>
        <input type="text" name="q" value="<?= e($filtres['recherche']??'') ?>" placeholder="N° commande, client..." class="form-input py-2 text-sm"></div>
        <div class="w-40"><label class="form-label text-xs">Statut</label>
        <select name="statut" class="form-select py-2 text-sm"><option value="">Tous</option>
        <option value="en_attente" <?= $filtres['statut']==='en_attente'?'selected':'' ?>>En attente</option>
        <option value="validee" <?= $filtres['statut']==='validee'?'selected':'' ?>>Validée</option>
        <option value="livree" <?= $filtres['statut']==='livree'?'selected':'' ?>>Livrée</option>
        <option value="annulee" <?= $filtres['statut']==='annulee'?'selected':'' ?>>Annulée</option>
        </select></div>
        <div class="w-44"><label class="form-label text-xs">Client</label>
        <select name="client" class="form-select py-2 text-sm"><option value="">Tous</option>
        <?php foreach($clients as $c): ?><option value="<?= $c['id_client'] ?>" <?= (string)($filtres['id_client']??'')===(string)$c['id_client']?'selected':'' ?>><?= e($c['nom']) ?></option><?php endforeach; ?>
        </select></div>
        <button type="submit" class="btn-primary py-2"><i class="fa-solid fa-filter"></i> Filtrer</button>
        <a href="<?= url('vente/commandes') ?>" class="btn-secondary py-2"><i class="fa-solid fa-xmark"></i></a>
    </form>
</div>
<div class="card overflow-hidden">
    <table class="data-table"><thead><tr><th>N° Commande</th><th>Client</th><th>Date</th><th class="text-right">Montant</th><th class="text-center">Statut</th><th class="text-center">Actions</th></tr></thead>
    <tbody>
    <?php if(empty($lignes)): ?><tr><td colspan="6" class="text-center py-10 text-slate-400">Aucune commande.</td></tr><?php endif; ?>
    <?php foreach($lignes as $c): ?>
    <tr>
        <td class="font-mono text-xs text-violet-600"><?= e($c['numero']) ?></td>
        <td class="font-medium text-slate-700"><?= e($c['client']) ?></td>
        <td class="text-sm text-slate-500"><?= dateFr($c['date_document']) ?></td>
        <td class="text-right font-semibold"><?= money($c['montant_total']) ?></td>
        <td class="text-center"><?= badgeStatutCC($c['statut']) ?></td>
        <td><div class="flex items-center justify-center gap-1">
            <a href="<?= url('vente/commandes/'.$c['oid_doc']) ?>" class="btn-secondary btn-sm"><i class="fa-solid fa-eye"></i></a>
        </div></td>
    </tr>
    <?php endforeach; ?>
    </tbody></table>
</div>
<?= paginationLinks($page??1, $totalPages, url('vente/commandes').'?'.http_build_query(array_filter($filtres??[]))) ?>
