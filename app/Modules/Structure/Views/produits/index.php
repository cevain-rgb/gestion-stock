<?php $totalPages = max(1,(int)ceil($total/20)); ?>
<div class="flex items-center justify-between mb-6">
    <div><h1 class="text-xl font-bold text-slate-800">Produits</h1>
    <p class="text-sm text-slate-500"><?= $total ?> produit(s)</p></div>
    <?php if (!empty($_SESSION['droits']['structure.creer'])): ?>
    <a href="<?= url('structure/produits/creer') ?>" class="btn-primary"><i class="fa-solid fa-plus"></i> Nouveau produit</a>
    <?php endif; ?>
</div>
<!-- Stats -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
<?php
$kpis=[['Total produits',$stats['total']??0,'fa-boxes-stacking','bg-violet-50 text-violet-600','text-violet-700'],
       ['En alerte',$stats['en_alerte']??0,'fa-triangle-exclamation','bg-amber-50 text-amber-600','text-amber-700'],
       ['En rupture',$stats['en_rupture']??0,'fa-ban','bg-rose-50 text-rose-600','text-rose-700'],
       ['Valeur stock',money($stats['valeur_totale']??0),'fa-warehouse','bg-emerald-50 text-emerald-600','text-emerald-700']];
foreach($kpis as [$lbl,$val,$ico,$icoCls,$valCls]):
?><div class="card p-4"><div class="w-8 h-8 rounded-lg <?= $icoCls ?> flex items-center justify-center mb-2"><i class="fa-solid <?= $ico ?> text-sm"></i></div>
<div class="text-lg font-bold <?= $valCls ?>"><?= is_string($val)?$val:number_format((int)$val,0,',',' ') ?></div>
<div class="text-xs text-slate-500"><?= $lbl ?></div></div>
<?php endforeach; ?>
</div>
<!-- Filtres -->
<div class="card card-body mb-4">
    <form method="GET" action="<?= url('structure/produits') ?>" class="flex flex-wrap items-end gap-3">
        <div class="flex-1 min-w-[160px]"><label class="form-label text-xs">Recherche</label>
        <div class="relative"><i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
        <input type="text" name="q" value="<?= e($filtres['recherche']??'') ?>" placeholder="Code, désignation..." class="form-input pl-8 py-2 text-sm"></div></div>
        <div class="w-44"><label class="form-label text-xs">Famille</label>
        <select name="famille" class="form-select py-2 text-sm"><option value="">Toutes</option>
        <?php foreach($familles as $f): ?><option value="<?= $f['id_famille'] ?>" <?= (string)($filtres['id_famille']??'')===(string)$f['id_famille']?'selected':'' ?>><?= e($f['libelle']) ?></option><?php endforeach; ?>
        </select></div>
        <div class="flex items-end gap-2">
            <label class="flex items-center gap-1.5 cursor-pointer text-sm text-amber-600 border border-amber-200 bg-amber-50 px-3 py-2 rounded-lg hover:bg-amber-100 transition">
                <input type="checkbox" name="alerte" value="1" <?= !empty($filtres['alerte'])?'checked':'' ?>> <i class="fa-solid fa-triangle-exclamation text-xs"></i> En alerte
            </label>
            <label class="flex items-center gap-1.5 cursor-pointer text-sm text-rose-600 border border-rose-200 bg-rose-50 px-3 py-2 rounded-lg hover:bg-rose-100 transition">
                <input type="checkbox" name="rupture" value="1" <?= !empty($filtres['rupture'])?'checked':'' ?>> <i class="fa-solid fa-ban text-xs"></i> Rupture
            </label>
        </div>
        <button type="submit" class="btn-primary py-2"><i class="fa-solid fa-filter"></i> Filtrer</button>
        <a href="<?= url('structure/produits') ?>" class="btn-secondary py-2"><i class="fa-solid fa-xmark"></i></a>
    </form>
</div>
<div class="card overflow-hidden">
    <table class="data-table"><thead><tr>
        <th>Code</th><th>Désignation</th><th>Famille</th><th class="text-right">Prix achat</th>
        <th class="text-right">Prix vente</th><th class="text-right">Stock</th><th class="text-center">État</th><th class="text-center">Actions</th>
    </tr></thead>
    <tbody>
    <?php if(empty($lignes)): ?><tr><td colspan="8" class="text-center py-10 text-slate-400">Aucun produit.</td></tr><?php endif; ?>
    <?php foreach($lignes as $p): ?>
    <tr>
        <td class="font-mono text-xs text-violet-600"><?= e($p['code']) ?></td>
        <td><div class="font-medium text-slate-800"><?= e($p['designation']) ?></div>
            <?php if($p['id_produit_pere']): ?><div class="text-xs text-slate-400">Produit fils</div><?php endif; ?>
        </td>
        <td class="text-sm text-slate-500"><?= e($p['famille']) ?></td>
        <td class="text-right text-sm"><?= money($p['prix_achat']) ?></td>
        <td class="text-right text-sm font-semibold"><?= money($p['prix_vente']) ?></td>
        <td class="text-right">
            <span class="font-bold <?= $p['en_alerte']?'text-rose-600':'text-slate-800' ?>">
                <?= number_format((float)$p['stock_actuel'],2,',',' ') ?>
            </span>
            <span class="text-xs text-slate-400 ml-0.5"><?= e($p['unite']) ?></span>
        </td>
        <td class="text-center">
            <?php if((float)$p['stock_actuel']<=0): ?>
                <span class="text-xs bg-rose-100 text-rose-700 px-2 py-0.5 rounded-full">Rupture</span>
            <?php elseif($p['en_alerte']): ?>
                <span class="text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full"><i class="fa-solid fa-triangle-exclamation text-[10px] mr-0.5"></i>Alerte</span>
            <?php else: ?>
                <span class="text-xs bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full">OK</span>
            <?php endif; ?>
        </td>
        <td><div class="flex items-center justify-center gap-1">
            <a href="<?= url('structure/produits/'.$p['id_produit']) ?>" class="btn-secondary btn-sm"><i class="fa-solid fa-eye"></i></a>
            <?php if(!empty($_SESSION['droits']['structure.modifier'])): ?>
            <a href="<?= url('structure/produits/'.$p['id_produit'].'/edit') ?>" class="btn-secondary btn-sm"><i class="fa-solid fa-pen"></i></a>
            <?php endif; ?>
            <?php if(!empty($_SESSION['droits']['structure.supprimer'])): ?>
            <form method="POST" action="<?= url('structure/produits/'.$p['id_produit'].'/supprimer') ?>" onsubmit="return confirm('Supprimer ce produit ?')">
                <?= csrfField() ?><button class="btn-danger btn-sm"><i class="fa-solid fa-trash"></i></button>
            </form>
            <?php endif; ?>
        </div></td>
    </tr>
    <?php endforeach; ?>
    </tbody></table>
</div>
<?= paginationLinks($page??1, $totalPages, url('structure/produits').'?'.http_build_query(array_filter($filtres??[]))) ?>
