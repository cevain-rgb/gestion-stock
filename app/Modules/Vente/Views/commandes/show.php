<div class="max-w-3xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="<?= url('vente/commandes') ?>" class="btn-secondary btn-sm"><i class="fa-solid fa-arrow-left"></i></a>
        <div class="flex-1">
            <h1 class="text-xl font-bold text-slate-800 font-mono"><?= e($cmd['numero']) ?></h1>
            <p class="text-sm text-slate-500"><?= e($cmd['client']) ?></p>
        </div>
        <?= badgeStatutCC($cmd['statut']) ?>
    </div>

    <div class="flex items-center gap-2 mb-2">
        <?php if (!empty($_SESSION['droits']['vente.imprimer'])): ?>
        <a href="<?= url('vente/commandes/'.$cmd['oid_doc'].'/imprimer') ?>" target="_blank" class="btn-secondary btn-sm">
            <i class="fa-solid fa-print"></i> Imprimer BC
        </a>
        <?php endif; ?>
    </div>
    <?php if(!empty($_SESSION['droits']['vente.modifier'])): ?>
    <div class="flex items-center gap-2 mb-4">
        <?php if($cmd['statut']==='en_attente'): ?>
        <a href="<?= url('vente/commandes/'.$cmd['oid_doc'].'/edit') ?>" class="btn-secondary btn-sm"><i class="fa-solid fa-pen"></i> Modifier</a>
        <form method="POST" action="<?= url('vente/commandes/'.$cmd['oid_doc'].'/valider') ?>" onsubmit="return confirm('Valider cette commande ?')">
            <?= csrfField() ?><button class="btn-primary btn-sm"><i class="fa-solid fa-check"></i> Valider</button>
        </form>
        <form method="POST" action="<?= url('vente/commandes/'.$cmd['oid_doc'].'/annuler') ?>" onsubmit="return confirm('Annuler cette commande ?')">
            <?= csrfField() ?><button class="btn-danger btn-sm"><i class="fa-solid fa-xmark"></i> Annuler</button>
        </form>
        <?php endif; ?>
        <?php if($cmd['statut']==='validee'): ?>
        <a href="<?= url('vente/commandes/'.$cmd['oid_doc'].'/livrer') ?>" class="btn-primary btn-sm"><i class="fa-solid fa-truck"></i> Livrer</a>
        <form method="POST" action="<?= url('vente/commandes/'.$cmd['oid_doc'].'/annuler') ?>" onsubmit="return confirm('Annuler cette commande ?')">
            <?= csrfField() ?><button class="btn-danger btn-sm"><i class="fa-solid fa-xmark"></i> Annuler</button>
        </form>
        <?php endif; ?>
        <?php if($cmd['statut']==='livree' && empty($cmd['a_facture']) && !$cmd['est_comptant'] && !empty($_SESSION['droits']['vente.creer'])): ?>
        <a href="<?= url('vente/factures/creer') ?>" class="btn-primary btn-sm"><i class="fa-solid fa-file-invoice-dollar"></i> Facturer</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="card card-body mb-4">
        <dl class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
            <div><dt class="text-xs text-slate-500 mb-1">Date</dt><dd><?= dateFr($cmd['date_document']) ?></dd></div>
            <div><dt class="text-xs text-slate-500 mb-1">Créée par</dt><dd class="font-mono"><?= e($cmd['cree_par']) ?></dd></div>
            <div><dt class="text-xs text-slate-500 mb-1">Montant total</dt><dd class="font-bold text-violet-700"><?= money($cmd['montant_total']) ?></dd></div>
            <div><dt class="text-xs text-slate-500 mb-1">Type</dt><dd><?= $cmd['est_comptant']?'<span class="text-emerald-600">Comptant</span>':'À crédit' ?></dd></div>
        </dl>
        <?php if(!empty($cmd['observations'])): ?>
        <div class="mt-3 pt-3 border-t border-slate-100 text-sm text-slate-600"><?= e($cmd['observations']) ?></div>
        <?php endif; ?>
    </div>

    <div class="card overflow-hidden">
        <div class="card-header"><h2 class="font-semibold text-slate-700">Articles commandés</h2></div>
        <table class="data-table"><thead><tr><th>Code</th><th>Désignation</th><th class="text-right">Quantité</th><th class="text-right">Prix unit.</th><th class="text-right">Remise</th><th class="text-right">Montant</th></tr></thead>
        <tbody>
        <?php foreach($cmd['lignes'] as $l): ?>
        <tr>
            <td class="font-mono text-xs text-violet-600"><?= e($l['code']) ?></td>
            <td><?= e($l['designation']) ?></td>
            <td class="text-right"><?= number_format((float)$l['quantite'],2,',',' ') ?> <?= e($l['unite']) ?></td>
            <td class="text-right"><?= money($l['prix_unitaire']) ?></td>
            <td class="text-right"><?= (float)$l['remise_pct']>0?'<span class="text-emerald-600">-'.$l['remise_pct'].'%</span>':'—' ?></td>
            <td class="text-right font-semibold"><?= money($l['montant_ligne']) ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot><tr class="bg-slate-50"><td colspan="5" class="px-4 py-3 text-right font-semibold text-slate-600">Total</td>
            <td class="px-4 py-3 text-right font-bold text-violet-700"><?= money($cmd['montant_total']) ?></td></tr></tfoot>
        </table>
    </div>
</div>
