<?php $totalPages = max(1,(int)ceil($total/20)); ?>
<div class="flex items-center justify-between mb-6">
    <div><h1 class="text-xl font-bold text-slate-800">Clients</h1><p class="text-sm text-slate-500"><?= $total ?> client(s)</p></div>
    <?php if(!empty($_SESSION['droits']['structure.creer'])): ?>
    <a href="<?= url('structure/clients/creer') ?>" class="btn-primary"><i class="fa-solid fa-plus"></i> Nouveau client</a>
    <?php endif; ?>
</div>
<div class="card card-body mb-4">
    <form method="GET" action="<?= url('structure/clients') ?>" class="flex flex-wrap items-end gap-3">
        <div class="flex-1 min-w-[160px]"><label class="form-label text-xs">Recherche</label>
        <div class="relative"><i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
        <input type="text" name="q" value="<?= e($filtres['recherche']??'') ?>" placeholder="Nom, email..." class="form-input pl-8 py-2 text-sm"></div></div>
        <div class="w-44"><label class="form-label text-xs">Catégorie</label>
        <select name="categorie" class="form-select py-2 text-sm"><option value="">Toutes</option>
        <?php foreach($categories as $c): ?><option value="<?= $c['id_categorie'] ?>" <?= (string)($filtres['id_categorie']??'')===(string)$c['id_categorie']?'selected':'' ?>><?= e($c['libelle']) ?></option><?php endforeach; ?>
        </select></div>
        <button type="submit" class="btn-primary py-2"><i class="fa-solid fa-filter"></i> Filtrer</button>
        <a href="<?= url('structure/clients') ?>" class="btn-secondary py-2"><i class="fa-solid fa-xmark"></i></a>
    </form>
</div>
<div class="card overflow-hidden">
    <table class="data-table"><thead><tr><th>Client</th><th>Contact</th><th>Catégorie</th><th class="text-center">Cmds</th><th class="text-right">Total achats</th><th class="text-center">Actions</th></tr></thead>
    <tbody>
    <?php if(empty($lignes)): ?><tr><td colspan="6" class="text-center py-10 text-slate-400">Aucun client.</td></tr><?php endif; ?>
    <?php foreach($lignes as $c): ?>
    <tr>
        <td><div class="font-semibold text-slate-800"><?= e($c['nom']) ?></div><div class="text-xs text-slate-400"><?= e($c['ville']?:'') ?></div></td>
        <td><div class="text-sm"><?= e($c['telephone']?:'—') ?></div><div class="text-xs text-slate-400"><?= e($c['email']?:'') ?></div></td>
        <td><span class="text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full"><?= e($c['categorie']) ?></span>
            <?php if((float)$c['remise_pct']>0): ?><span class="ml-1 text-xs text-emerald-600">-<?= $c['remise_pct'] ?>%</span><?php endif; ?></td>
        <td class="text-center text-sm font-semibold text-slate-600"><?= (int)$c['nb_commandes'] ?></td>
        <td class="text-right text-sm font-semibold text-violet-700"><?= money($c['total_achats']) ?></td>
        <td><div class="flex items-center justify-center gap-1">
            <?php if(!empty($_SESSION['droits']['structure.modifier'])): ?>
            <a href="<?= url('structure/clients/'.$c['id_client'].'/edit') ?>" class="btn-secondary btn-sm"><i class="fa-solid fa-pen"></i></a>
            <?php endif; ?>
            <?php if(!empty($_SESSION['droits']['structure.supprimer'])): ?>
            <form method="POST" action="<?= url('structure/clients/'.$c['id_client'].'/supprimer') ?>" onsubmit="return confirm('Supprimer ce client ?')">
                <?= csrfField() ?><button class="btn-danger btn-sm"><i class="fa-solid fa-trash"></i></button>
            </form>
            <?php endif; ?>
        </div></td>
    </tr>
    <?php endforeach; ?>
    </tbody></table>
</div>
<?= paginationLinks($page??1, $totalPages, url('structure/clients').'?'.http_build_query(array_filter($filtres??[]))) ?>
