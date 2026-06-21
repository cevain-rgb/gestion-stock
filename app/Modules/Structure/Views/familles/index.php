<?php $totalPages = max(1,(int)ceil($total/20)); ?>
<div class="flex items-center justify-between mb-6">
    <div><h1 class="text-xl font-bold text-slate-800">Familles de produits</h1>
    <p class="text-sm text-slate-500"><?= $total ?> famille(s)</p></div>
    <?php if (!empty($_SESSION['droits']['structure.creer'])): ?>
    <a href="<?= url('structure/familles/creer') ?>" class="btn-primary"><i class="fa-solid fa-plus"></i> Nouvelle famille</a>
    <?php endif; ?>
</div>
<!-- Recherche -->
<div class="card card-body mb-4">
    <form method="GET" action="<?= url('structure/familles') ?>" class="flex gap-3 items-end">
        <div class="flex-1"><label class="form-label text-xs">Recherche</label>
        <div class="relative"><i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
        <input type="text" name="q" value="<?= e($recherche??'') ?>" placeholder="Libellé, description..." class="form-input pl-8 py-2 text-sm"></div></div>
        <button type="submit" class="btn-primary py-2"><i class="fa-solid fa-filter"></i> Filtrer</button>
        <a href="<?= url('structure/familles') ?>" class="btn-secondary py-2"><i class="fa-solid fa-xmark"></i></a>
    </form>
</div>
<div class="card overflow-hidden">
    <table class="data-table"><thead><tr><th>Famille</th><th>Description</th><th class="text-center">Produits</th><th class="text-center">Actions</th></tr></thead>
    <tbody>
    <?php if (empty($lignes)): ?><tr><td colspan="4" class="text-center py-10 text-slate-400">Aucune famille.</td></tr><?php endif; ?>
    <?php foreach ($lignes as $f): ?>
    <tr>
        <td class="font-semibold text-slate-800"><?= e($f['libelle']) ?></td>
        <td class="text-slate-500 text-sm"><?= e($f['description']?:'—') ?></td>
        <td class="text-center"><span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-violet-100 text-violet-700 text-xs font-bold"><?= (int)$f['nb_produits'] ?></span></td>
        <td><div class="flex items-center justify-center gap-1">
            <?php if (!empty($_SESSION['droits']['structure.modifier'])): ?>
            <a href="<?= url('structure/familles/'.$f['id_famille'].'/edit') ?>" class="btn-secondary btn-sm"><i class="fa-solid fa-pen"></i></a>
            <?php endif; ?>
            <?php if (!empty($_SESSION['droits']['structure.supprimer'])): ?>
            <form method="POST" action="<?= url('structure/familles/'.$f['id_famille'].'/supprimer') ?>" onsubmit="return confirm('Supprimer cette famille ?')">
                <?= csrfField() ?><button class="btn-danger btn-sm" <?= (int)$f['nb_produits']>0?'disabled':'' ?>><i class="fa-solid fa-trash"></i></button>
            </form>
            <?php endif; ?>
        </div></td>
        <td class="text-center"><a href="<?= url('structure/familles/'.$f['id_famille'].'/produits') ?>" target="_blank" class="text-violet-500 hover:text-violet-700 text-sm" title="Voir les produits de cette famille"><i class="fa-solid fa-print"></i></a></td>
    </tr>
    <?php endforeach; ?>
    </tbody></table>
</div>
<?= paginationLinks($page??1, $totalPages, url('structure/familles')) ?>
