<?php
$labelsMotif = ['perime'=>['Périmé','fa-calendar-xmark','bg-amber-100 text-amber-700'],'casse'=>['Cassé','fa-hammer','bg-rose-100 text-rose-700'],
                'perte'=>['Perte','fa-magnifying-glass','bg-orange-100 text-orange-700'],'offert'=>['Offert','fa-gift','bg-emerald-100 text-emerald-700'],
                'autre'=>['Autre','fa-circle-question','bg-slate-100 text-slate-600']];
[$lbl,$ico,$cls] = $labelsMotif[$sortie['motif']] ?? ['Autre','fa-circle-question','bg-slate-100 text-slate-600'];
?>
<div class="max-w-3xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="<?= url('vente/sorties') ?>" class="btn-secondary btn-sm"><i class="fa-solid fa-arrow-left"></i></a>
        <div class="flex-1">
            <h1 class="text-xl font-bold text-slate-800 font-mono"><?= e($sortie['numero']) ?></h1>
        </div>
        <span class="inline-flex items-center gap-1.5 text-xs px-3 py-1.5 rounded-full font-medium <?= $cls ?>"><i class="fa-solid <?= $ico ?>"></i><?= $lbl ?></span>
    </div>
    <div class="card card-body mb-4">
        <dl class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
            <div><dt class="text-xs text-slate-500 mb-1">Date</dt><dd><?= dateFr($sortie['date_document']) ?></dd></div>
            <div><dt class="text-xs text-slate-500 mb-1">Créé par</dt><dd class="font-mono"><?= e($sortie['cree_par']) ?></dd></div>
            <div><dt class="text-xs text-slate-500 mb-1">Lignes</dt><dd><?= count($sortie['lignes']) ?> produit(s)</dd></div>
        </dl>
        <?php if(!empty($sortie['observations'])): ?>
        <div class="mt-3 pt-3 border-t border-slate-100 text-sm text-slate-600"><?= e($sortie['observations']) ?></div>
        <?php endif; ?>
    </div>
    <div class="card overflow-hidden">
        <div class="card-header"><h2 class="font-semibold text-slate-700">Produits sortis</h2></div>
        <table class="data-table"><thead><tr><th>Code</th><th>Désignation</th><th class="text-right">Quantité</th><th class="text-right">Valeur unit.</th><th class="text-right">Montant</th><th>Détail</th></tr></thead>
        <tbody>
        <?php foreach($sortie['lignes'] as $l): ?>
        <tr>
            <td class="font-mono text-xs text-violet-600"><?= e($l['code']) ?></td>
            <td><?= e($l['designation']) ?></td>
            <td class="text-right font-semibold text-rose-600"><?= number_format((float)$l['quantite'],2,',',' ') ?> <?= e($l['unite']) ?></td>
            <td class="text-right"><?= money($l['valeur_unitaire']) ?></td>
            <td class="text-right font-semibold"><?= money($l['montant_ligne']) ?></td>
            <td class="text-xs text-slate-400"><?= e($l['motif_detail']?:'—') ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody></table>
    </div>
</div>
