<?php $totalPages = max(1,(int)ceil($total/20)); ?>
<div class="flex items-center justify-between mb-6">
    <div><h1 class="text-xl font-bold text-slate-800">Dons</h1><p class="text-sm text-slate-500"><?= $total ?> don(s) enregistré(s)</p></div>
    <?php if(!empty($_SESSION['droits']['approvisionnement.creer'])): ?>
    <a href="<?= url('approvisionnement/dons/creer') ?>" class="btn-primary"><i class="fa-solid fa-gift"></i> Nouveau don</a>
    <?php endif; ?>
</div>
<div class="card card-body mb-4">
    <form method="GET" action="<?= url('approvisionnement/dons') ?>" class="flex gap-3 items-end">
        <div class="flex-1"><label class="form-label text-xs">Recherche</label>
        <input type="text" name="q" value="<?= e($filtres['recherche']??'') ?>" placeholder="N° don, donateur..." class="form-input py-2 text-sm"></div>
        <button type="submit" class="btn-primary py-2"><i class="fa-solid fa-filter"></i> Filtrer</button>
        <a href="<?= url('approvisionnement/dons') ?>" class="btn-secondary py-2"><i class="fa-solid fa-xmark"></i></a>
    </form>
</div>
<div class="card overflow-hidden">
    <table class="data-table"><thead><tr><th>N° Don</th><th>Donateur</th><th>Date</th><th class="text-right">Valeur estimée</th><th class="text-center">Actions</th></tr></thead>
    <tbody>
    <?php if(empty($lignes)): ?><tr><td colspan="5" class="text-center py-10 text-slate-400">Aucun don enregistré.</td></tr><?php endif; ?>
    <?php foreach($lignes as $d): ?>
    <tr>
        <td class="font-mono text-xs text-violet-600"><?= e($d['numero']) ?></td>
        <td class="font-medium text-slate-700"><?= e($d['fournisseur']?:'Anonyme') ?></td>
        <td class="text-sm text-slate-500"><?= dateFr($d['date_document']) ?></td>
        <td class="text-right font-semibold text-emerald-700"><?= money($d['valeur_totale']) ?></td>
        <td><div class="flex items-center justify-center gap-1">
            <a href="<?= url('approvisionnement/dons/'.$d['oid_doc']) ?>" class="btn-secondary btn-sm"><i class="fa-solid fa-eye"></i></a>
            <?php if(!empty($_SESSION['droits']['approvisionnement.supprimer'])): ?>
            <form method="POST" action="<?= url('approvisionnement/dons/'.$d['oid_doc'].'/supprimer') ?>" onsubmit="return confirm('Supprimer ce don ?')">
                <?= csrfField() ?><button class="btn-danger btn-sm"><i class="fa-solid fa-trash"></i></button>
            </form>
            <?php endif; ?>
        </div></td>
    </tr>
    <?php endforeach; ?>
    </tbody></table>
</div>
<?= paginationLinks($page??1, $totalPages, url('approvisionnement/dons')) ?>
