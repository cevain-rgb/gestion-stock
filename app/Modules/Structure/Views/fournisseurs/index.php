<?php $totalPages = max(1,(int)ceil($total/20)); ?>
<div class="flex items-center justify-between mb-6">
    <div><h1 class="text-xl font-bold text-slate-800">Fournisseurs</h1><p class="text-sm text-slate-500"><?= $total ?> fournisseur(s)</p></div>
    <?php if(!empty($_SESSION['droits']['structure.creer'])): ?>
    <a href="<?= url('structure/fournisseurs/creer') ?>" class="btn-primary"><i class="fa-solid fa-plus"></i> Nouveau fournisseur</a>
    <?php endif; ?>
</div>
<div class="card card-body mb-4">
    <form method="GET" action="<?= url('structure/fournisseurs') ?>" class="flex gap-3 items-end">
        <div class="flex-1"><label class="form-label text-xs">Recherche</label>
        <div class="relative"><i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
        <input type="text" name="q" value="<?= e($recherche??'') ?>" placeholder="Nom, email, téléphone..." class="form-input pl-8 py-2 text-sm"></div></div>
        <button type="submit" class="btn-primary py-2"><i class="fa-solid fa-filter"></i> Filtrer</button>
        <a href="<?= url('structure/fournisseurs') ?>" class="btn-secondary py-2"><i class="fa-solid fa-xmark"></i></a>
    </form>
</div>
<div class="card overflow-hidden">
    <table class="data-table"><thead><tr><th>Fournisseur</th><th>Contact</th><th>Ville</th><th class="text-center">Commandes</th><th class="text-center">Actions</th></tr></thead>
    <tbody>
    <?php if(empty($lignes)): ?><tr><td colspan="5" class="text-center py-10 text-slate-400">Aucun fournisseur.</td></tr><?php endif; ?>
    <?php foreach($lignes as $f): ?>
    <tr>
        <td class="font-semibold text-slate-800"><?= e($f['nom']) ?></td>
        <td><div class="text-sm text-slate-600"><?= e($f['telephone']?:'—') ?></div>
            <div class="text-xs text-slate-400"><?= e($f['email']?:'') ?></div></td>
        <td class="text-sm text-slate-500"><?= e($f['ville']?:'—') ?></td>
        <td class="text-center"><span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-blue-100 text-blue-700 text-xs font-bold"><?= (int)$f['nb_commandes'] ?></span></td>
        <td><div class="flex items-center justify-center gap-1">
            <?php if(!empty($_SESSION['droits']['structure.modifier'])): ?>
            <a href="<?= url('structure/fournisseurs/'.$f['id_fournisseur'].'/edit') ?>" class="btn-secondary btn-sm"><i class="fa-solid fa-pen"></i></a>
            <?php endif; ?>
            <?php if(!empty($_SESSION['droits']['structure.supprimer'])): ?>
            <form method="POST" action="<?= url('structure/fournisseurs/'.$f['id_fournisseur'].'/supprimer') ?>" onsubmit="return confirm('Supprimer ce fournisseur ?')">
                <?= csrfField() ?><button class="btn-danger btn-sm"><i class="fa-solid fa-trash"></i></button>
            </form>
            <?php endif; ?>
        </div></td>
    </tr>
    <?php endforeach; ?>
    </tbody></table>
</div>
<?= paginationLinks($page??1, $totalPages, url('structure/fournisseurs')) ?>
