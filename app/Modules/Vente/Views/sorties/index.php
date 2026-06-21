<?php
$totalPages = max(1,(int)ceil($total/20));
$labelsMotif = ['perime'=>['Périmé','fa-calendar-xmark','bg-amber-100 text-amber-700'],'casse'=>['Cassé','fa-hammer','bg-rose-100 text-rose-700'],
                'perte'=>['Perte','fa-magnifying-glass','bg-orange-100 text-orange-700'],'offert'=>['Offert','fa-gift','bg-emerald-100 text-emerald-700'],
                'autre'=>['Autre','fa-circle-question','bg-slate-100 text-slate-600']];
?>
<div class="flex items-center justify-between mb-6">
    <div><h1 class="text-xl font-bold text-slate-800">Bons de sortie</h1><p class="text-sm text-slate-500"><?= $total ?> bon(s)</p></div>
    <?php if(!empty($_SESSION['droits']['vente.creer'])): ?>
    <a href="<?= url('vente/sorties/creer') ?>" class="btn-primary"><i class="fa-solid fa-box-open"></i> Nouveau bon de sortie</a>
    <?php endif; ?>
</div>
<div class="card card-body mb-4">
    <form method="GET" action="<?= url('vente/sorties') ?>" class="flex flex-wrap items-end gap-3">
        <div class="flex-1 min-w-[160px]"><label class="form-label text-xs">Recherche</label>
        <input type="text" name="q" value="<?= e($filtres['recherche']??'') ?>" placeholder="N° bon..." class="form-input py-2 text-sm"></div>
        <div class="w-44"><label class="form-label text-xs">Motif</label>
        <select name="motif" class="form-select py-2 text-sm"><option value="">Tous</option>
        <?php foreach($labelsMotif as $key=>[$lbl,,]): ?><option value="<?= $key ?>" <?= $filtres['motif']===$key?'selected':'' ?>><?= $lbl ?></option><?php endforeach; ?>
        </select></div>
        <button type="submit" class="btn-primary py-2"><i class="fa-solid fa-filter"></i> Filtrer</button>
        <a href="<?= url('vente/sorties') ?>" class="btn-secondary py-2"><i class="fa-solid fa-xmark"></i></a>
    </form>
</div>
<div class="card overflow-hidden">
    <table class="data-table"><thead><tr><th>N° Bon</th><th>Motif</th><th>Date</th><th class="text-right">Valeur</th><th class="text-center">Actions</th></tr></thead>
    <tbody>
    <?php if(empty($lignes)): ?><tr><td colspan="5" class="text-center py-10 text-slate-400">Aucun bon de sortie.</td></tr><?php endif; ?>
    <?php foreach($lignes as $s): ?>
    <?php [$lbl,$ico,$cls] = $labelsMotif[$s['motif']] ?? ['Autre','fa-circle-question','bg-slate-100 text-slate-600']; ?>
    <tr>
        <td class="font-mono text-xs text-violet-600"><?= e($s['numero']) ?></td>
        <td><span class="inline-flex items-center gap-1.5 text-xs px-2 py-0.5 rounded-full font-medium <?= $cls ?>"><i class="fa-solid <?= $ico ?> text-[10px]"></i><?= $lbl ?></span></td>
        <td class="text-sm text-slate-500"><?= dateFr($s['date_document']) ?></td>
        <td class="text-right font-semibold text-rose-600"><?= money($s['valeur_totale']) ?></td>
        <td><div class="flex items-center justify-center gap-1">
            <a href="<?= url('vente/sorties/'.$s['oid_doc']) ?>" class="btn-secondary btn-sm"><i class="fa-solid fa-eye"></i></a>
            <?php if(!empty($_SESSION['droits']['vente.supprimer'])): ?>
            <form method="POST" action="<?= url('vente/sorties/'.$s['oid_doc'].'/supprimer') ?>" onsubmit="return confirm('Supprimer ce bon ?')">
                <?= csrfField() ?><button class="btn-danger btn-sm"><i class="fa-solid fa-trash"></i></button>
            </form>
            <?php endif; ?>
        </div></td>
    </tr>
    <?php endforeach; ?>
    </tbody></table>
</div>
<?= paginationLinks($page??1, $totalPages, url('vente/sorties')) ?>
