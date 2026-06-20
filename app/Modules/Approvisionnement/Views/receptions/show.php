<div class="max-w-3xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="<?= url('approvisionnement/receptions') ?>" class="btn-secondary btn-sm"><i class="fa-solid fa-arrow-left"></i></a>
        <div class="flex-1">
            <h1 class="text-xl font-bold text-slate-800 font-mono"><?= e($reception['numero']) ?></h1>
            <p class="text-sm text-slate-500">Commande <?= e($reception['numero_commande']) ?> — <?= e($reception['fournisseur']) ?></p>
        </div>
                <a href="<?= url('approvisionnement/receptions/'.$reception['oid_doc'].'/imprimer') ?>" target="_blank" class="btn-secondary btn-sm">
            <i class="fa-solid fa-print"></i> Imprimer
        </a>
        <?php if(!empty($_SESSION['droits']['approvisionnement.supprimer'])): ?>
        <form method="POST" action="<?= url('approvisionnement/receptions/'.$reception['oid_doc'].'/supprimer') ?>" onsubmit="return confirm('Supprimer cette réception ? Le stock ne sera pas automatiquement ajusté.')">
            <?= csrfField() ?><button class="btn-danger btn-sm"><i class="fa-solid fa-trash"></i> Supprimer</button>
        </form>
        <?php endif; ?>
    </div>
    <div class="card card-body mb-4">
        <dl class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
            <div><dt class="text-xs text-slate-500 mb-1">Date</dt><dd><?= dateFr($reception['date_document']) ?></dd></div>
            <div><dt class="text-xs text-slate-500 mb-1">Créée par</dt><dd class="font-mono"><?= e($reception['cree_par']) ?></dd></div>
            <div><dt class="text-xs text-slate-500 mb-1">Lignes</dt><dd><?= count($reception['lignes']) ?> produit(s)</dd></div>
        </dl>
        <?php if(!empty($reception['observations'])): ?>
        <div class="mt-3 pt-3 border-t border-slate-100 text-sm text-slate-600"><?= e($reception['observations']) ?></div>
        <?php endif; ?>
    </div>
    <div class="card overflow-hidden">
        <div class="card-header"><h2 class="font-semibold text-slate-700">Produits reçus</h2></div>
        <table class="data-table"><thead><tr><th>Code</th><th>Désignation</th><th class="text-right">Qté reçue</th><th class="text-right">Prix unit.</th><th class="text-right">Montant</th></tr></thead>
        <tbody>
        <?php foreach($reception['lignes'] as $l): ?>
        <tr>
            <td class="font-mono text-xs text-violet-600"><?= e($l['code']) ?></td>
            <td><?= e($l['designation']) ?></td>
            <td class="text-right font-semibold text-emerald-700"><?= number_format((float)$l['quantite_recue'],2,',',' ') ?> <?= e($l['unite']) ?></td>
            <td class="text-right"><?= money($l['prix_unitaire']) ?></td>
            <td class="text-right font-semibold"><?= money($l['montant_ligne']) ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody></table>
    </div>
</div>
